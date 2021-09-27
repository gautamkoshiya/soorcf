<?php


namespace App\WebRepositories;


use App\MISC\CustomeFooter;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\PaymentReceive;
use App\Models\PaymentReceiveDetail;
use App\Models\PaymentType;
use App\Models\Sale;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IPaymentReceiveRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class PaymentReceiveRepository implements IPaymentReceiveRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(PaymentReceive::with('user','company','customer')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    //$button = '<a href="'.route('payment_receives.show', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-bars"></i></a>';
                    $button = '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
//                    $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
//                    $button .='&nbsp;';
                    return $button;
                })
                ->addColumn('customer', function($data) {
                    return $data->customer->Name ?? "No Quantity";
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('customer_payments_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
//                        $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
//                        $button .='&nbsp;';
                        $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        $button .= '&nbsp;';
                        $button .= '<button type="submit" class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-danger" onclick="cancel_customer_payment(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                        return $button;
                    }else{
                        $button = '&nbsp;&nbsp;';
                        $button.= '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-danger" onclick="cancel_customer_payment(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                        return $button;
                    }
                })
                ->addColumn('paymentReceiveDate', function($data) {
                    return date('d-m-Y', strtotime($data->paymentReceiveDate)) ?? "No date";
                })
                ->rawColumns(
                    [
                        'action',
                        'push',
                        'customer',
                        'referenceNumber',
                        'paymentReceiveDate',
                    ])
                ->make(true);
        }
        return view('admin.customer_payment_receive.index');
    }

    public function all_payment_receives(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = PaymentReceive::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select pr.id,pr.paidAmount,pr.customer_id,pr.company_id,pr.bank_id,pr.accountNumber,pr.transferDate,pr.payment_type,pr.referenceNumber,pr.receiverName,pr.Description,pr.paymentReceiveDate,pr.isPushed,pr.isActive,pr.deleted_at,c.Name from payment_receives as pr left join customers as c on c.id = pr.customer_id  where pr.company_id = '.session('company_id').' and pr.isActive = 1 and pr.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $payment_receive = DB::select( DB::raw($sql));
        }
        else {
            $search = $request->input('search.value');
            $sql = 'select pr.*,c.Name from payment_receives as pr left join customers as c on c.id = pr.customer_id where pr.company_id = '.session('company_id').' and pr.isActive = 1 and pr.deleted_at is null and pr.referenceNumber LIKE "%'.$search.'%" or c.Name LIKE "%'.$search.'%" and pr.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $payment_receive = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,pr.*, c.Name from payment_receives as pr left join customers as c on c.id = pr.customer_id where pr.company_id = '.session('company_id').' and pr.isActive = 1 and pr.deleted_at is null and pr.referenceNumber LIKE "%'.$search.'%" or c.Name LIKE "%'.$search.'%" and pr.deleted_at is null order by id desc limit '.$limit.' offset '.$start ;
            $payment_receive_count = DB::select(DB::raw($sql_count));
            if(!empty($payment_receive_count))
            {
                $totalFiltered = $payment_receive_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($payment_receive))
        {
            foreach ($payment_receive as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['customer'] = $single->Name ?? "No Name";
                $nestedData['paymentReceiveDate'] = date('d-m-Y', strtotime($single->paymentReceiveDate));
                $nestedData['paidAmount'] = $single->paidAmount ?? "No Pad";
                $nestedData['referenceNumber'] = $single->referenceNumber ?? "No Number";
                $nestedData['payment_type'] = $single->payment_type ?? 0.00;
                $nestedData['Description'] = mb_strimwidth($single->Description, 0, 50, '...') ?? "";
                $push='';
                if($single->isPushed == false)
                {
                    $push .= '<a href="'.route('payment_receives.edit', $single->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $push .= '&nbsp;';
                    //'.url('customer_payments_push', $single->id).'
                    $push .= '<button class="btn btn-danger btn-sm" onclick="push_payment(this.id)" type="button" id="pay_'.$single->id.'"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                    $push .= '&nbsp;';
                }
                else
                {
                    $push = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                    $push .= '&nbsp;';
                }
                //$push.='&nbsp;<button class="btn btn-danger" onclick="cancel_customer_payment(this.id)" type="button" id="cancel_'.$single->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                $push.='<a href="#" data-id="'.$single->id.'" data-toggle="modal" class="paymentDelete btn btn-danger btn-sm" data-target="#deletePayment"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                $nestedData['push']=$push;
                $button='<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-eye""></i></button>&nbsp;<button class="btn btn-primary" onclick="print_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-print""></i></button>';
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
        return view('admin.customer_payment_receive.create',compact('customers','banks'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use($request) {
            $AllRequestCount = collect($request->Data)->count();
            if($AllRequestCount > 0) {
                $user_id = session('user_id');
                $company_id = session('company_id');
                $paymentReceive = new PaymentReceive();
                $paymentReceive->customer_id = $request->Data['customer_id'];
                $paymentReceive->totalAmount = $request->Data['totalAmount'];
                $paymentReceive->payment_type = $request->Data['payment_type'];
                $paymentReceive->referenceNumber = preg_replace("/\s+/", "", $request->Data['referenceNumber']);
                $paymentReceive->paymentReceiveDate = $request->Data['paymentReceiveDate'];
                $paymentReceive->paidAmount = $request->Data['paidAmount'];
                $paymentReceive->amountInWords = $request->Data['amountInWords'];
                $paymentReceive->receiptNumber = $request->Data['receiptNumber'];
                $paymentReceive->receiverName = $request->Data['receiverName'];
                $paymentReceive->transferDate = $request->Data['TransferDate'];
                $paymentReceive->accountNumber = $request->Data['accountNumber'];
                $paymentReceive->Description = $request->Data['Description'];
                $paymentReceive->bank_id = $request->Data['bank_id'] ?? 0;
                $paymentReceive->user_id = $user_id;
                $paymentReceive->createdDate = date('Y-m-d');
                $paymentReceive->company_id = $company_id;
                $paymentReceive->save();
                $paymentReceive = $paymentReceive->id;
                $amount = 0;
                $total_i_have=$request->Data['paidAmount'];
                foreach($request->Data['orders'] as $detail)
                {
                    $this_sale=Sale::where('id',$detail['sale_id'])->get()->first();
                    if($this_sale->IsPaid==0 AND $this_sale->remainingBalance!=0)
                    {
                        $total_you_need = $this_sale->remainingBalance;
                        $still_payable_to_you=0;
                        $total_giving_to_you=0;
                        $isPartialPaid = 0;
                        if ($total_i_have >= $total_you_need)
                        {
                            $total_i_have = $total_i_have - $total_you_need;
                            $total_giving_to_you=$total_you_need;
                        }
                        else
                        {
                            $total_giving_to_you=$total_i_have;
                            $total_i_have = $total_i_have - $total_giving_to_you;
                        }
                        PaymentReceiveDetail::create([
                            "amountPaid" => $total_giving_to_you,
                            "sale_id" => $detail['sale_id'],
                            "company_id" => $company_id,
                            "user_id" => $user_id,
                            "payment_receive_id" => $paymentReceive,
                            'createdDate' => $request->Data['paymentReceiveDate'],
                        ]);
                        if($total_i_have<=0)
                        {
                            break;
                        }
                    }
                }
                //return Response()->json($amount);
            }
        });
        return Response()->json(0);
    }

    public function update(Request $request, $Id)
    {
        $payment_receive = PaymentReceive::find($Id);
        $user_id = session('user_id');
        //echo "<pre>";print_r($request->all());die;
        $payment_receive->update([
            'payment_type' => $request->Data['payment_type'],
            'bank_id' => $request->Data['bank_id'],
            'accountNumber' => $request->Data['accountNumber'],
            'TransferDate' => $request->Data['TransferDate'],
            'receiptNumber' => $request->Data['receiptNumber'],
            'paymentReceiveDate' => $request->Data['paymentReceiveDate'],
            'Description' => $request->Data['Description'],
            'referenceNumber' => preg_replace("/\s+/", "", $request->Data['referenceNumber']),
            'receiverName' => $request->Data['receiverName'],
            'user_id' => $user_id,
        ]);
        return redirect()->route('payment_receives.index')->with('update','Record Updated Successfully');
    }

    public function getCustomerPaymentDetail($Id)
    {
        $payment=PaymentReceive::with(['customer','user'])->where('id',$Id)->first();
        //echo "<pre>";print_r($payment->user);die;
        $payment_detail=PaymentReceiveDetail::with(['sale','sale.sale_details'])->where('payment_receive_id',$Id)->get();
        $html='<div class="row"><div class="col-md-12"><label>Customer Name : '.$payment->customer->Name.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Date : '.date('d-M-Y',strtotime($payment->paymentReceiveDate)).'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Type : '.$payment->payment_type.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Reference No. : '.$payment->referenceNumber.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Description : '.$payment->Description.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Amount : '.$payment->paidAmount.'</label></div></div>';
        $html.='<div class="row text-right"><div class="col-md-12"><label>(created by : '.$payment->user->name.'-'.$payment->created_at.')</label></div></div>';
        $html.='<table class="table table-sm table-responsive"><thead><th>SR</th><th>Disbursed</th><th>Sale Date</th><th>PAD</th><th>Total</th><th>Paid</th><th>Balance</th></thead><tbody>';
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

    public function printCustomerPaymentDetail($Id)
    {
        $payment=PaymentReceive::with(['customer','user'])->where('id',$Id)->first();
        $payment_detail=PaymentReceiveDetail::with(['sale','sale.sale_details'])->where('payment_receive_id',$Id)->get();
        $html='<table border="0.5" cellpadding="2"><tr><td width="100">Customer Name : </td><td width="435">'.$payment->customer->Name.'</td></tr>';
        $html.='<tr><td>Payment Date : </td><td>'.date('d-M-Y',strtotime($payment->paymentReceiveDate)).'</td></tr>';
        $html.='<tr><td>Payment Type : </td><td>'.$payment->payment_type.'</td></tr>';
        $html.='<tr><td>Reference No. : </td><td style="font-weight: bold;">'.$payment->referenceNumber.'</td></tr>';
        $html.='<tr><td>Description : </td><td>'.$payment->Description.'</td></tr>';
        $html.='<tr><td>Amount : </td><td style="font-weight: bold;">'.$payment->paidAmount.'</td></tr>';
        $html.='<tr><td>Created By : </td><td>'.$payment->user->name.'-'.$payment->created_at.'</td></tr></table>';
        $html1='<table border="0.5" cellpadding="2"><tr><td align="center">SR</td><td align="center">Disbursed</td><td align="center">Sale Date</td><td align="center">PAD</td><td align="center">Total</td><td align="center">Paid</td><td align="center">Balance</td></tr>';
        $i=0;
        $disbursed_total=0;
        $grand_total_sum=0;
        $paid_total_sum=0;
        $balance_total_sum=0;
        foreach ($payment_detail as $item)
        {
            $disbursed_total+=$item->amountPaid;
            $grand_total_sum+=$item->grandTotal;
            $paid_total_sum+=$item->paidBalance;
            $balance_total_sum+=$item->remainingBalance;
            $html1.='<tr>';
            $html1.='<td align="center">'.(++$i).'</td>';
            $html1.='<td align="right">'.($item->amountPaid).'</td>';
            $html1.='<td align="center">'.(date('d-M-Y',strtotime($item->sale->SaleDate))).'</td>';
            $html1.='<td align="center">'.($item->sale->sale_details[0]->PadNumber).'</td>';
            $html1.='<td align="right">'.($item->sale->grandTotal).'</td>';
            $html1.='<td align="right">'.($item->sale->paidBalance).'</td>';
            $html1.='<td align="right">'.($item->sale->remainingBalance).'</td>';
            $html1.='</tr>';
        }
        $html1.='</table>';
        //echo "<pre>";print_r($html);die;

        $footer=new CustomeFooter;
        $footer->footer();
        $pdf = new PDF();
        $pdf::setPrintHeader(false);

        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, 14);

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);

        $pdf::SetFont('helvetica', '', 15);
        $title='Payment Detail ';
        $pdf::writeHTMLCell(0, 0, '', '', $title,0, 1, 0, true, 'L', true);

        $pdf::SetFont('helvetica', '', 10);
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::SetFont('helvetica', '', 10);
        $pdf::writeHTML($html1, true, false, false, false, '');

        $pdf::lastPage();
        $time=time();
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$time.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }

    public function edit($Id)
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        $banks = Bank::where('company_id',session('company_id'))->get();
        $payment_receive = PaymentReceive::with('user','company','customer','payment_receive_details.sale.sale_details')->find($Id);
        return view('admin.customer_payment_receive.edit',compact('payment_receive','customers','banks'));
    }

    public function cancelCustomerPayment($id)
    {
        $response=false;
        DB::transaction(function () use($id,&$response){
            $company_id = session('company_id');
            $payment=PaymentReceive::with(['payment_receive_details'])->where('id',$id)->first();
            if($payment)
            {
                foreach($payment->payment_receive_details as $single)
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

                if($payment->isPushed==1)
                {
                    if($payment->payment_type == 'cash')
                    {
                        $description_string='CustomerCashPayment|'.$id;
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
                    elseif ($payment->payment_type == 'bank')
                    {
                        $description_string='CustomerBankPayment|'.$id;
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
                    elseif ($payment->payment_type == 'cheque')
                    {
                        $description_string='CustomerChequePayment|'.$id;
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
                }
                $payment->update(['user_id'=>session('user_id')]);
                PaymentReceiveDetail::where('payment_receive_id',$id)->update(['user_id'=>session('user_id')]);

                PaymentReceiveDetail::where('payment_receive_id', array($id))->delete();
                PaymentReceive::where('id', array($id))->delete();
                $response=true;
            }
        });
        return Response()->json($response);
    }

    public function payment_receives_delete_post(Request $request)
    {
        $response=false;
        DB::transaction(function () use($request,&$response){
            $company_id = session('company_id');
            $payment=PaymentReceive::with(['payment_receive_details'])->where('id',$request->payment_id)->first();
            if($payment)
            {
                foreach($payment->payment_receive_details as $single)
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

                if($payment->isPushed==1)
                {
                    if($payment->payment_type == 'cash')
                    {
                        $description_string='CustomerCashPayment|'.$request->payment_id;
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
                    elseif ($payment->payment_type == 'bank')
                    {
                        $description_string='CustomerBankPayment|'.$request->payment_id;
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
                    elseif ($payment->payment_type == 'cheque')
                    {
                        $description_string='CustomerChequePayment|'.$request->payment_id;
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
                }
                $payment->update(['user_id'=>session('user_id')]);
                PaymentReceiveDetail::where('payment_receive_id',$request->payment_id)->update(['user_id'=>session('user_id')]);

                PaymentReceiveDetail::where('payment_receive_id', array($request->payment_id))->delete();
                PaymentReceive::where('id', array($request->payment_id))->delete();
                $response=true;

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'payment_receives';
                $update_note->RelationId = $request->payment_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            }
        });
        return Response()->json($response);
    }

    public function CheckCustomerPaymentReferenceExist($request)
    {
        $data = PaymentReceive::where('company_id',session('company_id'))->where('referenceNumber','like','%'.$request->referenceNumber.'%')->get();
        $data1 = CustomerAdvance::where('company_id',session('company_id'))->where('receiptNumber','like','%'.$request->referenceNumber.'%')->get();
        if($data->first() || $data1->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function customer_payments_push($Id)
    {
        DB::transaction(function () use($Id)
        {
            $payments = PaymentReceive::with('customer','payment_receive_details')->find($Id);

            foreach($payments->payment_receive_details as $single)
            {
                $sales=Sale::where('id',$single->sale_id)->get()->first();
                $is_paid=0;
                if($sales->remainingBalance-$single->amountPaid==0)
                {
                    $is_paid=1;
                    $is_partial_paid=0;
                }
                else
                {
                    $is_partial_paid=1;
                }
                $sales->update([
                    'paidBalance'=>$sales->paidBalance+$single->amountPaid,
                    'remainingBalance'=>$sales->remainingBalance-$single->amountPaid,
                    'Description'=>$payments->referenceNumber,
                    'IsPaid'=>$is_paid,
                    'IsPartialPaid'=>$is_partial_paid,
                    'TermsAndCondition'=>0,
                    'supplierNote'=>0,
                ]);
            }

            $user_id = session('user_id');
            $company_id = session('company_id');
            $payments->update([
                'isPushed' =>true,
                'user_id' =>$user_id,
            ]);

            $accountTransaction_ref=0;

            if($payments->payment_type == 'cash')
            {
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$Id;
                $cash_transaction->createdDate=$payments->transferDate ?? date('Y-m-d h:i:s');
                $cash_transaction->Type='payment_receives';
                $cash_transaction->Details='CustomerCashPayment|'.$Id;
                $cash_transaction->Credit=0.00;
                if($payments->paidAmount==0)
                {
                    $cash_transaction->Debit=0.01;
                }
                else
                {
                    $cash_transaction->Debit=$payments->paidAmount;
                }
                $cash_transaction->Differentiate=$difference+$payments->paidAmount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $payments->referenceNumber;
                $cash_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['customer_id'=> $payments->customer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'customer_id' => $payments->customer_id,
                        'Debit' => 0.00,
                        'Credit' => $payments->paidAmount,
                        'Differentiate' => $last_closing-$payments->paidAmount,
                        'createdDate' => $payments->transferDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'CustomerCashPayment|'.$Id,
                        'referenceNumber'=>$payments->referenceNumber,
                        'TransactionDesc' => $payments->Description,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref=$AccountTransactions->id;
                // new entry done
            }
            elseif ($payments->payment_type == 'bank')
            {
                $bankTransaction = BankTransaction::where(['bank_id'=> $payments->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$Id;
                $bank_transaction->createdDate=$payments->transferDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='payment_receives';
                $bank_transaction->Details='CustomerBankPayment|'.$Id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$payments->paidAmount;
                $bank_transaction->Differentiate=$difference+$payments->paidAmount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $payments->bank_id;
                $bank_transaction->updateDescription = $payments->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['customer_id'=> $payments->customer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'customer_id' => $payments->customer_id,
                        'Debit' => 0.00,
                        'Credit' => $payments->paidAmount,
                        'Differentiate' => $last_closing-$payments->paidAmount,
                        'createdDate' => $payments->transferDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'CustomerBankPayment|'.$Id,
                        'referenceNumber'=>$payments->referenceNumber,
                        'TransactionDesc' => $payments->Description,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref=$AccountTransactions->id;
                // new entry done
            }
            elseif ($payments->payment_type == 'cheque')
            {
                $bankTransaction = BankTransaction::where(['bank_id'=> $payments->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$Id;
                $bank_transaction->createdDate=$payments->transferDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='payment_receives';
                $bank_transaction->Details='CustomerChequePayment|'.$Id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$payments->paidAmount;
                $bank_transaction->Differentiate=$difference+$payments->paidAmount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $payments->bank_id;
                $bank_transaction->updateDescription = $payments->referenceNumber;
                $bank_transaction->save();

                // start new entry
                $accountTransaction = AccountTransaction::where(['customer_id'=> $payments->customer_id,])->get();
                $last_closing=$accountTransaction->last()->Differentiate;
                $AccData =
                    [
                        'customer_id' => $payments->customer_id,
                        'Debit' => 0.00,
                        'Credit' => $payments->paidAmount,
                        'Differentiate' => $last_closing-$payments->paidAmount,
                        'createdDate' => $payments->transferDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'CustomerChequePayment|'.$Id,
                        'referenceNumber'=>$payments->referenceNumber,
                        'TransactionDesc' => $payments->Description,
                    ];
                $AccountTransactions = AccountTransaction::Create($AccData);
                $accountTransaction_ref=$AccountTransactions->id;
                // new entry done
            }
        });
        return redirect()->route('payment_receives.index')->with('pushed','Your Account Debit Successfully');
    }
}
