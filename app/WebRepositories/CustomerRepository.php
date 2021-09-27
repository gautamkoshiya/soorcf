<?php


namespace App\WebRepositories;


use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\CustomerAdvanceBooking;
use App\Models\CustomerPrice;
use App\Models\Product;
use App\Models\Region;
use App\Models\PaymentType;
use App\Models\PaymentTerm;
use App\Models\CompanyType;
use App\Models\AccountTransaction;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Unit;
use App\Models\UpdateNote;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\ICustomerRepositoryInterface;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements ICustomerRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Customer::with(['payment_type'=>function($q){$q->select('id','Name');},'company_type'=>function($q){$q->select('id','Name');},'payment_term'=>function($q){$q->select('id','Name');}])->select('id','Name','Phone','Mobile','openingBalance','company_id','payment_type_id','company_type_id','payment_term_id','Address','isActive')->where('company_id',session('company_id'))->latest()->get())
               ->addColumn('action', function ($data) {
                    $button= '<a href="'.route('customers.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button.= '&nbsp;&nbsp;';
                    $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';

                    return $button;
                })
                ->addColumn('isActive', function($data){
                    if($data->isActive == true)
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'" checked><span class="slider"></span></label>';
                        return $button;
                    }
                    else
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'"><span class="slider"></span></label>';
                        return $button;
                    }
                })
                 ->addColumn('paymentType', function($data) {
                        return $data->payment_type->Name ?? "No Data";
                    })
                ->addColumn('companyType', function($data) {
                    return $data->company_type->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'isActive',
                    'paymentType',
                    'companyType',
                ])
                ->make(true);
        }
        return view('admin.customer.index');
    }

    public function customer_app()
    {
        if(request()->ajax())
        {
            return datatables()->of(Customer::select('id','Name','Mobile','company_id','app_access')->where('company_id',session('company_id'))->where('isActive',1)->get())
                ->addColumn('action', function ($data) {
                    $button= '<a href="'.route('ChangeCustomerAppDataEdit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button.= '&nbsp;&nbsp;';
                    return $button;
                })
                ->addColumn('app_access', function($data){
                    if($data->app_access == true)
                    {
                        $button = '<label class="switch"><input onclick="change_app_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'" checked><span class="slider"></span></label>';
                        return $button;
                    }
                    else
                    {
                        $button = '<label class="switch"><input onclick="change_app_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'"><span class="slider"></span></label>';
                        return $button;
                    }
                })
                ->rawColumns([
                    'action',
                    'app_access',
                ])
                ->make(true);
        }
        return view('admin.customer.customer_app');
    }

    public function create()
    {
        $regions = Region::with('city')->get();
        $payment_types = PaymentType::orderBy('id', 'asc')->skip(0)->take(2)->get();
        $company_types = CompanyType::all();
        $payment_terms = PaymentTerm::all();
        return view('admin.customer.create',compact('regions','payment_types','company_types','payment_terms'));
    }

    public function CheckCustomerExist($request)
    {
        $data = Customer::where('Name','like','%'.$request->Name.'%')->where('company_id','=',session('company_id'))->get();
        if($data->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function store(CustomerRequest $customerRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($customerRequest->hasFile('fileUpload'))
            $filename = $customerRequest->file('fileUpload')->storeAs('customers', $filename,'public');

        else
            $filename = null;

        $customer = [
            'Name' =>$customerRequest->Name,
            'Mobile' =>$customerRequest->Mobile,
            'Representative' =>$customerRequest->Representative,
            'Phone' =>$customerRequest->Phone,
            'Address' =>$customerRequest->Address,
            'Email' =>$customerRequest->Email,
            'postCode' =>$customerRequest->postCode,
            'region_id' =>$customerRequest->region_id ?? 0,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$customerRequest->Description,
            'registrationDate' =>$customerRequest->registrationDate,
            'TRNNumber' =>$customerRequest->TRNNumber,
            'openingBalance' =>$customerRequest->openingBalance,
            'openingBalanceAsOfDate' =>$customerRequest->openingBalanceAsOfDate,
            'payment_term_id' =>$customerRequest->paymentTerm ?? 0,
            'company_type_id' =>$customerRequest->companyType ?? 0,
            'payment_type_id' =>$customerRequest->paymentType ?? 0,
        ];
        $customer = Customer::create($customer);
        if ($customer)
        {
            //account entry
            $account = new AccountTransaction([
                'customer_id' => $customer->id,
                'user_id' => $user_id,
                'createdDate' => $customerRequest->openingBalanceAsOfDate,
                'company_id' =>$company_id,
                'Description' =>'initial',
                'referenceNumber' =>'initial',
                'Credit' =>0.00,
                'Debit' =>0.00,
                'Differentiate' =>$customerRequest->openingBalance,
            ]);
            $customer->account_transaction()->save($account);

            if($customerRequest->companyType==1 && $customerRequest->openingBalance!=0)
            {
                //sales entry
                $sale = new Sale();
                $sale->SaleNumber = 'initial';
                $sale->SaleDate = $customerRequest->openingBalanceAsOfDate;
                $sale->Total = $customerRequest->openingBalance;
                $sale->subTotal = $customerRequest->openingBalance;
                $sale->totalVat = 0.00;
                $sale->grandTotal = $customerRequest->openingBalance;
                $sale->paidBalance = 0.00;
                $sale->remainingBalance = $customerRequest->openingBalance;
                $sale->customer_id = $customer->id;
                $sale->Description = '';
                $sale->IsPaid = 0;
                $sale->isActive = 0;
                $sale->IsPartialPaid = 0;
                $sale->IsReturn = false;
                $sale->IsPartialReturn = false;
                $sale->IsNeedStampOrSignature = false;
                $sale->user_id = $user_id;
                $sale->company_id = $company_id;
                $sale->save();
                $sale = $sale->id;

                $product=Product::select('id')->get()->first();
                $unit=Unit::select('id')->get()->first();


                $data =  SaleDetail::create([
                    "product_id" => $product->id,
                    "vehicle_id" => 0,
                    "unit_id" => $unit->id,
                    "Quantity" => 0.00,
                    "Price" => 0.00,
                    "rowTotal" => $customerRequest->openingBalance,
                    "VAT" => 0.00,
                    "rowVatAmount" => 0.00,
                    "rowSubTotal" => $customerRequest->openingBalance,
                    "PadNumber" => '',
                    "company_id" => $company_id,
                    "user_id" => $user_id,
                    "sale_id" => $sale,
                    "createdDate" => $customerRequest->openingBalanceAsOfDate,
                    "customer_id" => $customer->id,
                ]);
            }
        }

        //also add customer base price
        $price = [
            'Rate' =>$customerRequest->Rate,
            'VAT' =>$customerRequest->VAT,
            'customerLimit' =>$customerRequest->customerLimit,
            'user_id' =>$user_id,
            'customer_id' =>$customer->id,
            'company_id' =>$company_id,
            'pricesDate' =>date('Y-m-d'),
            'createdDate' =>date('Y-m-d'),
            'isActive' =>1,
        ];
        CustomerPrice::create($price);
        return redirect()->route('customers.index');
    }

    public function update(Request $request, $Id)
    {
        $customer = Customer::find($Id);
        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($request->hasFile('fileUpload'))
            $filename = $request->file('fileUpload')->storeAs('customers', $filename,'public');

        else
            $filename = $customer->fileUpload;

        $user_id = session('user_id');
        $customer->update([
            'Name' =>$request->Name,
            'Mobile' =>$request->Mobile,
            'Representative' =>$request->Representative,
            'Phone' =>$request->Phone,
            'Address' =>$request->Address,
            'postCode' =>$request->postCode,
            'region_id' =>$request->region_id ?? 0,
            'Email' =>$request->Email,
            'user_id' =>$user_id,
//            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$request->Description,
            'registrationDate' =>$request->registrationDate,
            'TRNNumber' =>$request->TRNNumber,
            'openingBalance' =>$request->openingBalance,
            'openingBalanceAsOfDate' =>$request->openingBalanceAsOfDate,
            'payment_term_id' =>$request->paymentTerm ?? 0,
            'company_type_id' =>$request->companyType ?? 0,
            'payment_type_id' =>$request->paymentType ?? 0,

        ]);
        return redirect()->route('customers.index');
    }

    public function ChangeCustomerAppData(Request $request, $Id)
    {
        $customer = Customer::find($Id);
        $new_password=$request->password;
        $new_login_email=$request->login_email;
        if($customer->password!=$request->password)
        {
            $new_password=md5($request->password);
        }
        if($customer->login_email!=$request->login_email)
        {
            $new_login_email=$request->login_email;
        }
        $data=[
            'password'=>$new_password,
            'login_email'=>$new_login_email,
            'password_last_updated'=>date('Y-m-d h:i:s'),
        ];
        $customer->update($data);
        return redirect()->route('customers.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function ChangeCustomerStatus($Id)
    {
        $customer = Customer::find($Id);
        if($customer->isActive==1)
        {
            $customer->isActive=0;
        }
        else
        {
            $customer->isActive=1;
        }
        $customer->update();
        return Response()->json(true);
    }

    public function ChangeCustomerAppStatus($Id)
    {
        $customer = Customer::find($Id);
        if($customer->app_access==1)
        {
            $customer->app_access=0;
        }
        else
        {
            $customer->app_access=1;
        }
        $customer->update();
        return Response()->json(true);
    }

    public function edit($Id)
    {
        $regions = Region::with('city')->get();
        $payment_types = PaymentType::orderBy('id', 'asc')->skip(0)->take(2)->get();
        $company_types = CompanyType::all();
        $payment_terms = PaymentTerm::all();
        $customer = Customer::with('region','payment_type','company_type','payment_term')->find($Id);
        return view('admin.customer.edit',compact('customer','regions','payment_types','company_types','payment_terms'));
    }

    public function ChangeCustomerAppDataEdit($Id)
    {
        $customer = Customer::find($Id);
        return view('admin.customer.customer_app_edit',compact('customer',));
    }

    public function delete(Request $request, $Id)
    {
        $data = Customer::findOrFail($Id);
        $data->delete();
        return redirect()->route('customers.index');
    }

    public function customer_delete_post(Request $request)
    {
        $data = Customer::findOrFail($request->row_id);
        $data->delete();

        $update_note = new UpdateNote();
        $update_note->RelationTable = 'customers';
        $update_note->RelationId = $request->row_id;
        $update_note->UpdateDescription = $request->deleteDescription;
        $update_note->user_id = session('user_id');
        $update_note->company_id = session('company_id');
        $update_note->save();

        return Response()->json(true);
        //return redirect()->route('customers.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function getCustomerVehicleDetails($Id)
    {
        // TODO: Implement getCustomerVehicleDetails() method.
    }

    public function customerDetails($Id)
    {
        // getting latest closing for supplier from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id')
            ->where('ac.customer_id','=',$Id)
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.customer_id','ac.Differentiate')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->get();
        $row=json_decode(json_encode($row), true);
        if(empty($row))
        {
            $row=0.00;
        }
        else
        {
            $row=$row[0]['Differentiate'];
        }

        //$customers = Customer::with('vehicles','customer_prices')->select('id','Name')->find($Id);
        $customers = Customer::with(['vehicles'=>function($q){$q->select('id','registrationNumber','customer_id');},'customer_prices'=>function($q){$q->select('id','Rate','VAT','customerLimit','customer_id');},])->select('id','Name')->where('id',$Id)->get();

        return response()->json(array('customers'=>$customers,'closing'=>$row));
    }

    public function getLedgerCustomers()
    {
        $customers = Customer::select('id','Name')->where('company_id',session('company_id'))->where('company_type_id',1)->get();
        return response()->json(array('parties'=>$customers));
    }

    public function salesCustomerDetails($Id)
    {
        // getting latest closing for supplier from account transaction table
        /*$row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id')
            ->where('ac.customer_id','=',$Id)
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.customer_id','ac.Differentiate')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->get();
        $row=json_decode(json_encode($row), true);
        if(empty($row))
        {
            $row=0.00;
        }
        else
        {
            $row=$row[0]['Differentiate'];
        }*/

        $credit_sum=AccountTransaction::where('customer_id',$Id)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Credit');
        $debit_sum=AccountTransaction::where('customer_id',$Id)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Debit');
        $diff=$debit_sum-$credit_sum;

        //$customers = Customer::with('vehicles','customer_prices')->select('id','Name')->find($Id);
        $customers = Customer::with(['vehicles'=>function($q){$q->select('id','registrationNumber','customer_id')->where('isActive',1);},'customer_prices'=>function($q){$q->select('id','Rate','VAT','customerLimit','customer_id');},])->select('id','Name')->where('id',$Id)->get();

        //advance booking details
        $advance_booking='';
        $booking_rem_quantity=CustomerAdvanceBooking::where('customer_id',$Id)->sum('remainingQuantity');
        if($booking_rem_quantity>0)
        {
            $advance_booking='Remaining Advance booking Quantity is : '.$booking_rem_quantity;
        }
        else
        {
            $sum_of_overfilled_qty=SaleDetail::where('customer_id',$Id)->whereNull('deleted_at')->where('isActive',1)->whereNotNull('booking_shortage')->sum('Quantity');
            if($sum_of_overfilled_qty>0)
            {
                $advance_booking='<h2 style="color: red;">Overfilled Quantity is : '.$sum_of_overfilled_qty.'</h2>';
            }

        }
        return response()->json(array('customers'=>$customers,'closing'=>round($diff,2),'advance_booking'=>$advance_booking));
    }

    public function GetCustomerAcquisitionAnalysis()
    {
        return view('admin.customer.get_customer_acquisition_analysis');
    }

    public function ViewCustomerAcquisitionAnalysis(Request $request)
    {
        $customer = Customer::whereBetween('created_at',[$request->fromDate,$request->toDate])->orderBy('created_at')->get()->groupBy(function($item) {
            return $item->created_at->format('Y-m-d');
        });
        $all_dates=array();
        $all_customer=array();
        foreach($customer as $key => $post){
            $all_dates[] = $key;
            $all_customer[] = $post->count();
        }
        return view('admin.customer.view_customer_acquisition_analysis',compact('all_customer','all_dates'));
    }

    public function getTopTenCustomerByAmount()
    {
        return view('admin.customer.get_top_ten_customer_by_amount');
    }

    public function getTopTenCustomerByQty()
    {
        return view('admin.customer.get_top_ten_customer_by_qty');
    }

    public function printTopTenCustomerByAmount(Request $request)
    {
        $customer = Customer::select('id','Name')->where('company_id',session('company_id'))->where('isActive',1)->whereNotIn('id', [65,79,2,126,131,132])->get();
        $final_array=array();
        foreach($customer as $single)
        {
            $sum_of_sales = Sale::whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('customer_id',$single->id)->sum('grandTotal');
            //$sum_of_sales = SaleDetail::whereBetween('createdDate',[$request->fromDate,$request->toDate])->where('customer_id',$single->id)->sum('Quantity');
            $final_array[]=array('customer_id'=>$single->id,'customer_name'=>$single->Name,'sum_of_sales'=>$sum_of_sales);
        }
        $row=$this->array_sort($final_array, 'sum_of_sales', SORT_DESC);
        $row=array_values($row);
        $row = array_slice($row, 0, 20);
        $row=array_reverse($row);
        $all_names=array_column($row,'customer_name');
        $all_amount=array_column($row,'sum_of_sales');
        $title='Top Customers From : '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate)).' - By Amount.';
        return view('admin.customer.print_top_ten_customer_by_amount',compact('all_names','all_amount','title'));
    }

    public function printTopTenCustomerByQty(Request $request)
    {
        $customer = Customer::select('id','Name')->where('company_id',session('company_id'))->where('isActive',1)->whereNotIn('id', [65,79,2,126,131,132])->get();
        $final_array=array();
        foreach($customer as $single)
        {
            //$sum_of_sales = Sale::whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('customer_id',$single->id)->sum('grandTotal');
            $sum_of_sales = SaleDetail::whereBetween('createdDate',[$request->fromDate,$request->toDate])->where('customer_id',$single->id)->sum('Quantity');
            $final_array[]=array('customer_id'=>$single->id,'customer_name'=>$single->Name,'sum_of_sales'=>$sum_of_sales);
        }
        $row=$this->array_sort($final_array, 'sum_of_sales', SORT_DESC);
        $row=array_values($row);
        $row = array_slice($row, 0, 20);
        $row=array_reverse($row);
        $all_names=array_column($row,'customer_name');
        $all_amount=array_column($row,'sum_of_sales');
        $title='Top Customers From : '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate)).' - By Qty.';
        return view('admin.customer.print_top_ten_customer_by_qty',compact('all_names','all_amount','title'));
    }

    function array_sort($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        return $new_array;
    }
}
