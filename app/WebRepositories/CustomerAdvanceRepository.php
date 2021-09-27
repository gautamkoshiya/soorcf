<?php


namespace App\WebRepositories;


use App\Http\Requests\CustomerAdvanceRequest;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\CustomerAdvanceDetail;
use App\Models\PaymentReceive;
use App\Models\Sale;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerAdvanceRepository implements ICustomerAdvanceRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(CustomerAdvance::with('user','customer')->where('company_id',session('company_id'))->where('isActive',1)->latest()->get())
                ->addColumn('customer', function($data) {
                    return $data->customer->Name ?? "No Data";
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<a href="'.route('customer_advances.edit', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        $button .='&nbsp;';
                        $button .= '<button class="btn btn-danger btn-sm" onclick="push_payment(this.id)" type="button" id="pay_'.$data->id.'"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        //$button .= '<button class="btn btn-danger" onclick="cancel_customer_advance(this.id)" type="button" id="cancel_'.$data->id.'">Cancel</button>';
                        $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="paymentDelete btn btn-danger btn-sm" data-target="#deleteCustomerAdvance"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        //$button .= '<button class="btn btn-danger" onclick="cancel_customer_advance(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                        $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="paymentDelete btn btn-danger btn-sm" data-target="#deleteCustomerAdvance"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                        return $button;
                    }
                })
                ->addColumn('disburse', function($data) {
                    if($data->isPushed == true){
                        if($data->IsSpent == 0){
                            $button = '<a href="'.route('customer_advances_get_disburse', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-battery-full"> Disburse</i></a>';
                            $button .= '&nbsp;&nbsp;';
                            $button .= '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                            return $button;
                        }else{
                            $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-battery-empty"> Disbursed</i></button>';
                            $button .= '&nbsp;&nbsp;';
                            $button .= '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                            return $button;
                        }
                    }
                    else
                    {
                        return 'Not Available';
                    }
                })
                ->addColumn('TransferDate', function($data) {
                    return date('d-m-Y', strtotime($data->TransferDate)) ?? "No date";
                })
                ->rawColumns(
                    [
                        'receiptNumber',
                        'push',
                        'disburse',
                        'customer',
                        'TransferDate',
                    ])
                ->make(true);
        }
        return view('admin.customerAdvance.index');
    }

    public function all_customer_advance(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = CustomerAdvance::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select ca.id,ca.Amount,ca.customer_id,ca.company_id,ca.bank_id,ca.accountNumber,ca.TransferDate,ca.paymentType,ca.receiptNumber,ca.receiverName,ca.Description,ca.IsSpent,ca.spentBalance,ca.remainingBalance,ca.isPushed,ca.isActive,ca.deleted_at,c.Name from customer_advances as ca left join customers as c on c.id = ca.customer_id  where ca.company_id = '.session('company_id').' and ca.isActive = 1 and ca.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $customer_advance = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select ca.id,ca.Amount,ca.customer_id,ca.company_id,ca.bank_id,ca.accountNumber,ca.TransferDate,ca.paymentType,ca.receiptNumber,ca.receiverName,ca.Description,ca.IsSpent,ca.spentBalance,ca.remainingBalance,ca.isPushed,ca.isActive,ca.deleted_at,c.Name from customer_advances as ca left join customers as c on c.id = ca.customer_id  where ca.company_id = '.session('company_id').' and ca.isActive = 1 and ca.deleted_at is NULL and ca.receiptNumber LIKE "%'.$search.'%" or c.Name LIKE "%'.$search.'%" and ca.deleted_at is NULL  order by id desc limit '.$limit.' offset '.$start ;
            $customer_advance = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,ca.id,ca.Amount,ca.customer_id,ca.company_id,ca.bank_id,ca.accountNumber,ca.TransferDate,ca.paymentType,ca.receiptNumber,ca.receiverName,ca.Description,ca.IsSpent,ca.spentBalance,ca.remainingBalance,ca.isPushed,ca.isActive,ca.deleted_at,c.Name from customer_advances as ca left join customers as c on c.id = ca.customer_id  where ca.company_id = '.session('company_id').' and ca.isActive = 1 and ca.deleted_at is null and ca.receiptNumber LIKE "%'.$search.'%" or c.Name LIKE "%'.$search.'%" and ca.deleted_at is NULL order by id desc limit '.$limit.' offset '.$start ;
            $customer_advance_count = DB::select(DB::raw($sql_count));
            if(!empty($customer_advance_count))
            {
                $totalFiltered = $customer_advance_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($customer_advance))
        {
            foreach ($customer_advance as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['customer'] = $single->Name ?? "N.A.";
                $nestedData['Amount'] = $single->Amount ?? 0.00;
                $nestedData['spentBalance'] = $single->spentBalance ?? 0.00;
                $nestedData['remainingBalance'] = '<span style="color:green;">'.$single->remainingBalance ?? 0.00.'</span>';
                $nestedData['paymentType'] = $single->paymentType ?? 'N.A.';
                $nestedData['TransferDate'] = date('d-m-Y', strtotime($single->TransferDate));
                $nestedData['Description'] = mb_strimwidth($single->Description, 0, 50, '...') ?? "N.A.";
                $nestedData['receiptNumber'] = $single->receiptNumber ?? "N.A.";
                $nestedData['payment_type'] = $single->payment_type ?? 0.00;
                $push='';
                if($single->isPushed == false)
                {
                    $push = '<a href="'.route('customer_advances.edit', $single->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $push .='&nbsp;';
                    $push .= '<button class="btn btn-danger btn-sm" onclick="push_payment(this.id)" type="button" id="pay_'.$single->id.'"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                    $push .= '&nbsp;&nbsp;';
                    $push.='<a href="#" data-id="'.$single->id.'" data-toggle="modal" class="paymentDelete btn btn-danger btn-sm" data-target="#deleteCustomerAdvance"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                }
                else
                {
                    $push = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                    $push .= '&nbsp;&nbsp;';
                    $push.='<a href="#" data-id="'.$single->id.'" data-toggle="modal" class="paymentDelete btn btn-danger btn-sm" data-target="#deleteCustomerAdvance"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                }
                $nestedData['push']=$push;
                $button='';
                if($single->isPushed == true)
                {
                    if($single->remainingBalance != 0)
                    {
                        $button.= '<a href="'.route('customer_advances_get_disburse', $single->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-battery-full"> Disburse</i></a>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                    }
                    else
                    {
                        $button.= '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-battery-empty"> Disbursed</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                    }
                }
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
        $customers = Customer::where('company_id',session('company_id'))->get();
        $banks = Bank::where('company_id',session('company_id'))->get();
        return view('admin.customerAdvance.create',compact('customers','banks'));
    }

    public function store(CustomerAdvanceRequest $customerAdvanceRequest)
    {
        DB::transaction(function () use($customerAdvanceRequest)
        {
            $user_id = session('user_id');
            $company_id = session('company_id');

            $advance = [
                'receiptNumber' =>preg_replace("/\s+/", "", $customerAdvanceRequest->receiptNumber),
                'paymentType' =>$customerAdvanceRequest->paymentType,
                'Amount' =>$customerAdvanceRequest->amount,
                'spentBalance' =>0.00,
                'remainingBalance' =>$customerAdvanceRequest->amount,
                'IsSpent' =>0,
                'IsPartialSpent' =>0,
                'sumOf' =>$customerAdvanceRequest->amountInWords,
                'receiverName' =>$customerAdvanceRequest->receiverName,
                'accountNumber' =>$customerAdvanceRequest->accountNumber ?? 0,
                'ChequeNumber' =>$customerAdvanceRequest->ChequeNumber,
                'TransferDate' =>$customerAdvanceRequest->TransferDate ?? 0,
                'registerDate' =>$customerAdvanceRequest->registerDate,
                'bank_id' =>$customerAdvanceRequest->bank_id ?? 0,
                'user_id' =>$user_id,
                'company_id' =>$company_id,
                'customer_id' =>$customerAdvanceRequest->customer_id ?? 0,
                'Description' =>$customerAdvanceRequest->Description,
            ];
            CustomerAdvance::create($advance);
        });
        return redirect()->route('customer_advances.index');
    }

    public function update(Request $request, $Id)
    {
        DB::transaction(function () use($request,$Id)
        {
            $advance = CustomerAdvance::find($Id);

            $user_id = session('user_id');
            $advance->update([
                'receiptNumber' =>preg_replace("/\s+/", "", $request->receiptNumber),
                'paymentType' =>$request->paymentType,
                'Amount' =>$request->amount,
                'spentBalance' =>0.00,
                'remainingBalance' =>$request->amount,
                'IsSpent' =>0,
                'IsPartialSpent' =>0,
                'sumOf' =>$request->amountInWords,
                'receiverName' =>$request->receiverName,
                'accountNumber' =>$request->accountNumber ?? null,
                'ChequeNumber' =>$request->ChequeNumber ?? 0,
                'TransferDate' =>$request->TransferDate,
                'registerDate' =>$request->registerDate,
                'bank_id' =>$request->bank_id ?? 0,
                'user_id' =>$user_id,
                'customer_id' =>$request->customer_id ?? null,
                'Description' =>$request->Description,
            ]);
        });
        return redirect()->route('customer_advances.index');
    }

    public function edit($Id)
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        $banks = Bank::where('company_id',session('company_id'))->get();
        $customerAdvance = CustomerAdvance::with('customer')->find($Id);
        return view('admin.customerAdvance.edit',compact('customers','customerAdvance','banks'));
    }

    public function CheckCustomerAdvanceReferenceExist($request)
    {
        $data = CustomerAdvance::where('company_id',session('company_id'))->where('receiptNumber','like','%'.$request->receiptNumber.'%')->get();
        $data1 = PaymentReceive::where('company_id',session('company_id'))->where('referenceNumber','like','%'.$request->receiptNumber.'%')->get();
        if($data->first() || $data1->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function customer_advances_get_disburse($Id)
    {
        $customerAdvance = CustomerAdvance::with('customer')->find($Id);
        return view('admin.customerAdvance.create_disburse',compact('customerAdvance'));
    }

    public function getById($Id)
    {
        $customer_advance_details = CustomerAdvanceDetail::with('user','company','customer_advance.customer')->where('customer_advances_id',$Id)->get();
        return view('admin.customerAdvance.show',compact('customer_advance_details'));
    }

    public function customer_advances_save_disburse(Request $request)
    {
        DB::transaction(function () use($request)
        {
            $user_id = session('user_id');
            $company_id = session('company_id');
            $AllRequestCount = collect($request->Data)->count();
            if($AllRequestCount > 0)
            {
                $advance = CustomerAdvance::with('customer')->find($request->Data['customer_advance_id']);
                if($advance->IsSpent==0 AND $advance->remainingBalance>0)
                {
                    $total_i_have=$advance->remainingBalance;

                    $total_spending=0.00;
                    foreach($request->Data['orders'] as $detail)
                    {
                        $this_sale=Sale::where('id',$detail['sale_id'])->get()->first();
                        if($this_sale->IsPaid==0 AND $this_sale->remainingBalance!=0)
                        {
                            $total_you_need = $this_sale->remainingBalance;
                            $isPartialPaid = 0;
                            if ($total_i_have >= $total_you_need)
                            {
                                $isPaid = 1;
                                $total_i_have = $total_i_have - $total_you_need;

                                $this_sale->update([
                                    "paidBalance"        => $this_sale->paidBalance+$total_you_need,
                                    "remainingBalance"   => $this_sale->remainingBalance-$total_you_need,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "TermsAndCondition"=>0,
                                    "supplierNote"=>0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'FromAdvance|'.$advance->id,
                                ]);

                                CustomerAdvanceDetail::create([
                                    "amountPaid" => $total_you_need,
                                    "customer_advances_id" => $advance->id,
                                    "user_id" => $user_id,
                                    "company_id" => $company_id,
                                    "sale_id" => $this_sale->id,
                                    'advanceReceiveDetailDate' => $advance->TransferDate,
                                    'createdDate' => date('Y-m-d')
                                ]);
                                $total_spending+=$total_you_need;
                            }
                            else
                            {
                                $isPaid = 0;
                                $isPartialPaid = 1;
                                $total_giving_to_you=$total_i_have;
                                $total_i_have = $total_i_have - $total_giving_to_you;
                                $total_spending+=$total_giving_to_you;
                                $this_sale->update([
                                    "paidBalance"        => $this_sale->paidBalance+$total_giving_to_you,
                                    "remainingBalance"   => $this_sale->remainingBalance-$total_giving_to_you,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "TermsAndCondition"=>0,
                                    "supplierNote"=>0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'FromAdvance|'.$advance->id,
                                ]);

                                CustomerAdvanceDetail::create([
                                    "amountPaid" => $total_giving_to_you,
                                    "customer_advances_id" => $advance->id,
                                    "user_id" => $user_id,
                                    "company_id" => $company_id,
                                    "sale_id" => $this_sale->id,
                                    'advanceReceiveDetailDate' => $advance->TransferDate,
                                    'createdDate' => date('Y-m-d')
                                ]);
                            }
                        }
                        if($total_i_have<=0)
                        {
                            break;
                        }
                    }
                    if($total_spending!=0)
                    {
                        if($advance->remainingBalance-$total_spending<=0)
                        {
                            $advance->update([
                                'IsSpent' =>1,
                                'IsPartialSpent'=>0,
                                'spentBalance'=>$advance->spentBalance+$total_spending,
                                'remainingBalance'=>$advance->remainingBalance-$total_spending,
                                //'ChequeNumber'=>'one',
                            ]);
                        }
                        else
                        {
                            $advance->update([
                                'IsSpent' =>0,
                                'IsPartialSpent'=>1,
                                'spentBalance'=>$advance->spentBalance+$total_spending,
                                'remainingBalance'=>$advance->remainingBalance-$total_spending,
                                //'ChequeNumber'=>'three',
                            ]);
                        }
                    }
                    else
                    {
                        $advance->update([
                            'IsSpent' =>1,
                            'IsPartialSpent'=>0,
                            'spentBalance'=>$advance->Amount,
                            'remainingBalance'=>0,
                            //'ChequeNumber'=>'two',
                        ]);
                    }
                }
            }
        });
        return redirect()->route('customer_advances.index')->with('pushed','Your Account Debit Successfully');
    }

    public function getCustomerAdvanceDetail($Id)
    {
        $payment=CustomerAdvance::with(['customer'])->where('id',$Id)->first();
        $payment_detail=CustomerAdvanceDetail::with(['sale','sale.sale_details'])->where('customer_advances_id',$Id)->get();
        $html='<div class="row"><div class="col-md-12"><label>Customer Name : '.$payment->customer->Name.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Date : '.date('d-M-Y',strtotime($payment->TransferDate)).'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Type : '.$payment->paymentType.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Reference No. : '.$payment->receiptNumber.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Amount : '.$payment->Amount.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Amount (in words) : '.$payment->sumOf.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Description : '.$payment->Description.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Disbursed Amount  : '.$payment->spentBalance.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Remaining Amount : '.$payment->remainingBalance.'</label></div></div>';
        $html.='<div class="row text-right"><div class="col-md-12"><label>(created by : '.$payment->user->name.'-'.$payment->created_at.')</label></div></div>';
        $html.='<table class="table table-sm"><thead><th>SR</th><th>Disbursed</th><th>Sale Date</th><th>PAD</th><th>Total</th><th>Paid</th><th>Balance</th></thead><tbody>';
        $i=0;
        foreach ($payment_detail as $item)
        {

            $html.='<tr>';
            $html.='<td>'.++$i.'</td>';
            $html.='<td>'.$item->amountPaid??"NA".'</td>';
            $html.='<td>'.date('d-M-Y',strtotime($item->sale->SaleDate))??"NA".'</td>';
            $html.='<td>'.$item->sale->sale_details[0]->PadNumber??"NA".'</td>';
            $html.='<td>'.$item->sale->grandTotal??"NA".'</td>';
            $html.='<td>'.$item->sale->paidBalance??"NA".'</td>';
            $html.='<td>'.$item->sale->remainingBalance??"NA".'</td>';
            $html.='</tr>';
        }
        $html.='</tbody>';
        return Response()->json($html);
    }

    public function cancelCustomerAdvance($id)
    {
        $response=false;
        DB::transaction(function () use($id,&$response){
            $company_id = session('company_id');
            $payment=CustomerAdvance::with(['customer_advance_details'])->where('id',$id)->first();

            if($payment)
            {
                if($payment->isPushed==0)
                {
                    $payment->update(['user_id'=>session('user_id')]);
                    CustomerAdvance::where('id', array($id))->delete();
                    $response=true;
                }
                elseif($payment->isPushed==1)
                {
                    if($payment->IsSpent!=0 and $payment->IsPartialSpent!=0)
                    {
                        if($payment->paymentType == 'cash')
                        {
                            $description_string='CustomerCashAdvance|'.$id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $previous_probable_cash_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_cash_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'bank')
                        {
                            $description_string='CustomerBankAdvance|'.$id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'cheque')
                        {
                            $description_string='CustomerChequeAdvance|'.$id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }

                        $payment->update(['user_id'=>session('user_id')]);
                        CustomerAdvance::where('id', array($id))->delete();
                        $response=true;
                    }
                    else
                    {
                        foreach($payment->customer_advance_details as $single)
                        {
                            $sales=Sale::where('id',$single->sale_id)->get()->first();
                            $remaining_paid_balance=$sales->paidBalance-$single->amountPaid;
                            $updated_remaining_balance=$sales->remainingBalance+$single->amountPaid;

                            $TermsAndCondition=0;
                            $supplierNote=0;
                            if($remaining_paid_balance==0.00)
                            {
                                $is_paid=0;
                                $is_partial_paid=0;
                                $TermsAndCondition=1;
                                $supplierNote=1;
                            }
                            else
                            {
                                $is_paid=0;
                                $is_partial_paid=1;
                            }

                            $sales->update([
                                'paidBalance'=>$remaining_paid_balance,
                                'remainingBalance'=>$updated_remaining_balance,
                                'Description'=>null,
                                'IsPaid'=>$is_paid,
                                'IsPartialPaid'=>$is_partial_paid,
                                'TermsAndCondition'=>$TermsAndCondition,
                                'supplierNote'=>$supplierNote,
                            ]);

                        }
                        if($payment->paymentType == 'cash')
                        {
                            $description_string='CustomerCashAdvance|'.$id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $previous_probable_cash_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_cash_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'bank')
                        {
                            $description_string='CustomerBankAdvance|'.$id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'cheque')
                        {
                            $description_string='CustomerChequeAdvance|'.$id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }

                        CustomerAdvanceDetail::where('customer_advances_id',$id)->update(['user_id'=>session('user_id')]);
                        CustomerAdvanceDetail::where('customer_advances_id',$id)->delete();
                        $payment->update(['user_id'=>session('user_id')]);
                        CustomerAdvance::where('id', array($id))->delete();
                        $response=true;
                    }
                }
            }
        });
        return Response()->json($response);
    }

    public function customer_advance_delete_post(Request $request)
    {
        $response=false;
        DB::transaction(function () use($request,&$response){
            $company_id = session('company_id');
            $payment=CustomerAdvance::with(['customer_advance_details'])->where('id',$request->payment_id)->first();

            if($payment)
            {
                if($payment->isPushed==0)
                {
                    $payment->update(['user_id'=>session('user_id')]);
                    CustomerAdvance::where('id', array($request->payment_id))->delete();
                    $response=true;
                }
                elseif($payment->isPushed==1)
                {
                    if($payment->IsSpent!=0 and $payment->IsPartialSpent!=0)
                    {
                        if($payment->paymentType == 'cash')
                        {
                            $description_string='CustomerCashAdvance|'.$request->payment_id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $previous_probable_cash_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_cash_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'bank')
                        {
                            $description_string='CustomerBankAdvance|'.$request->payment_id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'cheque')
                        {
                            $description_string='CustomerChequeAdvance|'.$request->payment_id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }

                        $payment->update(['user_id'=>session('user_id')]);
                        CustomerAdvance::where('id', array($request->payment_id))->delete();
                        $response=true;
                    }
                    else
                    {
                        foreach($payment->customer_advance_details as $single)
                        {
                            $sales=Sale::where('id',$single->sale_id)->get()->first();
                            $remaining_paid_balance=$sales->paidBalance-$single->amountPaid;
                            $updated_remaining_balance=$sales->remainingBalance+$single->amountPaid;

                            $TermsAndCondition=0;
                            $supplierNote=0;
                            if($remaining_paid_balance==0.00)
                            {
                                $is_paid=0;
                                $is_partial_paid=0;
                                $TermsAndCondition=1;
                                $supplierNote=1;
                            }
                            else
                            {
                                $is_paid=0;
                                $is_partial_paid=1;
                            }

                            $sales->update([
                                'paidBalance'=>$remaining_paid_balance,
                                'remainingBalance'=>$updated_remaining_balance,
                                'Description'=>null,
                                'IsPaid'=>$is_paid,
                                'IsPartialPaid'=>$is_partial_paid,
                                'TermsAndCondition'=>$TermsAndCondition,
                                'supplierNote'=>$supplierNote,
                            ]);

                        }
                        if($payment->paymentType == 'cash')
                        {
                            $description_string='CustomerCashAdvance|'.$request->payment_id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $previous_probable_cash_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_cash_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'bank')
                        {
                            $description_string='CustomerBankAdvance|'.$request->payment_id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'cheque')
                        {
                            $description_string='CustomerChequeAdvance|'.$request->payment_id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }

                        CustomerAdvanceDetail::where('customer_advances_id',$request->payment_id)->update(['user_id'=>session('user_id')]);
                        CustomerAdvanceDetail::where('customer_advances_id',$request->payment_id)->delete();
                        $payment->update(['user_id'=>session('user_id')]);
                        CustomerAdvance::where('id', array($request->payment_id))->delete();
                        $response=true;

                        $update_note = new UpdateNote();
                        $update_note->RelationTable = 'customer_advances';
                        $update_note->RelationId = $request->payment_id;
                        $update_note->UpdateDescription = $request->deleteDescription;
                        $update_note->user_id = session('user_id');
                        $update_note->company_id = $company_id;
                        $update_note->save();
                    }
                }
            }
        });
        return Response()->json($response);
    }

    public function customer_advances_push(Request $request, $Id)
    {
        DB::transaction(function () use($request,$Id)
        {
            $advance = CustomerAdvance::with('customer')->find($Id);

            $user_id = session('user_id');
            $company_id = session('company_id');
            $advance->update([
                'isPushed' =>true,
                'user_id' =>$user_id,
            ]);

            if($advance->Amount>0)
            {
                $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
                $closing_before_advance_credit=$accountTransaction->last()->Differentiate;

                $accountTransaction_ref=0;
                // account section by gautam //
                if($advance->paymentType == 'cash')
                {
                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference=$Id;
                    $cash_transaction->createdDate=$advance->TransferDate;
                    $cash_transaction->Type='customer_advances';
                    $cash_transaction->Details='CustomerCashAdvance|'.$Id;
                    $cash_transaction->Credit=0.00;
                    $cash_transaction->Debit=$advance->Amount;
                    $cash_transaction->Differentiate=$difference+$advance->Amount;
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->PadNumber = $advance->receiptNumber;
                    $cash_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'customer_id' => $advance->customer_id,
                            'referenceNumber' => $advance->receiptNumber,
                            'Debit' => 0.00,
                            'Credit' => $advance->Amount,
                            'Differentiate' => $last_closing-$advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'CustomerCashAdvance|'.$Id,
                            'TransactionDesc' => $advance->Description,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref=$AccountTransactions->id;
                    // new entry done
                }
                elseif ($advance->paymentType == 'bank')
                {
                    $bankTransaction = BankTransaction::where(['bank_id'=> $advance->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$Id;
                    $bank_transaction->createdDate=$advance->TransferDate;
                    $bank_transaction->Type='customer_advances';
                    $bank_transaction->Details='CustomerBankAdvance|'.$Id;
                    $bank_transaction->Credit=0.00;
                    $bank_transaction->Debit=$advance->Amount;
                    $bank_transaction->Differentiate=$difference+$advance->Amount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $advance->bank_id;
                    $bank_transaction->updateDescription = $advance->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'customer_id' => $advance->customer_id,
                            'referenceNumber' => $advance->receiptNumber,
                            'Debit' => 0.00,
                            'Credit' => $advance->Amount,
                            'Differentiate' => $last_closing-$advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'CustomerBankAdvance|'.$Id,
                            'TransactionDesc' => $advance->Description,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref=$AccountTransactions->id;
                    // new entry done
                }
                elseif ($advance->paymentType == 'cheque')
                {
                    $bankTransaction = BankTransaction::where(['bank_id'=> $advance->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$Id;
                    $bank_transaction->createdDate=$advance->TransferDate;
                    $bank_transaction->Type='customer_advances';
                    $bank_transaction->Details='CustomerChequeAdvance|'.$Id;
                    $bank_transaction->Credit=0.00;
                    $bank_transaction->Debit=$advance->Amount;
                    $bank_transaction->Differentiate=$difference+$advance->Amount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $advance->bank_id;
                    $bank_transaction->updateDescription = $advance->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['customer_id'=> $advance->customer_id,])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'customer_id' => $advance->customer_id,
                            'referenceNumber' => $advance->receiptNumber,
                            'Debit' => 0.00,
                            'Credit' => $advance->Amount,
                            'Differentiate' => $last_closing-$advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>'SupplierChequeAdvance|'.$Id,
                            'TransactionDesc' => $advance->Description,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref=$AccountTransactions->id;
                    // new entry done
                }
                // account section by gautam //
            }
            $advance->update([
                'isPushed' =>true,
                'user_id' =>$user_id,
            ]);
        });
        return redirect()->route('customer_advances.index')->with('pushed','Your Account Debit Successfully');
    }
}
