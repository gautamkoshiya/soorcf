<?php


namespace App\WebRepositories;


use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Company;
use App\Models\Investor;
use App\Models\InvestorTransaction;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IInvestorRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class InvestorTransactionRepository implements IInvestorRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(InvestorTransaction::with('user','company','investor')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    //$button = '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';//
                    $button='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    return $button;
                })
                ->addColumn('investor', function($data) {
                    return $data->investor->Name ?? "NA";
                })
                ->addColumn('transaction_type', function($data) {
                    if($data->transaction_type==1)
                    {
                        return 'Debit';
                    }
                    return 'Credit';
                })
                ->addColumn('transferDate', function($data) {
                    return date('d-m-Y', strtotime($data->transferDate)) ?? "No date";
                })
                ->addColumn('User', function($data) {
                    return $data->user->name.'-'.$data->created_at;
                })
                /*->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('customer_payments_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
                        $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        $button .= '&nbsp;';
                        $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm()"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-danger" onclick="cancel_customer_payment(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-danger" onclick="cancel_customer_payment(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                        return $button;
                    }
                })*/
                ->rawColumns(
                    [
                        'action',
                        'investor',
                        'transaction_type',
                        'transferDate',
                        'User',
                    ])
                ->make(true);
        }
        return view('admin.investor_transaction.index');
    }

    public function create()
    {
        //$companies = Company::all();
        $investor = Investor::where('company_id',session('company_id'))->get();
        $banks = Bank::all();
        return view('admin.investor_transaction.create',compact('investor','banks',));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use($request)
        {
            $AllRequestCount = collect($request->Data)->count();
            if($AllRequestCount > 0)
            {
                $transaction_type=0;//credit
                if($request->Data['transaction_type']=='debit')
                {
                    $transaction_type=1;//debit
                }
                $user_id = session('user_id');
                $investor_transaction = new InvestorTransaction();
                $investor_transaction->transaction_type = $transaction_type;
                $investor_transaction->totalAmount = $request->Data['totalAmount'];
                $investor_transaction->investor_id = $request->Data['investor_id'];
                $investor_transaction->user_id = $user_id;
                $investor_transaction->company_id = session('company_id') ?? 0;
                $investor_transaction->bank_id = $request->Data['bank_id'] ?? 0;
                $investor_transaction->accountNumber = $request->Data['accountNumber'];
                $investor_transaction->transferDate = $request->Data['TransferDate'];
                $investor_transaction->payment_type = $request->Data['payment_type'];
                $investor_transaction->referenceNumber = $request->Data['referenceNumber'];
                $investor_transaction->PersonName = $request->Data['PersonName'];
                $investor_transaction->receiptNumber = $request->Data['receiptNumber'];
                $investor_transaction->Description = $request->Data['Description'];
                $investor_transaction->createdDate = date('Y-m-d');
                $investor_transaction->isActive = 1;
                $investor_transaction->save();

                if($request->Data['payment_type'] == 'cash')
                {
                    $cashTransaction = CashTransaction::where(['company_id'=> session('company_id')])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference=$investor_transaction->id;
                    $cash_transaction->createdDate=$request->Data['TransferDate'] ?? date('Y-m-d h:i:s');
                    $cash_transaction->Type='investor_transactions';
                    $cash_transaction->Details='InvestorCashPayment|'.$investor_transaction->id;
                    if($request->Data['transaction_type']=='credit')
                    {
                        $cash_transaction->Credit=0.00;
                        $cash_transaction->Debit=$request->Data['totalAmount'];
                        $cash_transaction->Differentiate=$difference+$request->Data['totalAmount'];
                    }
                    else if($request->Data['transaction_type']=='debit')
                    {
                        $cash_transaction->Credit=$request->Data['totalAmount'];
                        $cash_transaction->Debit=0.00;
                        $cash_transaction->Differentiate=$difference-$request->Data['totalAmount'];
                    }
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = session('company_id');
                    $cash_transaction->PadNumber = $request->Data['referenceNumber'];
                    $cash_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['investor_id'=> $request->Data['investor_id'],])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    if($request->Data['transaction_type']=='credit')
                    {
                        $AccData =
                            [
                                'investor_id' => $request->Data['investor_id'],
                                'Debit' => 0.00,
                                'Credit' => $request->Data['totalAmount'],
                                'Differentiate' => $last_closing+$request->Data['totalAmount'],
                                'createdDate' => $request->Data['TransferDate'],
                                'user_id' => $user_id,
                                'company_id' => session('company_id'),
                                'Description'=>'InvestorCashPayment|'.$investor_transaction->id,
                                'referenceNumber'=>$request->Data['referenceNumber'],
                            ];
                    }
                    else if($request->Data['transaction_type']=='debit')
                    {
                        $AccData =
                            [
                                'investor_id' => $request->Data['investor_id'],
                                'Debit' => $request->Data['totalAmount'],
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing-$request->Data['totalAmount'],
                                'createdDate' => $request->Data['TransferDate'],
                                'user_id' => $user_id,
                                'company_id' => session('company_id'),
                                'Description'=>'InvestorCashPayment|'.$investor_transaction->id,
                                'referenceNumber'=>$request->Data['referenceNumber'],
                            ];
                    }
                    AccountTransaction::Create($AccData);
                }
                elseif ($request->Data['payment_type'] == 'bank')
                {
                    $bankTransaction = BankTransaction::where(['company_id'=> session('company_id')])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$investor_transaction->id;
                    $bank_transaction->createdDate=$request->Data['TransferDate'] ?? date('Y-m-d h:i:s');
                    $bank_transaction->Type='investor_transactions';
                    $bank_transaction->Details='InvestorBankPayment|'.$investor_transaction->id;
                    if($request->Data['transaction_type']=='credit')
                    {
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$request->Data['totalAmount'];
                        $bank_transaction->Differentiate=$difference+$request->Data['totalAmount'];
                    }
                    else if($request->Data['transaction_type']=='debit')
                    {
                        $bank_transaction->Credit=$request->Data['totalAmount'];
                        $bank_transaction->Debit=0.00;
                        $bank_transaction->Differentiate=$difference-$request->Data['totalAmount'];
                    }
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = session('company_id');
                    $bank_transaction->updateDescription = $request->Data['referenceNumber'];
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['investor_id'=> $request->Data['investor_id'],])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    if($request->Data['transaction_type']=='credit')
                    {
                        $AccData =
                            [
                                'investor_id' => $request->Data['investor_id'],
                                'Debit' => 0.00,
                                'Credit' => $request->Data['totalAmount'],
                                'Differentiate' => $last_closing+$request->Data['totalAmount'],
                                'createdDate' => $request->Data['TransferDate'],
                                'user_id' => $user_id,
                                'company_id' => session('company_id'),
                                'Description'=>'InvestorBankPayment|'.$investor_transaction->id,
                                'referenceNumber'=>$request->Data['referenceNumber'],
                            ];
                    }
                    else if($request->Data['transaction_type']=='debit')
                    {
                        $AccData =
                            [
                                'investor_id' => $request->Data['investor_id'],
                                'Debit' => $request->Data['totalAmount'],
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing-$request->Data['totalAmount'],
                                'createdDate' => $request->Data['TransferDate'],
                                'user_id' => $user_id,
                                'company_id' => session('company_id'),
                                'Description'=>'InvestorCashPayment|'.$investor_transaction->id,
                                'referenceNumber'=>$request->Data['referenceNumber'],
                            ];
                    }
                    AccountTransaction::Create($AccData);
                }
                elseif ($request->Data['payment_type'] == 'cheque')
                {
                    $bankTransaction = BankTransaction::where(['company_id'=> session('company_id')])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference=$investor_transaction->id;
                    $bank_transaction->createdDate=$request->Data['TransferDate'] ?? date('Y-m-d h:i:s');
                    $bank_transaction->Type='investor_transactions';
                    $bank_transaction->Details='InvestorChequePayment|'.$investor_transaction->id;
                    if($request->Data['transaction_type']=='credit')
                    {
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$request->Data['totalAmount'];
                        $bank_transaction->Differentiate=$difference+$request->Data['totalAmount'];
                    }
                    else if($request->Data['transaction_type']=='debit')
                    {
                        $bank_transaction->Credit=$request->Data['totalAmount'];
                        $bank_transaction->Debit=0.00;
                        $bank_transaction->Differentiate=$difference-$request->Data['totalAmount'];
                    }
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = session('company_id');
                    $bank_transaction->updateDescription = $request->Data['referenceNumber'];
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['investor_id'=> $request->Data['investor_id'],])->get();
                    $last_closing=$accountTransaction->last()->Differentiate;
                    if($request->Data['transaction_type']=='credit')
                    {
                        $AccData =
                            [
                                'investor_id' => $request->Data['investor_id'],
                                'Debit' => 0.00,
                                'Credit' => $request->Data['totalAmount'],
                                'Differentiate' => $last_closing+$request->Data['totalAmount'],
                                'createdDate' => $request->Data['TransferDate'],
                                'user_id' => $user_id,
                                'company_id' => session('company_id'),
                                'Description'=>'InvestorChequePayment|'.$investor_transaction->id,
                                'referenceNumber'=>$request->Data['referenceNumber'],
                            ];
                    }
                    else if($request->Data['transaction_type']=='debit')
                    {
                        $AccData =
                            [
                                'investor_id' => $request->Data['investor_id'],
                                'Debit' => $request->Data['totalAmount'],
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing-$request->Data['totalAmount'],
                                'createdDate' => $request->Data['TransferDate'],
                                'user_id' => $user_id,
                                'company_id' => session('company_id'),
                                'Description'=>'InvestorChequePayment|'.$investor_transaction->id,
                                'referenceNumber'=>$request->Data['referenceNumber'],
                            ];
                    }
                    AccountTransaction::Create($AccData);
                }
            }
        });
        return Response()->json(true);
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function investor_transaction_delete_post(Request $request)
    {
        $response=false;
        DB::transaction(function () use($request,&$response) {
            $transaction = InvestorTransaction::find($request->row_id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            if($transaction)
            {
                if($transaction->payment_type == 'cash')
                {
                    $description_string='InvestorCashPayment|'.$request->row_id;
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
                elseif ($transaction->payment_type == 'bank')
                {
                    $description_string='InvestorBankPayment|'.$request->row_id;
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
                elseif ($transaction->payment_type == 'cheque')
                {
                    $description_string='InvestorChequePayment|'.$request->row_id;
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
            $transaction->update(['user_id'=>session('user_id')]);
            InvestorTransaction::where('id', array($request->row_id))->delete();
            $response=true;

            $update_note = new UpdateNote();
            $update_note->RelationTable = 'investor_transactions';
            $update_note->RelationId = $request->row_id;
            $update_note->UpdateDescription = $request->deleteDescription;
            $update_note->user_id = session('user_id');
            $update_note->company_id = $company_id;
            $update_note->save();

        });
        //return redirect()->route('deposits.index');
        return Response()->json($response);
    }

    public function InvestorReportByCompany()
    {
        //$companies = Company::get();
        $investors = Investor::where('company_id',session('company_id'))->get();
        return view('admin.investor_transaction.investor_report_by_company',compact('investors'));
    }

    public function PrintInvestorReportByCompany(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='' && $request->investor_id=='all')
        {
            //echo "<pre>";print_r(session('company_id'));die;

            $account_transactions=AccountTransaction::with(['company'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('createdDate', [$request->fromDate, $request->toDate])->where('Description','like','%Investor%')->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
            $sum_of_debit_before_from_date=AccountTransaction::where('company_id',session('comapny_id'))->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=AccountTransaction::where('company_id',session('comapny_id'))->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
        }
        else if($request->fromDate!='' && $request->toDate!='' && $request->investor_id!='all')
        {
            $account_transactions=AccountTransaction::with(['company'=>function($q){$q->select('id','Name');}])->where('company_id','=',session('company_id'))->where('investor_id','=',$request->investor_id)->whereBetween('createdDate', [$request->fromDate, $request->toDate])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
            $sum_of_debit_before_from_date=AccountTransaction::where('investor_id',$request->investor_id)->where('company_id','=',session('company_id'))->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=AccountTransaction::where('investor_id',$request->investor_id)->where('company_id','=',session('company_id'))->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
        }
        else
        {
            return FALSE;
        }
        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);
        //echo "<pre>";print_r($row);die;

        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $company_name=Company::where('id',session('company_id'))->first();
            $company='Company Name : '.$company_name->Name;
            //$company='Company Name : '.$request->company_name;
            $investor='Investor Name : '.$request->investor_name;

            $pdf::Cell(95,5,$company,'',0,'L');
            $pdf::Cell(95,5,$investor,'',0,'R');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $opb=' Opening Balance  '.$closing_amount;
            $pdf::Cell(95,5,$html,'',0,'L');
            $pdf::Cell(95,5,$opb,'',0,'R');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="57">Date</th>
                <th align="center" width="60">Ref#</th>
                <th align="center" width="120">Description</th>
                <th align="center" width="80">Company</th>
                <th align="center" width="75">Debit</th>
                <th align="center" width="75">Credit</th>
                <th align="right" width="80">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $credit_total=0.0;
            $debit_total=0.0;
            $balance=$closing_amount;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Debit']!=0)
                {
                    $debit_total += $row[$i]['Debit'];
                    $balance = $balance - $row[$i]['Debit'];
                }
                elseif($row[$i]['Credit']!=0)
                {
                    $credit_total += $row[$i]['Credit'];
                    $balance = $balance + $row[$i]['Credit'];
                }
                else
                {
                    $balance += $row[$i]['Differentiate'];
                }

                $html .='<tr>
                    <td align="left" width="57">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left" width="60">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left" width="120">'.$row[$i]['Description'].'</td>
                    <td align="left" width="80">'.$row[$i]['company']['Name'].'</td>
                    <td align="right" width="75">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="75">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="80">'.(number_format($balance,2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($balance<0)
            {
                $html='<table border="0.5" cellpadding="1">';
                $html.= '
                 <tr>
                     <td width="317" align="right" colspan="3">Total : </td>
                     <td width="75" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="75" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="80" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="1">';
                $html.= '
                 <tr>
                     <td width="310" align="right" colspan="3">Total : </td>
                     <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    /*public function PrintInvestorReportByCompany(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='' && $request->company_id=='all')
        {
            $account_transactions=AccountTransaction::with(['company'=>function($q){$q->select('id','Name');}])->where('investor_id','=',$request->investor_id)->whereBetween('createdDate', [$request->fromDate, $request->toDate])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
            $sum_of_debit_before_from_date=AccountTransaction::where('investor_id',$request->investor_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=AccountTransaction::where('investor_id',$request->investor_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
        }
        else if($request->fromDate!='' && $request->toDate!='' && $request->company_id!='all')
        {
            $account_transactions=AccountTransaction::with(['company'=>function($q){$q->select('id','Name');}])->where('company_id','=',$request->company_id)->where('investor_id','=',$request->investor_id)->whereBetween('createdDate', [$request->fromDate, $request->toDate])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
            $sum_of_debit_before_from_date=AccountTransaction::where('investor_id',$request->investor_id)->where('company_id','=',$request->company_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=AccountTransaction::where('investor_id',$request->investor_id)->where('company_id','=',$request->company_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
        }
        else
        {
            return FALSE;
        }
        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);
        //echo "<pre>";print_r($row);die;

        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $company='Company Name : '.$request->company_name;
            $investor='Investor Name : '.$request->investor_name;

            $pdf::Cell(95,5,$company,'',0,'L');
            $pdf::Cell(95,5,$investor,'',0,'R');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $opb=' Opening Balance  '.$closing_amount;
            $pdf::Cell(95,5,$html,'',0,'L');
            $pdf::Cell(95,5,$opb,'',0,'R');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="57">Date</th>
                <th align="center" width="60">Ref#</th>
                <th align="center" width="120">Description</th>
                <th align="center" width="80">Company</th>
                <th align="center" width="75">Debit</th>
                <th align="center" width="75">Credit</th>
                <th align="right" width="80">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $credit_total=0.0;
            $debit_total=0.0;
            $balance=$closing_amount;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Debit']!=0)
                {
                    $debit_total += $row[$i]['Debit'];
                    $balance = $balance - $row[$i]['Debit'];
                }
                elseif($row[$i]['Credit']!=0)
                {
                    $credit_total += $row[$i]['Credit'];
                    $balance = $balance + $row[$i]['Credit'];
                }
                else
                {
                    $balance += $row[$i]['Differentiate'];
                }

                $html .='<tr>
                    <td align="left" width="57">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left" width="60">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left" width="120">'.$row[$i]['Description'].'</td>
                    <td align="left" width="80">'.$row[$i]['company']['Name'].'</td>
                    <td align="right" width="75">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="75">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="80">'.(number_format($balance,2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($balance<0)
            {
                $html='<table border="0.5" cellpadding="1">';
                $html.= '
                 <tr>
                     <td width="317" align="right" colspan="3">Total : </td>
                     <td width="75" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="75" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="80" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="1">';
                $html.= '
                 <tr>
                     <td width="310" align="right" colspan="3">Total : </td>
                     <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }*/
}
