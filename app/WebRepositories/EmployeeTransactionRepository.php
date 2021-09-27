<?php

namespace App\WebRepositories;

use App\MISC\CustomeFooter;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Employee;
use App\Models\Project;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IEmployeeTransactionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class EmployeeTransactionRepository implements IEmployeeTransactionRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(AccountTransaction::with('user','company','employee')->where('company_id',session('company_id'))->where('employee_id','!=',0)->latest()->get())
                ->addColumn('action', function ($data) {
                $button='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                return $button;
                })
                ->addColumn('employee', function($data) {
                    return $data->employee->Name ?? "NA";
                })
                ->addColumn('User', function($data) {
                    return $data->user->name.'-'.$data->created_at;
                })
                ->rawColumns(
                    [
                        'action',
                        'User',
                    ])
                ->make(true);
        }
        return view('admin.employee_transaction.index');
    }

    public function create()
    {
        $banks = Bank::all();
        $employees = Employee::where('company_id',session('company_id'))->where('isActive',1)->get();
        return view('admin.employee_transaction.create',compact('employees','banks'));
    }

    public function CheckAccountTransactionReferenceExist($request)
    {
        $data = AccountTransaction::where('referenceNumber','=',$request->referenceNumber)->where('company_id',session('company_id'))->get();
        if($data->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function store(Request $request)
    {
        DB::transaction(function () use($request)
        {
            $user_id = session('user_id');
            $company_id = session('company_id');
            $desc_string='';
            switch ($request['payment_type'])
            {
                case 'cash':
                    $desc_string='EmployeeCashPayment|';
                case 'bank':
                    $desc_string='EmployeeBankPayment|';
                case 'cheque':
                    $desc_string='EmployeeChequePayment|';
            }
            if($request['transaction_type']=='debit')
            {
                $employee_transaction = new AccountTransaction();
                $employee_transaction->Credit = 0.00;
                $employee_transaction->Debit = $request['totalAmount'];
                $employee_transaction->Differentiate = 0.00;
                $employee_transaction->referenceNumber = $request['referenceNumber'];
                $employee_transaction->user_id = $user_id;
                $employee_transaction->employee_id = $request['employee_id'];
                $employee_transaction->company_id = $company_id;
                $employee_transaction->createdDate = $request['createdDate'];
                $employee_transaction->TransactionDesc = $request['Description'];
                $employee_transaction->save();
            }
            else if($request['transaction_type']=='credit')
            {
                $employee_transaction = new AccountTransaction();
                $employee_transaction->Credit = $request['totalAmount'];
                $employee_transaction->Debit = 0.00;
                $employee_transaction->Differentiate = 0.00;
                $employee_transaction->referenceNumber = $request['referenceNumber'];
                $employee_transaction->user_id = $user_id;
                $employee_transaction->employee_id = $request['employee_id'];
                $employee_transaction->company_id = $company_id;
                $employee_transaction->createdDate = $request['createdDate'];
                $employee_transaction->TransactionDesc = $request['Description'];
                $employee_transaction->save();
            }

            // cash or bank entries
            if($request['payment_type'] == 'cash')
            {
                $cashTransaction = CashTransaction::where(['company_id'=> session('company_id')])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$employee_transaction->id;
                $cash_transaction->createdDate=$request['createdDate'];
                $cash_transaction->Type='employee_transactions';
                $cash_transaction->Details=$desc_string.$employee_transaction->id;
                if($request['transaction_type']=='debit')
                {
                    $cash_transaction->Credit=$request['totalAmount'];
                    $cash_transaction->Debit=0.00;
                    $cash_transaction->Differentiate=$difference-$request['totalAmount'];
                }
                else if($request['transaction_type']=='credit')
                {
                    $cash_transaction->Credit=0.00;
                    $cash_transaction->Debit=$request['totalAmount'];
                    $cash_transaction->Differentiate=$difference+$request['totalAmount'];
                }
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = session('company_id');
                $cash_transaction->PadNumber = $request['referenceNumber'];
                $cash_transaction->save();

            }
            elseif ($request['payment_type'] == 'bank')
            {
                $bankTransaction = BankTransaction::where(['company_id'=> session('company_id')])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$employee_transaction->id;
                $bank_transaction->createdDate=$request['createdDate'];
                $bank_transaction->Type='employee_transactions';
                $bank_transaction->Details=$desc_string.$employee_transaction->id;
                if($request['transaction_type']=='debit')
                {
                    $bank_transaction->Credit=$request['totalAmount'];
                    $bank_transaction->Debit=0.00;
                    $bank_transaction->Differentiate=$difference-$request['totalAmount'];
                }
                else if($request['transaction_type']=='credit')
                {
                    $bank_transaction->Credit=0.00;
                    $bank_transaction->Debit=$request['totalAmount'];
                    $bank_transaction->Differentiate=$difference+$request['totalAmount'];
                }
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = session('company_id');
                $bank_transaction->updateDescription = $request['referenceNumber'];
                $bank_transaction->save();
            }
            elseif ($request['payment_type'] == 'cheque')
            {
                $bankTransaction = BankTransaction::where(['company_id'=> session('company_id')])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$employee_transaction->id;
                $bank_transaction->createdDate=$request['createdDate'];
                $bank_transaction->Type='employee_transactions';
                $bank_transaction->Details=$desc_string.$employee_transaction->id;
                if($request['transaction_type']=='debit')
                {
                    $bank_transaction->Credit=$request['totalAmount'];
                    $bank_transaction->Debit=0.00;
                    $bank_transaction->Differentiate=$difference-$request['totalAmount'];
                }
                else if($request['transaction_type']=='credit')
                {
                    $bank_transaction->Credit=0.00;
                    $bank_transaction->Debit=$request['totalAmount'];
                    $bank_transaction->Differentiate=$difference+$request['totalAmount'];
                }
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = session('company_id');
                $bank_transaction->updateDescription = $request['referenceNumber'];
                $bank_transaction->save();
            }
            $employee_transaction->update(['Description'=>$desc_string.$employee_transaction->id]);
        });
        return Response()->json(true);
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function employee_transaction_delete_post(Request $request)
    {
        $response=false;
        DB::transaction(function () use($request,&$response) {
            $transaction = AccountTransaction::find($request->row_id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            if($transaction)
            {
                if(strpos($transaction->Description, 'EmployeeCashPayment') !== false)
                {
                    $description_string='EmployeeCashPayment|'.$request->row_id;
                    $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                    if($previous_probable_cash_entry)
                    {
                        $previous_probable_cash_entry->update(['user_id'=>$user_id]);
                        $previous_probable_cash_entry->delete();
                    }

                    $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                    if($previous_probable_account_entry)
                    {
                        $previous_probable_account_entry->update(['user_id'=>$user_id]);
                        $previous_probable_account_entry->delete();
                    }
                }
                elseif (strpos($transaction->Description, 'EmployeeBankPayment') !== false)
                {
                    $description_string='EmployeeBankPayment|'.$request->row_id;
                    $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                    if($previous_probable_bank_entry)
                    {
                        $previous_probable_bank_entry->update(['user_id'=>$user_id]);
                        $previous_probable_bank_entry->delete();
                    }

                    $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                    if($previous_probable_account_entry)
                    {
                        $previous_probable_account_entry->update(['user_id'=>$user_id]);
                        $previous_probable_account_entry->delete();
                    }
                }
                elseif (strpos($transaction->Description, 'EmployeeChequePayment') !== false)
                {
                    $description_string='EmployeeChequePayment|'.$request->row_id;
                    $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                    if($previous_probable_bank_entry)
                    {
                        $previous_probable_bank_entry->update(['user_id'=>$user_id]);
                        $previous_probable_bank_entry->delete();
                    }

                    $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                    if($previous_probable_account_entry)
                    {
                        $previous_probable_account_entry->update(['user_id'=>$user_id]);
                        $previous_probable_account_entry->delete();
                    }
                }
            }
            $transaction->update(['user_id'=>$user_id,'updateDescription'=>'hide']);
            AccountTransaction::where('id', array($request->row_id))->delete();
            $response=true;

            $update_note = new UpdateNote();
            $update_note->RelationTable = 'employee_transactions';
            $update_note->RelationId = $request->row_id;
            $update_note->UpdateDescription = $request->deleteDescription;
            $update_note->user_id = $user_id;
            $update_note->company_id = $company_id;
            $update_note->save();

        });
        //return redirect()->route('deposits.index');
        return Response()->json($response);
    }

    public function EmployeeAccountStatement()
    {
        $employees = Employee::where('isActive',1)->where('company_id',session('company_id'))->get();
        return view('admin.employee_transaction.employee_account_statement',compact('employees'));
    }

    public function PrintEmployeeAccountStatement(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='' && $request->employee_id!='')
        {
            $account_transactions=AccountTransaction::where('employee_id',$request->employee_id)->whereBetween('createdDate', [$request->fromDate, $request->toDate])->orderBy('id')->get();
            //get opening balance
            $sum_of_credit_before_from_date=AccountTransaction::where('employee_id',$request->employee_id)->where('createdDate','<',$request->fromDate)->sum('Credit');
            $sum_of_debit_before_from_date=AccountTransaction::where('employee_id',$request->employee_id)->where('createdDate','<',$request->fromDate)->sum('Debit');
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
            $employee='Name : '.$request->employee_name;

            $pdf::Cell(95,5,$employee,'',0,'L');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $opb=' Opening Balance  '.$closing_amount;
            $pdf::Cell(95,5,$html,'',0,'L');
            $pdf::Cell(95,5,$opb,'',0,'R');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="1">
            <tr style="color: rgb(0,0,0);">
                <th align="center" width="57">Date</th>
                <th align="center" width="100">Ref#</th>
                <th align="center" width="208">Description</th>
                <th align="center" width="55">Debit</th>
                <th align="center" width="55">Credit</th>
                <th align="right" width="60">Closing</th>
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
                    $balance = $balance + $row[$i]['Debit'];
                }
                elseif($row[$i]['Credit']!=0)
                {
                    $credit_total += $row[$i]['Credit'];
                    $balance = $balance - $row[$i]['Credit'];
                }
                else
                {
                    $balance += $row[$i]['Differentiate'];
                }

                $html .='<tr>
                    <td align="left" width="57">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left" width="100">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left" width="208">'.$row[$i]['TransactionDesc'].'</td>
                    <td align="right" width="55">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="55">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="60">'.(number_format($balance,2,'.',',')).'</td>
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
                     <td width="365" align="right" colspan="3">Total : </td>
                     <td width="55" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="55" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="60" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
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
                     <td width="365" align="right" colspan="3">Total : </td>
                     <td width="55" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="55" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="60" align="right">'.number_format($balance,2,'.',',').'</td>
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

    public function GetEmployeeReceivable()
    {
        return view('admin.employee_transaction.employee_receivable');
    }

    public function PrintEmployeeReceivable()
    {
        $result_array=array();
        $employees=Employee::select('id','Name','Mobile')->where('company_id',session('company_id'))->get();
        foreach ($employees as $employee)
        {
            //get diff of total debit and credit column
            $credit_sum=AccountTransaction::where('employee_id',$employee->id)->sum('Credit');
            $debit_sum=AccountTransaction::where('employee_id',$employee->id)->sum('Debit');
            $diff=$debit_sum-$credit_sum;
            if($diff<0)
            {
                $temp=array('Name'=>$employee->Name,'Mobile'=>$employee->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
        }
        $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
        $row=array_values($row);
        //echo "<pre>";print_r($row);die;

        //$data=SalesResource::collection(Sale::get()->where('remainingBalance','!=',0));
        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Employee RECEIVABLE SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s A');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $html='Balance';
            $pdf::Cell(95,5,$html,'',0,'L');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="50">SN</th>
                <th align="center" width="230">Employee Name</th>
                <th align="center" width="185">Cell</th>
                <th align="right" width="70">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            $total_advances=0.0;

            for($i=0;$i<count($row);$i++)
            {
                $color='red';
                $total_balance+=$row[$i]['Differentiate'];
                $html .='<tr>
                <td align="center" width="50">'.($i+1).'</td>
                <td align="left" width="230">'.($row[$i]['Name']).'</td>
                <td align="left" width="185">'.($row[$i]['Mobile']).'</td>
                <td align="right" width="70" style="color:'.$color.';">'.(number_format($row[$i]['Differentiate'],2,'.',',')).'</td>
                </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            $html = '<table border="0" cellpadding="1"><tr color="red" style="font-weight: bold;font-size: 12px;">
                         <td width="450" align="right" colspan="3">Balance Total : </td>
                         <td width="70" align="right">'. number_format($total_balance, 2, '.', ',') .'</td>
                     </tr>';
            $html .= '</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

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

    public function GetEmployeeLabourList()
    {
        $projects=Project::get();
        return view('admin.employee_transaction.employee_labour_list',compact('projects'));
    }

    public function PrintEmployeeLabourList(Request $request)
    {
        if($request->project_id=='all')
        {
            $employees=Employee::with('project')->get();
        }
        else
        {
            $employees=Employee::with('project')->where('UpdateDescription',$request->project_id)->get();
        }
        $row=$this->array_sort($employees, 'Differentiate', SORT_DESC);
        $row=array_values($row);
        //echo "<pre>";print_r($row);die;

        //$data=SalesResource::collection(Sale::get()->where('remainingBalance','!=',0));
        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Labour List';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s A');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="50">SN</th>
                <th align="center" width="230">Employee Name</th>
                <th align="center" width="185">Labour Code</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);

            for($i=0;$i<count($row);$i++)
            {
                $html .='<tr>
                <td align="center" width="50">'.($i+1).'</td>
                <td align="center" width="230">'.($row[$i]['Name']).'</td>
                <td align="center" width="185">'.($row[$i]['labour_code']).'</td>
                </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

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
