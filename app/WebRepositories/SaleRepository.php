<?php

namespace App\WebRepositories;

use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\CustomerAdvanceBooking;
use App\Models\CustomerAdvanceBookingDetail;
use App\Models\CustomerAdvanceDetail;
use App\Models\CustomerPrice;
use App\Models\PaymentReceive;
use App\Models\PaymentReceiveDetail;
use App\Models\Product;
use App\Models\Unit;
use App\Models\AccountTransaction;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\UpdateNote;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\ISaleRepositoryInterface;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\OAuth2\Server\RequestEvent;

class   SaleRepository implements ISaleRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Sale::with('sale_details.product','sale_details.vehicle','customer')->where('company_id',session('company_id'))->where('isActive',1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '';
                    if($data->TermsAndCondition==1)
                    {
                        $button.='<a href="'.route('sales.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    }
                    if($data->supplierNote==1)
                    {
                        $button.='&nbsp;<a href="'.url('sales_delete', $data->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    }
                    $button.='&nbsp;&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-money"><i class="fa fa-question" ></i></i></button>';
                    return $button;
                })
                 ->addColumn('createdDate', function($data) {
                        return $data->sale_details[0]->createdDate ?? "No date";
                    })
                 ->addColumn('PadNumber', function($data) {
                        return $data->sale_details[0]->PadNumber ?? "No Pad";
                    })
                 ->addColumn('customer', function($data) {
                        return $data->customer->Name ?? "No Name";
                    })
                 ->addColumn('registrationNumber', function($data) {
                        return $data->sale_details[0]->vehicle->registrationNumber ?? "No Number";
                    })
//                 ->addColumn('Product', function($data) {
//                        return $data->sale_details[0]->product->Name ?? "No product";
//                    })
                  ->addColumn('Quantity', function($data) {
                        return $data->sale_details[0]->Quantity ?? "No Quantity";
                    })
                   ->addColumn('Price', function($data) {
                        return $data->sale_details[0]->Price ?? "No Quantity";
                    })
                ->rawColumns(
                    [
                    'action',
                    // 'isActive',
                    'createdDate',
                    'PadNumber',
                    'customer',
                    'registrationNumber',
                    'Quantity',
                    'Price'
                    ])
                ->make(true);
        }
        return view('admin.sale.index');
    }

    public function get_today_sale()
    {
        if(request()->ajax())
        {
            return datatables()->of(Sale::with('sale_details.product','sale_details.vehicle','customer')->where('company_id',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '';
                    if($data->TermsAndCondition==1)
                    {
                        $button.='<a href="'.route('sales.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    }
                    if($data->supplierNote==1)
                    {
                        $button.='&nbsp;<a href="'.url('sales_delete', $data->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    }
                    $button.='&nbsp;&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-money"><i class="fa fa-question" ></i></i></button>';
                    return $button;
                })
                ->addColumn('createdDate', function($data) {
                    return $data->sale_details[0]->createdDate ?? "No date";
                })
                ->addColumn('PadNumber', function($data) {
                    return $data->sale_details[0]->PadNumber ?? "No Pad";
                })
                ->addColumn('customer', function($data) {
                    return $data->customer->Name ?? "No Name";
                })
                ->addColumn('registrationNumber', function($data) {
                    return $data->sale_details[0]->vehicle->registrationNumber ?? "No Number";
                })
                ->addColumn('Quantity', function($data) {
                    return $data->sale_details[0]->Quantity ?? "No Quantity";
                })
                ->addColumn('Price', function($data) {
                    return $data->sale_details[0]->Price ?? "No Quantity";
                })
                ->rawColumns(
                    [
                        'action',
                        'createdDate',
                        'PadNumber',
                        'customer',
                        'registrationNumber',
                        'Quantity',
                        'Price'
                    ])
                ->make(true);
        }
        return view('admin.sale.daily_sales');
    }

    public function get_sale_of_date()
    {
        return view('admin.sale.get_sales_of_date');
    }

    public function view_sale_of_date(Request $request)
    {
        $fromDate=$request->fromDate;
        return view('admin.sale.view_sales_of_date',compact('fromDate'));
    }

    public function view_result_sale_of_date(Request $request)
    {
        if(request()->ajax()) {
            return datatables()->of(Sale::with('sale_details.product', 'sale_details.vehicle', 'customer')->where('company_id', session('company_id'))->where('isActive', 1)->where('SaleDate', $_GET['fromDate'])->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '';
                    if($data->TermsAndCondition==1)
                    {
                        $button.='<a href="'.route('sales.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    }
                    if($data->supplierNote==1)
                    {
                        $button.='&nbsp;<a href="'.url('sales_delete', $data->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    }
                    $button.='&nbsp;&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-money"><i class="fa fa-question" ></i></i></button>';
                    return $button;
                })
                ->addColumn('createdDate', function ($data) {
                    return $data->sale_details[0]->createdDate ?? "No date";
                })
                ->addColumn('PadNumber', function ($data) {
                    return $data->sale_details[0]->PadNumber ?? "No Pad";
                })
                ->addColumn('customer', function ($data) {
                    return $data->customer->Name ?? "No Name";
                })
                ->addColumn('registrationNumber', function ($data) {
                    return $data->sale_details[0]->vehicle->registrationNumber ?? "No Number";
                })
                ->addColumn('Quantity', function ($data) {
                    return $data->sale_details[0]->Quantity ?? "No Quantity";
                })
                ->addColumn('Price', function ($data) {
                    return $data->sale_details[0]->Price ?? "No Quantity";
                })
                ->rawColumns(
                    [
                        'action',
                        'createdDate',
                        'PadNumber',
                        'customer',
                        'registrationNumber',
                        'Quantity',
                        'Price'
                    ])
                ->make(true);
        }
    }

    public function all_sales(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = Sale::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select s.id,s.company_id,s.customer_id,s.customer_id,s.SaleDate,s.totalVat,s.grandTotal,s.paidBalance,s.TermsAndCondition,s.supplierNote,c.Name, sd.PadNumber,sd.Quantity,sd.Price,sd.registrationNumber from sales as s left join customers as c on c.id = s.customer_id join (SELECT sale_details.*,v.registrationNumber FROM sale_details join vehicles as v on sale_details.vehicle_id=v.id WHERE sale_details.deleted_at is null) as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1 and s.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $sales = DB::select( DB::raw($sql));
        }
        else {
            $search = $request->input('search.value');

            $sql = 'select s.*, c.Name,sd.PadNumber,sd.Quantity,sd.Price,sd.registrationNumber from sales as s left join customers as c on c.id = s.customer_id join (SELECT sale_details.*,v.registrationNumber FROM sale_details join vehicles as v on sale_details.vehicle_id=v.id WHERE sale_details.deleted_at is null and sale_details.PadNumber LIKE "%'.$search.'%") as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1 and s.deleted_at is null or c.Name LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $sales = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,s.*, c.Name from sales as s left join customers as c on c.id = s.customer_id join (SELECT * FROM sale_details WHERE deleted_at is null and PadNumber LIKE "%'.$search.'%") as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1 and s.deleted_at is null or c.Name LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $sales_count = DB::select(DB::raw($sql_count));
            if(!empty($sales_count))
            {
                $totalFiltered = $sales_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($sales))
        {
            foreach ($sales as $sale)
            {
                $edit =  route('sales.edit',$sale->id);
                $nestedData['id'] = $sale->id;
                $nestedData['SaleDate'] = date('d-m-Y', strtotime($sale->SaleDate));
                $nestedData['PadNumber'] = $sale->PadNumber ?? "No Pad";
                $nestedData['customer'] = $sale->Name ?? "No Name";
                $nestedData['registrationNumber'] = $sale->registrationNumber ?? "No Number";
                $nestedData['Quantity'] = $sale->Quantity ?? 0.00;
                $nestedData['Price'] = $sale->Price ?? 0.00;
                $nestedData['totalVat'] = $sale->totalVat ?? 0.00;
                $nestedData['grandTotal'] = $sale->grandTotal ?? 0.00;
                $nestedData['paidBalance'] = $sale->paidBalance ?? 0.00;
                $button='';
                if($sale->TermsAndCondition==1)
                {
                    //$button.='<a href="javascript:void(0);"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-ban"></i></a>&nbsp;<a href="'.url('sales_delete', $sale->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    $button.='<a href="'.route('sales.edit', $sale->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                }
                $button.='&nbsp;';
                if($sale->supplierNote==1)
                {
                    $button.='<a href="#" data-id="'.$sale->id.'" data-toggle="modal" class="salesDelete btn btn-danger btn-sm" data-target="#deleteSales"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    //$button.='&nbsp;<a href="'.url('sales_delete', $sale->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                }
                $button.='&nbsp;<button class="btn btn-primary"  onclick="show_detail(this.id)" type="button" id="show_'.$sale->id.'"><i style="font-size: 20px" class="fa fa-money"><i class="fa fa-question" ></i></i></button>';
                $nestedData['action']=$button;
                //$nestedData['action'] = '<a href="'.route('sales.edit', $sale->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>&nbsp;&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$sale->id.'"><i style="font-size: 20px" class="fa fa-money"><i class="fa fa-question" ></i></i></button>';
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function all_sales_service(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = Sale::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select s.id,s.company_id,s.customer_id,s.customer_id,s.SaleDate,s.totalVat,s.grandTotal,s.paidBalance,s.TermsAndCondition,s.supplierNote,c.Name, sd.PadNumber,sd.Quantity,sd.Price from sales as s left join customers as c on c.id = s.customer_id join (SELECT sale_details.* FROM sale_details WHERE sale_details.deleted_at is null) as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1 and s.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $sales = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select s.*, c.Name,sd.PadNumber,sd.Quantity,sd.Price from sales as s left join customers as c on c.id = s.customer_id join (SELECT sale_details.* FROM sale_details WHERE sale_details.deleted_at is null and sale_details.PadNumber LIKE "%'.$search.'%") as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1 and s.deleted_at is null or c.Name LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $sales = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,s.*, c.Name from sales as s left join customers as c on c.id = s.customer_id join (SELECT * FROM sale_details WHERE deleted_at is null and PadNumber LIKE "%'.$search.'%") as sd on s.id = sd.sale_id where s.company_id = '.session('company_id').' and s.isActive = 1 and s.deleted_at is null or c.Name LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $sales_count = DB::select(DB::raw($sql_count));
            if(!empty($sales_count))
            {
                $totalFiltered = $sales_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($sales))
        {
            foreach ($sales as $sale)
            {
                $nestedData['id'] = $sale->id;
                $nestedData['SaleDate'] = date('d-m-Y', strtotime($sale->SaleDate));
                $nestedData['PadNumber'] = $sale->PadNumber ?? "No Pad";
                $nestedData['customer'] = $sale->Name ?? "No Name";
                $nestedData['registrationNumber'] = "N.A.";
                $nestedData['Quantity'] = $sale->Quantity ?? 0.00;
                $nestedData['Price'] = $sale->Price ?? 0.00;
                $nestedData['totalVat'] = $sale->totalVat ?? 0.00;
                $nestedData['grandTotal'] = $sale->grandTotal ?? 0.00;
                $nestedData['paidBalance'] = $sale->paidBalance ?? 0.00;
                $button='';
                if($sale->TermsAndCondition==1)
                {
                    $button.='<a href="'.route('edit_sale_service', $sale->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                }
                $button.='&nbsp;';
                if($sale->supplierNote==1)
                {
                    $button.='<a href="#" data-id="'.$sale->id.'" data-toggle="modal" class="salesDelete btn btn-danger btn-sm" data-target="#deleteSales"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                }
                $button.='&nbsp;<button class="btn btn-primary"  onclick="show_detail(this.id)" type="button" id="show_'.$sale->id.'"><i style="font-size: 20px" class="fa fa-money"><i class="fa fa-question" ></i></i></button>';
                $nestedData['action']=$button;
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function create()
    {
        if(session('company_id')==4 OR session('company_id')==5 OR session('company_id')==6 OR session('company_id')==7 OR session('company_id')==8)
        {
            $saleNo = $this->invoiceNumber();
            $init_data = $this->PadNumber();
            $customers = Customer::with('customer_prices')->where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
            $products = Product::all();
            $salesRecords = Sale::with(['sale_details.vehicle','customer'])->where('company_id',session('company_id'))->orderBy('id', 'desc')->skip(0)->take(3)->get();
            $units = Unit::all();
            return view('admin.sale.create_sales_as_service',compact('customers','saleNo','products','salesRecords','init_data','units'));
        }
        else
        {
            $saleNo = $this->invoiceNumber();
            $init_data = $this->PadNumber();
            $customers = Customer::with('customer_prices')->where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
            $products = Product::all();
            $salesRecords = Sale::with(['sale_details.vehicle','customer'])->where('company_id',session('company_id'))->orderBy('id', 'desc')->skip(0)->take(3)->get();
            return view('admin.sale.create',compact('customers','saleNo','products','salesRecords','init_data'));
        }
    }

    public function store(Request $request)
    {
        if(isset($request->Data['orders'][0]['PadNumber']))
        {
            $pad_number=$request->Data['orders'][0]['PadNumber'];
        }
        else
        {
            $pad_number=0;
        }
        if (!preg_match('/[^A-Za-z]/', $pad_number)) // '/[^a-z\d]/i' should also work.
        {
            $data=array('result'=>false,'message'=>'INVALID PAD NUMBER');
            echo json_encode($data);exit();
        }

        //check pad number already exist or not
        if($pad_number!=0)
        {
            $already_exist = SaleDetail::where('company_id',session('company_id'))->where('PadNumber',$pad_number)->get();
            if(!$already_exist->isEmpty())
            {
                $data=array('result'=>false,'message'=>'PAD NUMBER ALREADY EXIST');
                echo json_encode($data);exit();
            }
        }

        //check cash paid is not grater than grand total
        if($request->Data['paidBalance'] > $request->Data['grandTotal'])
        {
            $data=array('result'=>false,'message'=>'CAN NOT ENTER EXTRA CASH HERE GO TO ADVANCES');
            echo json_encode($data);exit();
        }

        //check customer name
        $name=Customer::select('Name')->where('id',$request->Data['customer_id'])->first();
        if($name=='CASH' or $name=='cash' or $name=='Cash' && $request->Data['paidBalance']!=0)
        {
            $data=array('result'=>false,'message'=>'For cash customer credit not allowed...');
            echo json_encode($data);exit();
        }

        //check customer has running qty in advance booking
        $result=CustomerAdvanceBooking::where('company_id','=',session('company_id'))->where('customer_id',$request->Data['customer_id'])->sum('remainingQuantity');
        $have_ever_booked=CustomerAdvanceBooking::where('company_id',session('company_id'))->where('customer_id',$request->Data['customer_id'])->get();
        //echo "<pre>";print_r($result);die;
        if($result>0 or $have_ever_booked->first())
        {
            // there is some advance booked quantity with this customer so sale from here
            $AllRequestCount = collect($request->Data)->count();

            DB::transaction(function () use($AllRequestCount,$request,$pad_number)
            {
                //deduct quantity in advance booking if there is
                $user_id = session('user_id');
                $company_id = session('company_id');
                $booking_rem_quantity=CustomerAdvanceBooking::where('customer_id',$request->Data['customer_id'])->sum('remainingQuantity');
                if($booking_rem_quantity)
                {
                    if($booking_rem_quantity>0)
                    {
                        if(isset($request->Data['orders'][0]['Quantity']))
                        {
                            $bookings=CustomerAdvanceBooking::where('customer_id',$request->Data['customer_id'])->where('remainingQuantity','!=',0)->get();
                            $alpha_counter=0;
                            $total_needed_to_this_entry=$request->Data['orders'][0]['Quantity'];
                            $total_i_need = $request->Data['orders'][0]['Quantity'];
                            $pad_flag=false;
                            foreach ($bookings as $single)
                            {
                                $alphas = range('A', 'Z');

                                $total_i_have=$single->remainingQuantity;
                                if ($total_i_have >= $total_i_need)
                                {
                                    $remaining_i_have = $total_i_have - $total_i_need;
                                    $single->update([
                                        "remainingQuantity"=> $remaining_i_have,
                                        "consumedQuantity"=> $single->consumedQuantity+$total_i_need,
                                    ]);


                                    $total_needed_to_this_entry-=$total_i_need;

                                    // full sales entries need to be done here start
                                    if($AllRequestCount > 0)
                                    {
                                        if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0)
                                        {
                                            $isPaid_current = false;
                                            $partialPaid_current =false;
                                            $TermsAndCondition = 1;
                                            $supplierNote = 1;
                                        }
                                        elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                                        {
                                            $isPaid_current = 1;
                                            $TermsAndCondition = 0;
                                            $supplierNote = 1;
                                            $partialPaid_current=0;
                                        }

                                        $local_grand_total=($total_i_need*$single->Rate+($total_i_need*$single->Rate*$request->Data['orders'][0]['Vat']/100));

                                        $sale = new Sale();
                                        $sale->SaleNumber = $request->Data['SaleNumber'];
                                        $sale->SaleDate = $request->Data['SaleDate'];
                                        $sale->Total = ($total_i_need*$single->Rate);
                                        $sale->subTotal = ($total_i_need*$single->Rate+($total_i_need*$single->Rate*$request->Data['orders'][0]['Vat']/100));
                                        $sale->totalVat = ($total_i_need*$single->Rate*$request->Data['orders'][0]['Vat']/100);
                                        $sale->grandTotal = $local_grand_total;
                                        $sale->paidBalance = $request->Data['paidBalance'];
                                        $sale->remainingBalance = $local_grand_total-$request->Data['paidBalance'];
                                        $sale->customer_id = $request->Data['customer_id'];
                                        $sale->Description = $request->Data['customerNote'];
                                        $sale->referenceNumber = $single->id;//booking id
                                        $sale->IsPaid = $isPaid_current;
                                        $sale->IsPartialPaid = $partialPaid_current;
                                        $sale->TermsAndCondition = 0;//editable_or_not -- not editable by default
                                        $sale->supplierNote = $supplierNote;//deletable_or_not
                                        $sale->IsReturn = false;
                                        $sale->IsPartialReturn = false;
                                        $sale->IsNeedStampOrSignature = false;
                                        $sale->user_id = $user_id;
                                        $sale->company_id = $company_id;
                                        $sale->save();
                                        $sale = $sale->id;

                                        foreach($request->Data['orders'] as $detail)
                                        {
                                            if($pad_flag==false)
                                            {
                                                $pad_number=$detail['PadNumber'];
                                            }
                                            else
                                            {
                                                $pad_number=$detail['PadNumber'].$alphas[$alpha_counter];
                                                $alpha_counter++;
                                            }

                                            SaleDetail::create([
                                                "product_id"        => $detail['product_id'],
                                                "vehicle_id"        => $detail['vehicle_id'],
                                                "unit_id"        => $detail['unit_id'],
                                                "Quantity"        => $total_i_need,
                                                "Price"        => $single->Rate,
                                                "rowTotal"        => $total_i_need*$single->Rate,
                                                "VAT"        => $detail['Vat'],
                                                "rowVatAmount"        => $total_i_need*$single->Rate*$request->Data['orders'][0]['Vat']/100,
                                                "rowSubTotal"        => $local_grand_total,
                                                "PadNumber"        => $pad_number,
                                                "company_id" => $company_id,
                                                "user_id"      => $user_id,
                                                "sale_id"      => $sale,
                                                "createdDate" => $detail['createdDate'],
                                                "customer_id" => $request->Data['customer_id'],
                                            ]);
                                        }

                                        if($request->Data['paidBalance'] != 0.00 || $request->Data['paidBalance'] != 0)
                                        {
                                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                            $difference = $cashTransaction->last()->Differentiate;
                                            $cash_transaction = new CashTransaction();
                                            $cash_transaction->Reference=$sale;
                                            $cash_transaction->createdDate=$request->Data['SaleDate'];
                                            $cash_transaction->Type='sales';
                                            $cash_transaction->Details='CashSales|'.$sale;
                                            $cash_transaction->Credit=0.00;
                                            $cash_transaction->Debit=$request->Data['paidBalance'];
                                            $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                            $cash_transaction->user_id = $user_id;
                                            $cash_transaction->company_id = $company_id;
                                            $cash_transaction->PadNumber = $pad_number;
                                            $cash_transaction->save();
                                        }

                                        ////////////////// start account section gautam ////////////////
                                        if($sale)
                                        {
                                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                            // totally credit
                                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                                $totalCredit = $local_grand_total;
                                                $difference = $accountTransaction->last()->Differentiate + $local_grand_total;
                                                $AccData =
                                                    [
                                                        'customer_id' => $request->Data['customer_id'],
                                                        'Credit' => 0.00,
                                                        'Debit' => $totalCredit,
                                                        'Differentiate' => $difference,
                                                        'createdDate' => $request->Data['SaleDate'],
                                                        'user_id' => $user_id,
                                                        'company_id' => $company_id,
                                                        'Description'=>'Sales|'.$sale,
                                                        'referenceNumber'=>'P#'.$pad_number,
                                                    ];
                                                $AccountTransactions = AccountTransaction::Create($AccData);
                                            }
                                            // fully paid with cash
                                            else
                                            {
                                                $totalCredit = $local_grand_total;
                                                $difference = $accountTransaction->last()->Differentiate + $local_grand_total;

                                                //make credit entry for the sales
                                                $AccountTransactions=AccountTransaction::Create([
                                                    'customer_id' => $request->Data['customer_id'],
                                                    'Credit' => 0.00,
                                                    'Debit' => $totalCredit,
                                                    'Differentiate' => $difference,
                                                    'createdDate' => $request->Data['SaleDate'],
                                                    'user_id' => $user_id,
                                                    'company_id' => $company_id,
                                                    'Description'=>'Sales|'.$sale,
                                                    'referenceNumber'=>'P#'.$pad_number,
                                                ]);

                                                //make credit entry for the whatever cash is paid
                                                $difference=$difference-$request->Data['paidBalance'];
                                                $AccountTransactions=AccountTransaction::Create([
                                                    'customer_id' => $request->Data['customer_id'],
                                                    'Credit' => $request->Data['paidBalance'],
                                                    'Debit' => 0.00,
                                                    'Differentiate' => $difference,
                                                    'createdDate' => $request->Data['SaleDate'],
                                                    'user_id' => $user_id,
                                                    'company_id' => $company_id,
                                                    'Description'=>'FullCashSales|'.$sale,
                                                    'referenceNumber'=>'P#'.$pad_number,
                                                ]);
                                            }
                                            //return Response()->json($AccountTransactions);
                                            /*$data=array('result'=>true,'message'=>'Record Inserted Successfully.');
                                            echo json_encode($data);*/
                                        }
                                        ////////////////// end account section gautam ////////////////
                                    }
                                    // full sales entries need to be done here end

                                    CustomerAdvanceBookingDetail::create([
                                        "Quantity" => $total_i_need,
                                        "user_id" => $user_id,
                                        "company_id" => $company_id,
                                        "customer_id" => $single->customer_id,
                                        "booking_id" => $single->id,
                                        'sale_id' => $sale,
                                        'BookingDate' => $single->BookingDate,
                                        'PadNumber' => $request->Data['orders'][0]['PadNumber'],
                                    ]);
                                    $total_i_need=0;
                                    if($total_i_need<=0)
                                    {
                                        break;
                                    }
                                }
                                // for single sales from multiple bookings so need to generate multiple sales entry by default
                                else if ($total_i_have < $total_i_need)
                                {
                                    //echo $total_i_have.'#'.$total_you_need;die;
                                    $single->update([
                                        "remainingQuantity"=> 0,
                                        "consumedQuantity"=> $single->consumedQuantity+$total_i_have,
                                    ]);
                                    $total_needed_to_this_entry-=$total_i_have;

                                    // full sales entries need to be done here start
                                    if($AllRequestCount > 0)
                                    {
                                        if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                                            $isPaid_current = false;
                                            $partialPaid_current =false;
                                            $TermsAndCondition = 1;
                                            $supplierNote = 1;
                                        }
                                        elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                                        {
                                            $isPaid_current = 1;
                                            $TermsAndCondition = 0;
                                            $supplierNote = 1;
                                            $partialPaid_current=0;
                                        }

                                        $local_grand_total=($total_i_have*$single->Rate+($total_i_have*$single->Rate*$request->Data['orders'][0]['Vat']/100));

                                        $sale = new Sale();
                                        $sale->SaleNumber = $request->Data['SaleNumber'];
                                        $sale->SaleDate = $request->Data['SaleDate'];
                                        $sale->Total = ($total_i_have*$single->Rate);
                                        $sale->subTotal = ($total_i_have*$single->Rate+($total_i_have*$single->Rate*$request->Data['orders'][0]['Vat']/100));
                                        $sale->totalVat = ($total_i_have*$single->Rate*$request->Data['orders'][0]['Vat']/100);
                                        $sale->grandTotal = $local_grand_total;
                                        $sale->paidBalance = $request->Data['paidBalance'];
                                        $sale->remainingBalance = $local_grand_total-$request->Data['paidBalance'];
                                        $sale->customer_id = $request->Data['customer_id'];
                                        $sale->Description = $request->Data['customerNote'];
                                        $sale->referenceNumber = $single->id;//booking id
                                        $sale->IsPaid = $isPaid_current;
                                        $sale->IsPartialPaid = $partialPaid_current;
                                        $sale->TermsAndCondition = 0;//editable_or_not -- not editable by default
                                        $sale->supplierNote = $supplierNote;//deletable_or_not
                                        $sale->IsReturn = false;
                                        $sale->IsPartialReturn = false;
                                        $sale->IsNeedStampOrSignature = false;
                                        $sale->user_id = $user_id;
                                        $sale->company_id = $company_id;
                                        $sale->save();
                                        $sale = $sale->id;

                                        foreach($request->Data['orders'] as $detail)
                                        {
                                            $pad_number=$detail['PadNumber'].$alphas[$alpha_counter];
                                            $alpha_counter++;
                                            $pad_flag=true;
                                            SaleDetail::create([
                                                "product_id"        => $detail['product_id'],
                                                "vehicle_id"        => $detail['vehicle_id'],
                                                "unit_id"        => $detail['unit_id'],
                                                "Quantity"        => $total_i_have,
                                                "Price"        => $single->Rate,
                                                "rowTotal"        => $total_i_have*$single->Rate,
                                                "VAT"        => $detail['Vat'],
                                                "rowVatAmount"        => $total_i_have*$single->Rate*$request->Data['orders'][0]['Vat']/100,
                                                "rowSubTotal"        => $local_grand_total,
                                                "PadNumber"        => $pad_number,
                                                "company_id" => $company_id,
                                                "user_id"      => $user_id,
                                                "sale_id"      => $sale,
                                                "createdDate" => $detail['createdDate'],
                                                "customer_id" => $request->Data['customer_id'],
                                            ]);
                                        }

                                        if($request->Data['paidBalance'] != 0.00 || $request->Data['paidBalance'] != 0)
                                        {
                                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                            $difference = $cashTransaction->last()->Differentiate;
                                            $cash_transaction = new CashTransaction();
                                            $cash_transaction->Reference=$sale;
                                            $cash_transaction->createdDate=$request->Data['SaleDate'];
                                            $cash_transaction->Type='sales';
                                            $cash_transaction->Details='CashSales|'.$sale;
                                            $cash_transaction->Credit=0.00;
                                            $cash_transaction->Debit=$request->Data['paidBalance'];
                                            $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                            $cash_transaction->user_id = $user_id;
                                            $cash_transaction->company_id = $company_id;
                                            $cash_transaction->PadNumber = $pad_number;
                                            $cash_transaction->save();
                                        }

                                        ////////////////// start account section gautam ////////////////
                                        if($sale)
                                        {
                                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                            // totally credit
                                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                                $totalCredit = $local_grand_total;
                                                $difference = $accountTransaction->last()->Differentiate + $local_grand_total;
                                                $AccData =
                                                    [
                                                        'customer_id' => $request->Data['customer_id'],
                                                        'Credit' => 0.00,
                                                        'Debit' => $totalCredit,
                                                        'Differentiate' => $difference,
                                                        'createdDate' => $request->Data['SaleDate'],
                                                        'user_id' => $user_id,
                                                        'company_id' => $company_id,
                                                        'Description'=>'Sales|'.$sale,
                                                        'referenceNumber'=>'P#'.$pad_number,
                                                    ];
                                                $AccountTransactions = AccountTransaction::Create($AccData);
                                            }
                                            // fully paid with cash
                                            else
                                            {
                                                $totalCredit = $local_grand_total;
                                                $difference = $accountTransaction->last()->Differentiate + $local_grand_total;

                                                //make credit entry for the sales
                                                $AccountTransactions=AccountTransaction::Create([
                                                    'customer_id' => $request->Data['customer_id'],
                                                    'Credit' => 0.00,
                                                    'Debit' => $totalCredit,
                                                    'Differentiate' => $difference,
                                                    'createdDate' => $request->Data['SaleDate'],
                                                    'user_id' => $user_id,
                                                    'company_id' => $company_id,
                                                    'Description'=>'Sales|'.$sale,
                                                    'referenceNumber'=>'P#'.$pad_number,
                                                ]);

                                                //make credit entry for the whatever cash is paid
                                                $difference=$difference-$request->Data['paidBalance'];
                                                $AccountTransactions=AccountTransaction::Create([
                                                    'customer_id' => $request->Data['customer_id'],
                                                    'Credit' => $request->Data['paidBalance'],
                                                    'Debit' => 0.00,
                                                    'Differentiate' => $difference,
                                                    'createdDate' => $request->Data['SaleDate'],
                                                    'user_id' => $user_id,
                                                    'company_id' => $company_id,
                                                    'Description'=>'FullCashSales|'.$sale,
                                                    'referenceNumber'=>'P#'.$pad_number,
                                                ]);
                                            }
                                            //return Response()->json($AccountTransactions);
//                                            $data=array('result'=>true,'message'=>'Record Inserted Successfully.');
//                                            echo json_encode($data);
                                        }
                                        ////////////////// end account section gautam ////////////////
                                    }
                                    // full sales entries need to be done here end

                                    CustomerAdvanceBookingDetail::create([
                                        "Quantity" => $total_i_have,
                                        "user_id" => $user_id,
                                        "company_id" => $company_id,
                                        "customer_id" => $single->customer_id,
                                        "booking_id" => $single->id,
                                        'sale_id' => $sale,
                                        'BookingDate' => $single->BookingDate,
                                        'PadNumber' => $request->Data['orders'][0]['PadNumber'],
                                    ]);
                                    $total_i_need-=$total_i_have;

                                    if($total_i_need<=0)
                                    {
                                        break;
                                    }
                                }
                            }

                            // booking_overate_sales_entry
                            if($total_needed_to_this_entry!=0)
                            {
                                $shortage=$total_needed_to_this_entry;
                                $rate_from_customer_prices=CustomerPrice::select('Rate')->where('customer_id',$request->Data['customer_id'])->first();
                                $rate_from_customer_prices=$rate_from_customer_prices->Rate;
                                // 2.full sales entries need to be done here end (shortage)
                                    if($AllRequestCount > 0)
                                    {
                                        if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                                            $isPaid_current = false;
                                            $partialPaid_current =false;
                                            $TermsAndCondition = 1;
                                            $supplierNote = 1;
                                        }
                                        elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                                        {
                                            $isPaid_current = 1;
                                            $TermsAndCondition = 0;
                                            $supplierNote = 1;
                                            $partialPaid_current=0;
                                        }

                                        $local_grand_total=($shortage*$rate_from_customer_prices+($shortage*$rate_from_customer_prices*$request->Data['orders'][0]['Vat']/100));

                                        $sale = new Sale();
                                        $sale->SaleNumber = $request->Data['SaleNumber'];
                                        $sale->SaleDate = $request->Data['SaleDate'];
                                        $sale->Total = ($shortage*$rate_from_customer_prices);
                                        $sale->subTotal = ($shortage*$rate_from_customer_prices+($shortage*$rate_from_customer_prices*$request->Data['orders'][0]['Vat']/100));
                                        $sale->totalVat = ($shortage*$rate_from_customer_prices*$request->Data['orders'][0]['Vat']/100);
                                        $sale->grandTotal = $local_grand_total;
                                        $sale->paidBalance = $request->Data['paidBalance'];
                                        $sale->remainingBalance = $local_grand_total-$request->Data['paidBalance'];
                                        $sale->customer_id = $request->Data['customer_id'];
                                        $sale->Description = $request->Data['customerNote'];
                                        $sale->IsPaid = $isPaid_current;
                                        $sale->IsPartialPaid = $partialPaid_current;
                                        $sale->TermsAndCondition = 0;//editable_or_not -- not editable by default
                                        $sale->supplierNote = $supplierNote;//deletable_or_not
                                        $sale->IsReturn = false;
                                        $sale->IsPartialReturn = false;
                                        $sale->IsNeedStampOrSignature = false;
                                        $sale->user_id = $user_id;
                                        $sale->company_id = $company_id;
                                        $sale->save();
                                        $sale = $sale->id;

                                        foreach($request->Data['orders'] as $detail)
                                        {
                                            $pad_number=$detail['PadNumber'].$alphas[$alpha_counter];
                                            $alpha_counter++;
                                            SaleDetail::create([
                                                "product_id"        => $detail['product_id'],
                                                "vehicle_id"        => $detail['vehicle_id'],
                                                "unit_id"        => $detail['unit_id'],
                                                "Quantity"        => $shortage,
                                                "Price"        => $rate_from_customer_prices,
                                                "rowTotal"        => $shortage*$rate_from_customer_prices,
                                                "VAT"        => $detail['Vat'],
                                                "rowVatAmount"        => $shortage*$rate_from_customer_prices*$request->Data['orders'][0]['Vat']/100,
                                                "rowSubTotal"        => $local_grand_total,
                                                "PadNumber"        => $pad_number,
                                                "company_id" => $company_id,
                                                "user_id"      => $user_id,
                                                "sale_id"      => $sale,
                                                "createdDate" => $detail['createdDate'],
                                                "customer_id" => $request->Data['customer_id'],
                                                "booking_shortage" => $single->id,
                                            ]);
                                        }

                                        if($request->Data['paidBalance'] != 0.00 || $request->Data['paidBalance'] != 0)
                                        {
                                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                            $difference = $cashTransaction->last()->Differentiate;
                                            $cash_transaction = new CashTransaction();
                                            $cash_transaction->Reference=$sale;
                                            $cash_transaction->createdDate=$request->Data['SaleDate'];
                                            $cash_transaction->Type='sales';
                                            $cash_transaction->Details='CashSales|'.$sale;
                                            $cash_transaction->Credit=0.00;
                                            $cash_transaction->Debit=$request->Data['paidBalance'];
                                            $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                            $cash_transaction->user_id = $user_id;
                                            $cash_transaction->company_id = $company_id;
                                            $cash_transaction->PadNumber = $pad_number;
                                            $cash_transaction->save();
                                        }

                                        ////////////////// start account section gautam ////////////////
                                        if($sale)
                                        {
                                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                            // totally credit
                                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                                $totalCredit = $local_grand_total;
                                                $difference = $accountTransaction->last()->Differentiate + $local_grand_total;
                                                $AccData =
                                                    [
                                                        'customer_id' => $request->Data['customer_id'],
                                                        'Credit' => 0.00,
                                                        'Debit' => $totalCredit,
                                                        'Differentiate' => $difference,
                                                        'createdDate' => $request->Data['SaleDate'],
                                                        'user_id' => $user_id,
                                                        'company_id' => $company_id,
                                                        'Description'=>'Sales|'.$sale,
                                                        'referenceNumber'=>'P#'.$pad_number,
                                                    ];
                                                $AccountTransactions = AccountTransaction::Create($AccData);
                                            }
                                            // fully paid with cash
                                            else
                                            {
                                                $totalCredit = $local_grand_total;
                                                $difference = $accountTransaction->last()->Differentiate + $local_grand_total;

                                                //make credit entry for the sales
                                                $AccountTransactions=AccountTransaction::Create([
                                                    'customer_id' => $request->Data['customer_id'],
                                                    'Credit' => 0.00,
                                                    'Debit' => $totalCredit,
                                                    'Differentiate' => $difference,
                                                    'createdDate' => $request->Data['SaleDate'],
                                                    'user_id' => $user_id,
                                                    'company_id' => $company_id,
                                                    'Description'=>'Sales|'.$sale,
                                                    'referenceNumber'=>'P#'.$pad_number,
                                                ]);

                                                //make credit entry for the whatever cash is paid
                                                $difference=$difference-$request->Data['paidBalance'];
                                                $AccountTransactions=AccountTransaction::Create([
                                                    'customer_id' => $request->Data['customer_id'],
                                                    'Credit' => $request->Data['paidBalance'],
                                                    'Debit' => 0.00,
                                                    'Differentiate' => $difference,
                                                    'createdDate' => $request->Data['SaleDate'],
                                                    'user_id' => $user_id,
                                                    'company_id' => $company_id,
                                                    'Description'=>'FullCashSales|'.$sale,
                                                    'referenceNumber'=>'P#'.$pad_number,
                                                ]);
                                            }
                                            //return Response()->json($AccountTransactions);
//                                            $data=array('result'=>true,'message'=>'Record Inserted Successfully.');
//                                            echo json_encode($data);
                                        }
                                        ////////////////// end account section gautam ////////////////
                                    }
                                 //2.full sales entries need to be done here end (shortage)
                            }
                        }
                    }
                    else
                    {
                        $shortage=$request->Data['orders'][0]['Quantity'];
                        $rate_from_customer_prices=CustomerPrice::select('Rate')->where('customer_id',$request->Data['customer_id'])->first();
                        $rate_from_customer_prices=$rate_from_customer_prices->Rate;
                        // 2.full sales entries need to be done here end (shortage)
                        if($AllRequestCount > 0)
                        {
                            if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                                $isPaid_current = false;
                                $partialPaid_current =false;
                                $TermsAndCondition = 1;
                                $supplierNote = 1;
                            }
                            elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                            {
                                $isPaid_current = 1;
                                $TermsAndCondition = 0;
                                $supplierNote = 1;
                                $partialPaid_current=0;
                            }

                            $local_grand_total=($shortage*$rate_from_customer_prices+($shortage*$rate_from_customer_prices*$request->Data['orders'][0]['Vat']/100));

                            $sale = new Sale();
                            $sale->SaleNumber = $request->Data['SaleNumber'];
                            $sale->SaleDate = $request->Data['SaleDate'];
                            $sale->Total = ($shortage*$rate_from_customer_prices);
                            $sale->subTotal = ($shortage*$rate_from_customer_prices+($shortage*$rate_from_customer_prices*$request->Data['orders'][0]['Vat']/100));
                            $sale->totalVat = ($shortage*$rate_from_customer_prices*$request->Data['orders'][0]['Vat']/100);
                            $sale->grandTotal = $local_grand_total;
                            $sale->paidBalance = $request->Data['paidBalance'];
                            $sale->remainingBalance = $local_grand_total-$request->Data['paidBalance'];
                            $sale->customer_id = $request->Data['customer_id'];
                            $sale->Description = $request->Data['customerNote'];
                            $sale->IsPaid = $isPaid_current;
                            $sale->IsPartialPaid = $partialPaid_current;
                            $sale->TermsAndCondition = $TermsAndCondition;//editable_or_not
                            $sale->supplierNote = $supplierNote;//deletable_or_not
                            $sale->IsReturn = false;
                            $sale->IsPartialReturn = false;
                            $sale->IsNeedStampOrSignature = false;
                            $sale->user_id = $user_id;
                            $sale->company_id = $company_id;
                            $sale->save();
                            $sale = $sale->id;

                            foreach($request->Data['orders'] as $detail)
                            {
                                $pad_number=$detail['PadNumber'];
                                SaleDetail::create([
                                    "product_id"        => $detail['product_id'],
                                    "vehicle_id"        => $detail['vehicle_id'],
                                    "unit_id"        => $detail['unit_id'],
                                    "Quantity"        => $shortage,
                                    "Price"        => $rate_from_customer_prices,
                                    "rowTotal"        => $shortage*$rate_from_customer_prices,
                                    "VAT"        => $detail['Vat'],
                                    "rowVatAmount"        => $shortage*$rate_from_customer_prices*$request->Data['orders'][0]['Vat']/100,
                                    "rowSubTotal"        => $local_grand_total,
                                    "PadNumber"        => $pad_number,
                                    "company_id" => $company_id,
                                    "user_id"      => $user_id,
                                    "sale_id"      => $sale,
                                    "createdDate" => $detail['createdDate'],
                                    "customer_id" => $request->Data['customer_id'],
                                    "booking_shortage" => 0,
                                ]);
                            }

                            if($request->Data['paidBalance'] != 0.00 || $request->Data['paidBalance'] != 0)
                            {
                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$sale;
                                $cash_transaction->createdDate=$request->Data['SaleDate'];
                                $cash_transaction->Type='sales';
                                $cash_transaction->Details='CashSales|'.$sale;
                                $cash_transaction->Credit=0.00;
                                $cash_transaction->Debit=$request->Data['paidBalance'];
                                $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $pad_number;
                                $cash_transaction->save();
                            }

                            ////////////////// start account section gautam ////////////////
                            if($sale)
                            {
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                                // totally credit
                                if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                    $totalCredit = $local_grand_total;
                                    $difference = $accountTransaction->last()->Differentiate + $local_grand_total;
                                    $AccData =
                                        [
                                            'customer_id' => $request->Data['customer_id'],
                                            'Credit' => 0.00,
                                            'Debit' => $totalCredit,
                                            'Differentiate' => $difference,
                                            'createdDate' => $request->Data['SaleDate'],
                                            'user_id' => $user_id,
                                            'company_id' => $company_id,
                                            'Description'=>'Sales|'.$sale,
                                            'referenceNumber'=>'P#'.$pad_number,
                                        ];
                                    $AccountTransactions = AccountTransaction::Create($AccData);
                                }
                                // fully paid with cash
                                else
                                {
                                    $totalCredit = $local_grand_total;
                                    $difference = $accountTransaction->last()->Differentiate + $local_grand_total;

                                    //make credit entry for the sales
                                    $AccountTransactions=AccountTransaction::Create([
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $totalCredit,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$sale,
                                        'referenceNumber'=>'P#'.$pad_number,
                                    ]);

                                    //make credit entry for the whatever cash is paid
                                    $difference=$difference-$request->Data['paidBalance'];
                                    $AccountTransactions=AccountTransaction::Create([
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => $request->Data['paidBalance'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashSales|'.$sale,
                                        'referenceNumber'=>'P#'.$pad_number,
                                    ]);
                                }
                                //return Response()->json($AccountTransactions);
//                                $data=array('result'=>true,'message'=>'Record Inserted Successfully.');
//                                echo json_encode($data);
                            }
                            ////////////////// end account section gautam ////////////////
                        }
                        //2.full sales entries need to be done here end (shortage)
                    }
                }
            });
            $data=array('result'=>true,'message'=>'Record Inserted Successfully123.');
            echo json_encode($data);
            exit;
        }
        else
        {
            // normal sales entry
            $AllRequestCount = collect($request->Data)->count();

            DB::transaction(function () use($AllRequestCount,$request,$pad_number)
            {
                if($AllRequestCount > 0)
                {
                    if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                        $isPaid_current = false;
                        $partialPaid_current =false;
                        $TermsAndCondition = 1;
                        $supplierNote = 1;
                    }
                    elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                    {
                        $isPaid_current = 1;
                        $TermsAndCondition = 0;
                        $supplierNote = 1;
                        $partialPaid_current=0;
                    }
                    $user_id = session('user_id');
                    $company_id = session('company_id');

                    $sale = new Sale();
                    $sale->SaleNumber = $request->Data['SaleNumber'];
                    $sale->SaleDate = $request->Data['SaleDate'];
                    $sale->Total = $request->Data['Total'];
                    $sale->subTotal = $request->Data['subTotal'];
                    $sale->totalVat = $request->Data['totalVat'];
                    $sale->grandTotal = $request->Data['grandTotal'];
                    $sale->paidBalance = $request->Data['paidBalance'];
                    $sale->remainingBalance = $request->Data['grandTotal']-$request->Data['paidBalance'];
                    $sale->customer_id = $request->Data['customer_id'];
                    $sale->Description = $request->Data['customerNote'];
                    $sale->IsPaid = $isPaid_current;
                    $sale->IsPartialPaid = $partialPaid_current;
                    $sale->TermsAndCondition = $TermsAndCondition;//editable_or_not
                    $sale->supplierNote = $supplierNote;//deletable_or_not
                    $sale->IsReturn = false;
                    $sale->IsPartialReturn = false;
                    $sale->IsNeedStampOrSignature = false;
                    $sale->user_id = $user_id;
                    $sale->company_id = $company_id;
                    $sale->save();
                    $sale = $sale->id;

                    foreach($request->Data['orders'] as $detail)
                    {
                        $pad_number=$detail['PadNumber'];
                        SaleDetail::create([
                            "product_id"        => $detail['product_id'],
                            "vehicle_id"        => $detail['vehicle_id'],
                            "unit_id"        => $detail['unit_id'],
                            "Quantity"        => $detail['Quantity'],
                            "Price"        => $detail['Price'],
                            "rowTotal"        => $detail['rowTotal'],
                            "VAT"        => $detail['Vat'],
                            "rowVatAmount"        => $detail['rowVatAmount'],
                            "rowSubTotal"        => $detail['rowSubTotal'],
                            "PadNumber"        => $detail['PadNumber'],
                            "company_id" => $company_id,
                            "user_id"      => $user_id,
                            "sale_id"      => $sale,
                            "createdDate" => $detail['createdDate'],
                            "customer_id" => $request->Data['customer_id'],
                        ]);
                    }

                    if($request->Data['paidBalance'] != 0.00 || $request->Data['paidBalance'] != 0)
                    {
                        $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                        $difference = $cashTransaction->last()->Differentiate;
                        $cash_transaction = new CashTransaction();
                        $cash_transaction->Reference=$sale;
                        $cash_transaction->createdDate=$request->Data['SaleDate'];
                        $cash_transaction->Type='sales';
                        $cash_transaction->Details='CashSales|'.$sale;
                        $cash_transaction->Credit=0.00;
                        $cash_transaction->Debit=$request->Data['paidBalance'];
                        $cash_transaction->Differentiate=$difference+$request->Data['paidBalance'];
                        $cash_transaction->user_id = $user_id;
                        $cash_transaction->company_id = $company_id;
                        $cash_transaction->PadNumber = $pad_number;
                        $cash_transaction->save();
                    }

                    ////////////////// start account section gautam ////////////////
                    if($sale)
                    {
                        $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                        // totally credit
                        if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                            $AccData =
                                [
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $totalCredit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$sale,
                                    'referenceNumber'=>'P#'.$detail['PadNumber'],
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                        }
                        // fully paid with cash
                        else
                        {
                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                            //make credit entry for the sales
                            $AccountTransactions=AccountTransaction::Create([
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['SaleDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$sale,
                                'referenceNumber'=>'P#'.$detail['PadNumber'],
                            ]);

                            //make credit entry for the whatever cash is paid
                            $difference=$difference-$request->Data['paidBalance'];
                            $AccountTransactions=AccountTransaction::Create([
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => $request->Data['paidBalance'],
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['SaleDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'FullCashSales|'.$sale,
                                'referenceNumber'=>'P#'.$detail['PadNumber'],
                            ]);
                        }
                        //return Response()->json($AccountTransactions);
                        $data=array('result'=>true,'message'=>'Record Inserted Successfully456.');
                        echo json_encode($data);
                    }
                    ////////////////// end account section gautam ////////////////
                }

            });
        }
    }

    public function store_sale_service(Request $request)
    {
        //echo "<pre>";print_r($request->all());die;
        if(isset($request->Data['PadNumber']))
        {
            $pad_number=$request->Data['PadNumber'];
        }
        else
        {
            $pad_number=0;
        }
        if (!preg_match('/[^A-Za-z]/', $pad_number)) // '/[^a-z\d]/i' should also work.
        {
            $data=array('result'=>false,'message'=>'INVALID PAD NUMBER');
            echo json_encode($data);exit();
        }

        //check pad number already exist or not
        if($pad_number!=0)
        {
            $already_exist = SaleDetail::where('company_id',session('company_id'))->where('PadNumber',$pad_number)->get();
            if(!$already_exist->isEmpty())
            {
                $data=array('result'=>false,'message'=>'PAD NUMBER ALREADY EXIST');
                echo json_encode($data);exit();
            }
        }

        //check cash paid is not grater than grand total
        /*if($request->Data['paidBalance'] > $request->Data['grandTotal'])
        {
            $data=array('result'=>false,'message'=>'CAN NOT ENTER EXTRA CASH HERE GO TO ADVANCES');
            echo json_encode($data);exit();
        }*/

        //check customer name
        $name=Customer::select('Name')->where('id',$request->Data['customer_id'])->first();
        if($name=='CASH' or $name=='cash' or $name=='Cash' && $request->Data['cashPaid']!=0)
        {
            $data=array('result'=>false,'message'=>'For cash customer credit not allowed...');
            echo json_encode($data);exit();
        }

        $AllRequestCount = collect($request->Data)->count();

        DB::transaction(function () use($AllRequestCount,$request,$pad_number)
        {
            if($AllRequestCount > 0)
            {
                if ($request->Data['cashPaid'] == 0.00 || $request->Data['cashPaid'] == 0) {
                    $isPaid_current = false;
                    $partialPaid_current =false;
                    $TermsAndCondition = 1;
                    $supplierNote = 1;
                }
                elseif($request->Data['cashPaid'] >= $request->Data['grandTotal'])
                {
                    $isPaid_current = 1;
                    $TermsAndCondition = 0;
                    $supplierNote = 1;
                    $partialPaid_current=0;
                }
                elseif($request->Data['cashPaid'] <= $request->Data['grandTotal'])
                {
                    $isPaid_current = 0;
                    $TermsAndCondition = 0;
                    $supplierNote = 0;
                    $partialPaid_current=1;
                }
                $user_id = session('user_id');
                $company_id = session('company_id');

                $sale = new Sale();
                $sale->SaleNumber = $request->Data['SaleNumber'];
                $sale->SaleDate = $request->Data['SaleDate'];
                $sale->Total = $request->Data['subTotal'];
                $sale->subTotal = $request->Data['subTotal'];
                $sale->totalVat = $request->Data['totalVat'];
                $sale->grandTotal = $request->Data['grandTotal'];
                $sale->paidBalance = $request->Data['cashPaid'];
                $sale->remainingBalance = $request->Data['grandTotal']-$request->Data['cashPaid'];
                $sale->customer_id = $request->Data['customer_id'];
                $sale->IsPaid = $isPaid_current;
                $sale->IsPartialPaid = $partialPaid_current;
                $sale->TermsAndCondition = $TermsAndCondition;//editable_or_not
                $sale->supplierNote = $supplierNote;//deletable_or_not
                $sale->IsReturn = false;
                $sale->IsPartialReturn = false;
                $sale->IsNeedStampOrSignature = false;
                $sale->user_id = $user_id;
                $sale->company_id = $company_id;
                $sale->save();
                $sale = $sale->id;

                foreach($request->Data['orders'] as $detail)
                {
                    $pad_number=$request->Data['PadNumber'];
                    SaleDetail::create([
                        "product_id"        => $detail['product_id'],
                        "vehicle_id"        => 0,
                        "unit_id"        => $detail['unit_id'],
                        "Quantity"        => $detail['Quantity'],
                        "Description"        => $detail['Description'],
                        "Price"        => $detail['Price'],
                        "rowTotal"        => $detail['rowTotal'],
                        "VAT"        => $detail['VAT'],
                        "rowVatAmount"        => $detail['rowVatAmount'],
                        "rowSubTotal"        => $detail['rowSubTotal'],
                        "PadNumber"        => $request->Data['PadNumber'],
                        "company_id" => $company_id,
                        "user_id"      => $user_id,
                        "sale_id"      => $sale,
                        "createdDate" => $request->Data['SaleDate'],
                        "customer_id" => $request->Data['customer_id'],
                    ]);
                }

                if($request->Data['cashPaid'] != 0.00 || $request->Data['cashPaid'] != 0)
                {
                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference=$sale;
                    $cash_transaction->createdDate=$request->Data['SaleDate'];
                    $cash_transaction->Type='sales';
                    $cash_transaction->Details='CashSales|'.$sale;
                    $cash_transaction->Credit=0.00;
                    $cash_transaction->Debit=$request->Data['cashPaid'];
                    $cash_transaction->Differentiate=$difference+$request->Data['cashPaid'];
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->PadNumber = $pad_number;
                    $cash_transaction->save();
                }

                ////////////////// start account section gautam ////////////////
                if($sale)
                {
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                    // totally credit
                    if ($request->Data['cashPaid'] == 0 || $request->Data['cashPaid'] == 0.00) {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                        $AccData =
                            [
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['SaleDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$sale,
                                'referenceNumber'=>'P#'.$pad_number,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    }
                    // fully paid with cash
                    else
                    {
                        $totalCredit = $request->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                        //make credit entry for the sales
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => 0.00,
                            'Debit' => $totalCredit,
                            'Differentiate' => $difference,
                            'createdDate' => $request->Data['SaleDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'Sales|'.$sale,
                            'referenceNumber'=>'P#'.$pad_number,
                        ]);

                        //make credit entry for the whatever cash is paid
                        $difference=$difference-$request->Data['cashPaid'];
                        $AccountTransactions=AccountTransaction::Create([
                            'customer_id' => $request->Data['customer_id'],
                            'Credit' => $request->Data['cashPaid'],
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => $request->Data['SaleDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'FullCashSales|'.$sale,
                            'referenceNumber'=>'P#'.$pad_number,
                        ]);
                    }
                    //return Response()->json($AccountTransactions);
                    $data=array('result'=>true,'message'=>'Record Inserted Successfully.');
                    echo json_encode($data);
                }
                ////////////////// end account section gautam ////////////////
            }
        });
    }

    public function update(Request $request, $Id)
    {
        $AllRequestCount = collect($request->Data)->count();
        DB::transaction(function () use($AllRequestCount,$request,$Id)
        {
            if($AllRequestCount > 0)
            {
                $sold = Sale::with('customer.account_transaction')->find($Id);
                $user_id = session('user_id');
                $company_id = session('company_id');
                $PadNumber=$request['Data']['orders'][0]['PadNumber'];

                ////////////////// account section gautam ////////////////
                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                if (!is_null($accountTransaction))
                {
                    if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                    {
                        // here will come 3 cases
                        // 2. only quantity or price updating - customer is the same
                        // check if only grand total is changed and not the customer
                        if($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                        {
                            //customer is not changed then need to find what is the differance in total and for payment changes
                            // here in two way we can proceed
                            // option 1 : reverse previous account entries and make new entry
                            // option 2 : find out plus minus differance and make one another entry with differences
                            // option 2 is not preferable because of while displaying we need to add or subtract similar sales id entry so that is little tricky in query
                            // also need to manage isPaid and isPartialPaid flag according

                            // implementation of option 2
                            //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                            if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                            {
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Sales|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing-$previously_debited,
                                        'createdDate' => date('Y-m-d'),
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'updateDescription'=>'hide',
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                            }
                            //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                            elseif($sold->paidBalance > 0 AND $sold->paidBalance < $sold->grandTotal AND $sold->IsPartialPaid==1)
                            {
                                // entry 1 : debit entry for sales
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Sales|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing-$previously_debited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'updateDescription'=>'hide',
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='PartialCashSales|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing+$previously_credited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashSales|'.$Id,
                                        'updateDescription'=>'hide',
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            }
                            //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                            else
                            {
                                // entry 1 : debit entry for sales
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Sales|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing-$previously_debited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'updateDescription'=>'hide',
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='FullCashSales|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing+$previously_credited,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'FullCashSales|'.$Id,
                                        'updateDescription'=>'hide',
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                            }

                            /*new entry*/
                            // start new entry for updated customer with checking all three cases
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                            // totally credit
                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                $AccData =
                                    [
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $totalCredit,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                            // partial payment some cash some credit
                            elseif($request->Data['paidBalance'] > 0 AND $request->Data['paidBalance'] < $request->Data['grandTotal'] )
                            {
                                $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $difference = $differenceValue + $request->Data['grandTotal'];

                                //make credit entry for the sales
                                $AccData =
                                    [
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $request->Data['grandTotal'],
                                        'Differentiate' => $totalCredit,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);

                                //make debit entry for the whatever cash is paid
                                $difference=$totalCredit-$request->Data['paidBalance'];
                                $AccData =
                                    [
                                        'customer_id' => $request->Data['customer_id'],
                                        'Credit' => $request->Data['paidBalance'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['SaleDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'PartialCashSales|'.$Id,
                                        'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            }
                            // fully paid with cash or there may be some advance amount remains
                            else
                            {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                //make credit entry for the sales
                                $AccountTransactions=AccountTransaction::Create([
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $totalCredit,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'Sales|'.$Id,
                                    'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                ]);

                                //make debit entry for the whatever cash is paid
                                $difference=$difference-$request->Data['paidBalance'];
                                $AccountTransactions=AccountTransaction::Create([
                                    'customer_id' => $request->Data['customer_id'],
                                    'Credit' => $request->Data['paidBalance'],
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['SaleDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'FullCashSales|'.$Id,
                                    'referenceNumber'=>'P#'.$request->Data['orders'][0]['PadNumber'],
                                ]);
                            }
                            /*new entry*/
                        }
                    }


                    //identify if only and only date is changing
                    if($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal==$request->Data['grandTotal'] AND $request->Data['SaleDate']!=$sold->SaleDate)
                    {
                        $description_string='Sales|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                        if($previous_entry)
                        {
                            AccountTransaction::where('id', $previous_entry->id)->update(array('createdDate' => $request->Data['SaleDate']));
                        }
                    }
                }
                ////////////////// end of account section gautam ////////////////

                if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0) {
                    $isPaid = false;
                    $partialPaid =false;
                }
                elseif($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                {
                    $isPaid = true;
                    $partialPaid =false;
                }
                else
                {
                    $isPaid = false;
                    $partialPaid =true;
                }

                $sold->update([
                    'SaleNumber' => $request->Data['SaleNumber'],
                    'SaleDate' => $request->Data['SaleDate'],
                    'Total' => $request->Data['Total'],
                    'subTotal' => $request->Data['subTotal'],
                    'totalVat' => $request->Data['totalVat'],
                    'grandTotal' => $request->Data['grandTotal'],
                    'paidBalance' => $request->Data['paidBalance'],
                    'remainingBalance' => $request->Data['grandTotal'],
                    'customer_id' => $request->Data['customer_id'],
                    'Description' => $request->Data['Description'],
                    'IsPaid' => $isPaid,
                    'IsPartialPaid' => $partialPaid,
                    'IsReturn' => false,
                    'IsPartialReturn' => false,
                    'IsNeedStampOrSignature' => false,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'sales';
                $update_note->RelationId = $Id;
                $update_note->Description = $request->Data['UpdateDescription'];
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                SaleDetail::where('sale_id', array($Id))->delete();
                //echo "<pre>";print_r($request->all());die;
                foreach ($request->Data['orders'] as $detail)
                {
                    $saleDetails = SaleDetail::create([
                        //"Id" => $detail['Id'],
                        "product_id"        => $detail['product_id'],
                        "unit_id"        => $detail['unit_id'],
                        "vehicle_id"        => $detail['vehicle_id'],
                        "Quantity"        => $detail['Quantity'],
                        "Price"        => $detail['Price'],
                        "rowTotal"        => $detail['rowTotal'],
                        "VAT"        => $detail['Vat'],
                        "rowVatAmount"        => $detail['rowVatAmount'],
                        "rowSubTotal"        => $detail['rowSubTotal'],
                        "PadNumber"        => $detail['PadNumber'],
                        "company_id" => $company_id,
                        "user_id"      => $user_id,
                        "sale_id"      => $Id,
                        "createdDate" => $detail['createdDate'],
                        "customer_id" => $request->Data['customer_id'],
                    ]);
                }

                // check if this entry has anything to do with advance booking detail entry
                /*$advance_booking_detail=CustomerAdvanceBookingDetail::where('sale_id',$Id)->first();
                if($advance_booking_detail)
                {
                    //now need to make changes only if the new quantity is coming less than booking detail quantity
                    if($request['Data']['orders'][0]['Quantity']<$advance_booking_detail->Quantity)
                    {
                        //get parent entry and make respective changes
                        $parent=CustomerAdvanceBooking::where('id',$advance_booking_detail->booking_id)->first();
                        if($parent)
                        {
                            $data=[
                                'consumedQuantity' => $parent->consumedQuantity-($advance_booking_detail->Quantity-$request['Data']['orders'][0]['Quantity']),
                                'remainingQuantity' => $parent->remainingQuantity+($advance_booking_detail->Quantity-$request['Data']['orders'][0]['Quantity']),
                                'user_id' => $user_id];
                            //echo "coming here<pre>";print_r($advance_booking_detail);die;
                            $parent->update($data);

                        }
                        $advance_booking_detail->update([
                            'Quantity' => $request['Data']['orders'][0]['Quantity'],
                            'user_id' => $user_id,
                        ]);
                    }
                }

                $booking_rem_quantity=CustomerAdvanceBooking::where('customer_id',$request->Data['customer_id'])->sum('remainingQuantity');
                if($booking_rem_quantity)
                {
                    if($booking_rem_quantity>0)
                    {
                        if(isset($request->Data['orders'][0]['Quantity']))
                        {
                            $bookings=CustomerAdvanceBooking::where('customer_id',$request->Data['customer_id'])->get();
                            foreach ($bookings as $single)
                            {
                                $total_you_need = $request->Data['orders'][0]['Quantity'];
                                $total_i_have=$single->remainingQuantity;
                                if ($total_i_have >= $total_you_need)
                                {
                                    $remaining_i_have = $total_i_have - $total_you_need;
                                    $single->update([
                                        "remainingQuantity"=> $remaining_i_have,
                                        "consumedQuantity"=> $single->consumedQuantity+$total_you_need,
                                    ]);
                                    CustomerAdvanceBookingDetail::create([
                                        "Quantity" => $total_you_need,
                                        "user_id" => $user_id,
                                        "company_id" => $company_id,
                                        "customer_id" => $single->customer_id,
                                        "booking_id" => $single->id,
                                        'sale_id' => $Id,
                                        'BookingDate' => $single->BookingDate,
                                        'PadNumber' => $request->Data['orders'][0]['PadNumber'],
                                    ]);
                                }
                                else
                                {
                                    $single->update([
                                        "remainingQuantity"=> 0,
                                        "consumedQuantity"=> $single->consumedQuantity+$total_you_need,
                                    ]);
                                    CustomerAdvanceBookingDetail::create([
                                        "Quantity" => $total_i_have,
                                        "user_id" => $user_id,
                                        "company_id" => $company_id,
                                        "customer_id" => $single->customer_id,
                                        "booking_id" => $single->id,
                                        'sale_id' => $Id,
                                        'BookingDate' => $single->BookingDate,
                                        'PadNumber' => $request->Data['orders'][0]['PadNumber'],
                                    ]);
                                }
                            }
                        }
                    }
                }*/
                // check if this entry has anything to do with advance booking detail entry
                $ss = SaleDetail::where('sale_id', array($saleDetails['sale_id']))->get();
                return Response()->json($ss);
            }
        });
    }

    public function salesServiceUpdate(Request $request, $Id)
    {
        //echo "<pre>";print_r($request->all());die;
        $AllRequestCount = collect($request->Data)->count();
        DB::transaction(function () use($AllRequestCount,$request,$Id)
        {
            if($AllRequestCount > 0)
            {
                $sold = Sale::with('customer.account_transaction')->find($Id);
                $user_id = session('user_id');
                $company_id = session('company_id');
                $PadNumber=$request['Data']['PadNumber'];

                ////////////////// account section gautam ////////////////
                $accountTransaction = AccountTransaction::where(['customer_id'=> $sold->customer_id,])->get();
                if (!is_null($accountTransaction))
                {
                    if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                    {
                        // check if only grand total is changed and not the customer
                        if($request->Data['customer_id']==$sold->customer_id  AND $sold->grandTotal!=$request->Data['grandTotal'])
                        {
                            //customer is not changed then need to find what is the differance in total and for payment changes
                            // also need to manage isPaid and isPartialPaid flag according

                            //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                            if($sold->IsPaid==0 && $sold->IsPartialPaid==0)
                            {
                                // start reverse entry
                                $last_closing=$accountTransaction->last()->Differentiate;
                                $description_string='Sales|'.$Id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$sold->customer_id)->where('Description','like',$description_string)->last();
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'customer_id' => $sold->customer_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing-$previously_debited,
                                        'createdDate' => date('Y-m-d'),
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description'=>'Sales|'.$Id,
                                        'updateDescription'=>'hide',
                                        'referenceNumber'=>'P#'.$PadNumber,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                            }

                            /*new entry*/
                            // start new entry for updated customer with checking all three cases
                            $accountTransaction = AccountTransaction::where(['customer_id'=> $request->Data['customer_id'],])->get();
                            // totally credit

                            $totalCredit = $request->Data['grandTotal'];
                            $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                            $AccData =[
                                'customer_id' => $request->Data['customer_id'],
                                'Credit' => 0.00,
                                'Debit' => $totalCredit,
                                'Differentiate' => $difference,
                                'createdDate' => $request->Data['SaleDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Sales|'.$Id,
                                'referenceNumber'=>'P#'.$PadNumber,
                            ];
                            AccountTransaction::Create($AccData);
                            /*new entry*/
                        }
                    }

                }
                ////////////////// end of account section gautam ////////////////
                $sold->update([
                    'SaleNumber' => $sold->SaleNumber,
                    'SaleDate' => $request->Data['SaleDate'],
                    'Total' => $request->Data['subTotal'],
                    'subTotal' => $request->Data['subTotal'],
                    'totalVat' => $request->Data['totalVat'],
                    'grandTotal' => $request->Data['grandTotal'],
                    'remainingBalance' => $request->Data['grandTotal'],
                    'customer_id' => $request->Data['customer_id'],
                    'IsPaid' => false,
                    'IsPartialPaid' => false,
                    'IsReturn' => false,
                    'IsPartialReturn' => false,
                    'IsNeedStampOrSignature' => false,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'sales';
                $update_note->RelationId = $Id;
                $update_note->Description = $request->Data['UpdateDescription'];
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                SaleDetail::where('sale_id', array($Id))->delete();
                foreach ($request->Data['orders'] as $detail)
                {
                    $saleDetails = SaleDetail::create([
                        "product_id" => $detail['product_id'],
                        "Description" => $detail['Description'],
                        "unit_id" => $detail['unit_id'],
                        "Quantity" => $detail['Quantity'],
                        "Price" => $detail['Price'],
                        "rowTotal" => $detail['rowTotal'],
                        "VAT" => $detail['VAT'],
                        "rowVatAmount" => $detail['rowVatAmount'],
                        "rowSubTotal" => $detail['rowSubTotal'],
                        "PadNumber" => $PadNumber,
                        "company_id" => $company_id,
                        "user_id" => $user_id,
                        "sale_id" => $Id,
                        "createdDate" => $request->Data['SaleDate'],
                        "customer_id" => $request->Data['customer_id'],
                    ]);
                }
                $data=array('result'=>true,'message'=>'Record Updated Successfully.');
                echo json_encode($data);
                //$ss = SaleDetail::where('sale_id', array($saleDetails['sale_id']))->get();
                //return Response()->json($ss);
            }
        });
    }

    public function getSalesPaymentDetail($Id)
    {
        $rows_string='';
        $i=0;
        $payment_detail=PaymentReceiveDetail::where('sale_id',$Id)->get();
        if($payment_detail->first())
        {
            foreach ($payment_detail as $single)
            {
                $parent=PaymentReceive::where('id',$single->payment_receive_id)->first();
                $rows_string.='<tr>';
                $rows_string.='<td>'.++$i.'</td>';
                $rows_string.='<td>'.$single->amountPaid??"NA".'</td>';
                $rows_string.='<td>'.date('d-M-Y',strtotime($parent->transferDate))??"NA".'</td>';
                $rows_string.='<td>'.$parent->referenceNumber??"NA".'</td>';
                $rows_string.='<td>'.$parent->payment_type??"NA".'</td>';
                $rows_string.='<td>Payment</td>';
                $rows_string.='</tr>';
            }
        }
        $payment_detail=CustomerAdvanceDetail::where('sale_id',$Id)->get();
        if($payment_detail->first())
        {
            foreach ($payment_detail as $single)
            {
                $parent=CustomerAdvance::where('id',$single->customer_advances_id)->first();
                $rows_string.='<tr>';
                $rows_string.='<td>'.++$i.'</td>';
                $rows_string.='<td>'.$single->amountPaid??"NA".'</td>';
                $rows_string.='<td>'.date('d-M-Y',strtotime($parent->TransferDate))??"NA".'</td>';
                $rows_string.='<td>'.$parent->receiptNumber??"NA".'</td>';
                $rows_string.='<td>'.$parent->paymentType??"NA".'</td>';
                $rows_string.='<td>Advance</td>';
                $rows_string.='</tr>';
            }
        }
        $payment_detail=CashTransaction::where('Type','sales')->where('Reference',$Id)->whereNull('deleted_at')->get();
        if($payment_detail->first())
        {
            foreach ($payment_detail as $single)
            {
                $rows_string.='<tr>';
                $rows_string.='<td>'.++$i.'</td>';
                $rows_string.='<td>'.$single->Debit??"NA".'</td>';
                $rows_string.='<td>'.date('d-M-Y',strtotime($single->createdDate))??"NA".'</td>';
                $rows_string.='<td>'.$single->PadNumber??"NA".'</td>';
                $rows_string.='<td>'.$single->Details??"NA".'</td>';
                $rows_string.='<td>Cash</td>';
                $rows_string.='</tr>';
            }
        }
        $html='';
        $sales=Sale::where('id',$Id)->with('customer','sale_details')->first();
        if($sales)
        {
            $html='<div class="row"><div class="col-md-12"><label>Customer Name : '.$sales->customer->Name.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>Sales Date : '.date('d-M-Y',strtotime($sales->SaleDate)).'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>PAD# : '.$sales->sale_details[0]->PadNumber.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>VEHICLE# : '.$sales->customer->Name.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>Amount : '.$sales->grandTotal.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>Paid : '.$sales->paidBalance.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>Remaining : '.$sales->remainingBalance.'</label></div></div>';
            $html.='<table class="table table-sm"><thead><th>SR</th><th>Paid Amount</th><th>Date</th><th>REF#</th><th>PaymentMode</th><th>Type</th></thead><tbody>';
            $html.=$rows_string;
            $html.='</tbody>';
        }
        else
        {
            $html.='<h1>Sales Record Not Fount.</h1>';
        }
        return Response()->json($html);
    }

    public function getSalesQuantityChart()
    {
        return view('admin.sale.get_sales_quantity_chart');
    }

    public function printSalesQuantityChart(Request $request)
    {
        $begin = new DateTime($request->fromDate);
        $end   = new DateTime($request->toDate);
        $all_dates=array();
        $all_qty=array();
        for($i = $begin; $i <= $end; $i->modify('+1 day'))
        {
            $all_dates[]=$i->format("d-m-Y");
            $date=$i->format("Y-m-d");
            $qty=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('Quantity');
            $all_qty[]=$qty;
        }
        $title='Sales(in IG) From '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        return view('admin.sale.print_sales_quantity_chart',compact('all_dates','all_qty','title'));
    }

    public function getSalesQuantityChartCustomer()
    {
        $customers = Customer::where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
        return view('admin.sale.get_customer_sales_quantity_chart',compact('customers'));
    }

    public function printSalesQuantityChartCustomer(Request $request)
    {
        $begin = new DateTime($request->fromDate);
        $end   = new DateTime($request->toDate);
        $all_dates=array();
        $all_qty=array();
        $all_amount=array();
        for($i = $begin; $i <= $end; $i->modify('+1 day'))
        {
            $all_dates[]=$i->format("d-m-Y");
            $date=$i->format("Y-m-d");
            $qty=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->where('customer_id',$request->customer_id)->sum('Quantity');
            $amt=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->where('customer_id',$request->customer_id)->sum('rowSubTotal');
            $all_qty[]=$qty;
            $all_amount[]=$amt;
        }
        $customer_name=Customer::select('Name')->where('id',$request->customer_id)->first();
        $title=$customer_name->Name.' Sales From '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        return view('admin.sale.print_customer_sales_quantity_chart',compact('all_dates','all_qty','all_amount','title'));
    }

    public function CheckPadExist($request)
    {
        $data = SaleDetail::where('PadNumber','=',$request->PadNumber)->where('company_id','=',session('company_id'))->get();
        if($data->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function CheckVehicleStatus($request)
    {
        $data = Vehicle::with(['customer'=>function($q){$q->select('id','Name');}])->where('registrationNumber','=',$request->CheckVehicle)->where('company_id','=',session('company_id'))->first();

        $last_filled_message='';
        if($data)
        {
            $max_sales_id = SaleDetail::where('vehicle_id',$data->id)->max('id');
            $sales_id=SaleDetail::select('sale_id')->where('id',$max_sales_id)->first();
            if($sales_id)
            {
                $last_filled= Sale::with(['sale_details.vehicle','customer'])->where('id',$sales_id->sale_id)->first();
                $last_filled_message='Last Filled on '.date('d-m-y',strtotime($last_filled->SaleDate)).' PAD : '.$last_filled->sale_details[0]->PadNumber.' Qty : '.$last_filled->sale_details[0]->Quantity.' Rate : '.$last_filled->sale_details[0]->Price.' Remaining : '.$last_filled->remainingBalance;
            }
        }
        if(isset($data->isActive))
        {
            $result=array('result'=>true,'status'=>$data->isActive,'customer'=>'<a href="/vehicles" style="color: inherit;">'.$data->customer->Name.'</a>','last_filled'=>$last_filled_message);
            return Response()->json($result);
        }
        else
        {
            $result=array('result'=>false,'status'=>0,'customer'=>'');
            return Response()->json($result);
        }
    }

    public function edit($Id)
    {
        $sales = Sale::findOrFail($Id);
        if($sales->TermsAndCondition==0)
        {
            return redirect()->route('sales.index');
        }
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'sales'])->get();
        $customers = Customer::where('company_id',session('company_id'))->where('id',$sales->customer_id)->get();
        $products = Product::all();
        $units = Unit::all();
        $sale_details = SaleDetail::withTrashed()->with('sale.customer.customer_prices','user','product','unit','vehicle','customer')->where('sale_id', $Id)->get();
        return view('admin.sale.edit',compact('sale_details','customers','products','update_notes','units','sales'));
    }

    public function edit_sale_service($Id)
    {
        $sales = Sale::findOrFail($Id);
        if($sales->TermsAndCondition==0)
        {
            return redirect()->route('sales.index');
        }
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'sales'])->get();
        $customers = Customer::where('company_id',session('company_id'))->where('id',$sales->customer_id)->get();
        $products = Product::all();
        $units = Unit::all();
        $sale_details = SaleDetail::with('sale.customer.customer_prices','user','product','unit','vehicle','customer')->where('sale_id', $Id)->get();
        return view('admin.sale.edit_sales_as_service',compact('sales','sale_details','customers','products','update_notes','units'));
    }

    public function delete($Id)
    {
        $sales = Sale::findOrFail($Id);
        if($sales->supplierNote==0)
        {
            return redirect()->route('sales.index');
        }
        $user_id = session('user_id');
        $company_id = session('company_id');
        if($sales)
        {
            // fully credit sales payment not done
            if($sales->paidBalance==0.00)
            {
                DB::transaction(function () use($sales,$user_id,$company_id)
                {
                    if($sales->referenceNumber!=null)
                    {
                        // this sales entry belongs to booking
                        $booking = CustomerAdvanceBooking::where('id', $sales->referenceNumber)->first();
                        $detail = CustomerAdvanceBookingDetail::where('sale_id', array($sales->id))->first();
                        $booking_consumed = $booking->consumedQuantity;
                        $booking_rem = $booking->remainingQuantity;
                        $updated_con = $booking_consumed - $detail->Quantity;
                        $updated_rem = $booking_rem + $detail->Quantity;

                        $booking->update([
                            'consumedQuantity' => $updated_con,
                            'remainingQuantity' => $updated_rem,
                        ]);
                        $detail->delete();
                    }

                    SaleDetail::where('sale_id', '=', $sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id]);
                    SaleDetail::where('sale_id', '=', $sales->id)->where('company_id', '=', $company_id)->delete();

                    AccountTransaction::where('Description', 'like', 'Sales|' . $sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,'updateDescription'=>'hide']);
                    AccountTransaction::where('Description', 'like', 'Sales|' . $sales->id)->where('company_id', '=', $company_id)->delete();

                    $sales->update(['user_id' => $user_id,]);
                    $sales->delete();


                });
                return redirect()->route('sales.index');
            }
            elseif($sales->IsPaid==1)
            {
                $transaction=AccountTransaction::where('Description','like','FullCashSales|'.$sales->id)->where('company_id','=',$company_id)->get();
                if($transaction->first())
                {
                    //full cash sales delete entries
                    DB::transaction(function () use($sales,$user_id,$company_id)
                    {
                        if($sales->referenceNumber!=null)
                        {
                            // this sales entry belongs to booking
                            $booking = CustomerAdvanceBooking::where('id', $sales->referenceNumber)->first();
                            $detail = CustomerAdvanceBookingDetail::where('sale_id', array($sales->id))->first();
                            $booking_consumed = $booking->consumedQuantity;
                            $booking_rem = $booking->remainingQuantity;
                            $updated_con = $booking_consumed - $detail->Quantity;
                            $updated_rem = $booking_rem + $detail->Quantity;

                            $booking->update([
                                'consumedQuantity' => $updated_con,
                                'remainingQuantity' => $updated_rem,
                            ]);
                            $detail->delete();
                        }

                        AccountTransaction::where('Description', 'like', 'Sales|' . $sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,'updateDescription'=>'hide']);
                        AccountTransaction::where('Description', 'like', 'Sales|' . $sales->id)->where('company_id', '=', $company_id)->delete();
                        AccountTransaction::where('Description', 'like', 'FullCashSales|' . $sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,'updateDescription'=>'hide']);
                        AccountTransaction::where('Description', 'like', 'FullCashSales|' . $sales->id)->where('company_id', '=', $company_id)->delete();
                        Sale::where('id',$sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                        Sale::where('id',$sales->id)->where('company_id', '=', $company_id)->delete();
                        SaleDetail::where('sale_id',$sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                        SaleDetail::where('sale_id',$sales->id)->where('company_id', '=', $company_id)->delete();
                        CashTransaction::where('Details','like','CashSales|'.$sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                        CashTransaction::where('Details','like','CashSales|'.$sales->id)->where('company_id', '=', $company_id)->delete();
                    });
                    return redirect()->route('sales.index');
                }
            }
            return redirect()->route('sales.index');
        }
        else
        {
            return redirect()->route('sales.index');
        }
    }

    public function sales_delete_post(Request $request)
    {
        $sales = Sale::findOrFail($request->sales_id);
        if($sales->supplierNote==0)
        {
            return redirect()->route('sales.index');
        }
        $user_id = session('user_id');
        $company_id = session('company_id');
        if($sales)
        {
            // fully credit sales payment not done
            if($sales->paidBalance==0.00)
            {
                DB::transaction(function () use($sales,$user_id,$company_id,$request)
                {
                    if($sales->referenceNumber!=null)
                    {
                        // this sales entry belongs to booking
                        $booking = CustomerAdvanceBooking::where('id', $sales->referenceNumber)->first();
                        $detail = CustomerAdvanceBookingDetail::where('sale_id', array($sales->id))->first();
                        $booking_consumed = $booking->consumedQuantity;
                        $booking_rem = $booking->remainingQuantity;
                        $updated_con = $booking_consumed - $detail->Quantity;
                        $updated_rem = $booking_rem + $detail->Quantity;

                        $booking->update([
                            'consumedQuantity' => $updated_con,
                            'remainingQuantity' => $updated_rem,
                        ]);
                        $detail->delete();
                    }

                    SaleDetail::where('sale_id', '=', $sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id]);
                    SaleDetail::where('sale_id', '=', $sales->id)->where('company_id', '=', $company_id)->delete();

                    AccountTransaction::where('Description', 'like', 'Sales|' . $sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,'updateDescription'=>'hide']);
                    AccountTransaction::where('Description', 'like', 'Sales|' . $sales->id)->where('company_id', '=', $company_id)->delete();

                    $sales->update(['user_id' => $user_id,]);
                    $sales->delete();

                    $update_note = new UpdateNote();
                    $update_note->RelationTable = 'sales';
                    $update_note->RelationId = $request->sales_id;
                    $update_note->UpdateDescription = $request->deleteDescription;
                    $update_note->user_id = $user_id;
                    $update_note->company_id = $company_id;
                    $update_note->save();
                });
                return Response()->json(true);
            }
            elseif($sales->IsPaid==1)
            {
                $transaction=AccountTransaction::where('Description','like','FullCashSales|'.$sales->id)->where('company_id','=',$company_id)->get();
                if($transaction->first())
                {
                    //full cash sales delete entries
                    DB::transaction(function () use($sales,$user_id,$company_id,$request)
                    {
                        if($sales->referenceNumber!=null)
                        {
                            // this sales entry belongs to booking
                            $booking = CustomerAdvanceBooking::where('id', $sales->referenceNumber)->first();
                            $detail = CustomerAdvanceBookingDetail::where('sale_id', array($sales->id))->first();
                            $booking_consumed = $booking->consumedQuantity;
                            $booking_rem = $booking->remainingQuantity;
                            $updated_con = $booking_consumed - $detail->Quantity;
                            $updated_rem = $booking_rem + $detail->Quantity;

                            $booking->update([
                                'consumedQuantity' => $updated_con,
                                'remainingQuantity' => $updated_rem,
                            ]);
                            $detail->delete();
                        }

                        AccountTransaction::where('Description', 'like', 'Sales|' . $sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,'updateDescription'=>'hide']);
                        AccountTransaction::where('Description', 'like', 'Sales|' . $sales->id)->where('company_id', '=', $company_id)->delete();
                        AccountTransaction::where('Description', 'like', 'FullCashSales|' . $sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,'updateDescription'=>'hide']);
                        AccountTransaction::where('Description', 'like', 'FullCashSales|' . $sales->id)->where('company_id', '=', $company_id)->delete();
                        Sale::where('id',$sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                        Sale::where('id',$sales->id)->where('company_id', '=', $company_id)->delete();
                        SaleDetail::where('sale_id',$sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                        SaleDetail::where('sale_id',$sales->id)->where('company_id', '=', $company_id)->delete();
                        CashTransaction::where('Details','like','CashSales|'.$sales->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                        CashTransaction::where('Details','like','CashSales|'.$sales->id)->where('company_id', '=', $company_id)->delete();

                        $update_note = new UpdateNote();
                        $update_note->RelationTable = 'sales';
                        $update_note->RelationId = $request->sales_id;
                        $update_note->UpdateDescription = $request->deleteDescription;
                        $update_note->user_id = $user_id;
                        $update_note->company_id = $company_id;
                        $update_note->save();
                    });
                    return Response()->json(true);
                }
            }
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function invoiceNumber()
    {
        $invoice = new Sale();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'INV-00'.($lastInvoiceID + 1);
        return $newInvoiceID;
    }

    public function PadNumber()
    {
        // pad number according to max sales id
        $data=array();
        $max_sales_id = SaleDetail::where('company_id',session('company_id'))->max('id');
        $max_sales_id = SaleDetail::where('id',$max_sales_id)->first();
        if($max_sales_id)
        {
            $lastPad = $max_sales_id->PadNumber;
            $lastDate = $max_sales_id->createdDate;
            if(!is_numeric($lastPad))
            {
                $res = preg_replace("/[^0-9]/", "", $lastPad );
                $data['pad_no']=$res+1;
                $data['last_date']=$lastDate;
            }
            else
            {
                $data['pad_no']=$lastPad + 1;
                $data['last_date']=$lastDate;
            }
        }
        else
        {
            $data['pad_no']=1;
            $data['last_date']=date('Y-m-d');
        }
        return $data;
    }
}
