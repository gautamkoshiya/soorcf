<?php


namespace App\WebRepositories;


use App\MISC\CustomeFooter;
use App\Models\AccountTransaction;
use App\Models\Company;
use App\Models\UpdateNote;
use App\Models\Vault;
use App\WebRepositories\Interfaces\IVaultRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class VaultRepository implements IVaultRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Vault::with(['user'=>function($q){$q->select('id','name');},'company'=>function($q){$q->select('id','Name');}])->latest()->get())
                ->addColumn('action', function ($data) {
                    $button='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    return $button;
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
                ->rawColumns(
                    [
                        'action',
                        'transaction_type',
                        'transferDate',
                        'User',
                    ])
                ->make(true);
        }
        return view('admin.vault.index');
    }

    public function create()
    {
        $companies = Company::all();
        return view('admin.vault.create',compact('companies',));
    }

    public function store(Request $request)
    {
        $transaction_type=0;//credit
        if($request->Data['transaction_type']=='debit')
        {
            $transaction_type=1;//debit
        }
        $user_id = session('user_id');
        $vault = new Vault();
        $vault->transaction_type = $transaction_type;
        $vault->totalAmount = $request->Data['totalAmount'];
        $vault->user_id = $user_id;
        $vault->company_id = $request->Data['company_id'] ?? 0;
        $vault->transferDate = $request->Data['transferDate'];
        $vault->Description = $request->Data['Description'];
        $vault->createdDate = date('Y-m-d');
        $vault->save();
        return Response()->json(true);
    }

    public function vault_delete_post(Request $request)
    {
        $response=false;
        DB::transaction(function () use($request,&$response) {
            $vault = Vault::find($request->row_id);
            $company_id = session('company_id');

            if($vault)
            {
                $vault->update(['user_id'=>session('user_id')]);
                Vault::where('id', array($request->row_id))->delete();
                $response=true;

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'vaults';
                $update_note->RelationId = $request->row_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            }
        });
        return Response()->json($response);
    }

    public function getClosingVault($Id)
    {
        if ($Id==0)
        {
            $sum_of_debit=Vault::whereNull('deleted_at')->where('transaction_type',1)->sum('totalAmount');
            $sum_of_credit=Vault::whereNull('deleted_at')->where('transaction_type',0)->sum('totalAmount');
            $closing_amount=$sum_of_debit-$sum_of_credit;
        }
        else
        {
            $sum_of_debit=Vault::where('company_id','=',$Id)->whereNull('deleted_at')->where('transaction_type',1)->sum('totalAmount');
            $sum_of_credit=Vault::where('company_id','=',$Id)->whereNull('deleted_at')->where('transaction_type',0)->sum('totalAmount');
            $closing_amount=$sum_of_debit-$sum_of_credit;
        }
        return Response()->json($closing_amount);
    }

    public function VaultReportByCompany()
    {
        $companies = Company::get();
        return view('admin.vault.vault_report_by_company',compact('companies'));
    }

    public function PrintVaultReportByCompany(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='' && $request->company_id==0)
        {
            $account_transactions=Vault::with(['company'=>function($q){$q->select('id','Name');}])->whereBetween('transferDate', [$request->fromDate, $request->toDate])->orderBy('transferDate')->orderBy('id')->get();
            $sum_of_debit_before_from_date=Vault::whereNull('deleted_at')->where('transferDate','<',$request->fromDate)->where('transaction_type',1)->sum('totalAmount');
            $sum_of_credit_before_from_date=Vault::whereNull('deleted_at')->where('transferDate','<',$request->fromDate)->where('transaction_type',0)->sum('totalAmount');
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
        }
        else if($request->fromDate!='' && $request->toDate!='' && $request->company_id!=0)
        {
            $account_transactions=Vault::with(['company'=>function($q){$q->select('id','Name');}])->where('company_id','=',$request->company_id)->whereBetween('transferDate', [$request->fromDate, $request->toDate])->orderBy('transferDate')->orderBy('id')->get();
            $sum_of_debit_before_from_date=Vault::where('company_id','=',$request->company_id)->whereNull('deleted_at')->where('transferDate','<',$request->fromDate)->where('transaction_type',1)->sum('totalAmount');
            $sum_of_credit_before_from_date=Vault::where('company_id','=',$request->company_id)->whereNull('deleted_at')->where('transferDate','<',$request->fromDate)->where('transaction_type',0)->sum('totalAmount');
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
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $company='Company Name : '.$request->company_name;
            $investor='Vault Report';

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
                <th align="center" width="178">Description</th>
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
                if($row[$i]['transaction_type']==0)
                {
                    $debit_total += $row[$i]['totalAmount'];
                    $balance = $balance - $row[$i]['totalAmount'];
                }
                elseif($row[$i]['transaction_type']==1)
                {
                    $credit_total += $row[$i]['totalAmount'];
                    $balance = $balance + $row[$i]['totalAmount'];
                }
                else
                {
                    $balance += $row[$i]['totalAmount'];
                }

                $html .='<tr>
                    <td align="left" width="57">'.(date('d-m-Y', strtotime($row[$i]['transferDate']))).'</td>
                    <td align="left" width="178">'.$row[$i]['Description'].'</td>
                    <td align="left" width="80">'.$row[$i]['company']['Name'].'</td>';
                if($row[$i]['transaction_type']==1)
                {
                    $html.='<td align="right" width="75">'.(number_format($row[$i]['totalAmount'],2,'.',',')).'</td>';
                    $html.='<td align="right" width="75">'.(number_format(0,2,'.',',')).'</td>';
                }
                else
                {
                    $html.='<td align="right" width="75">'.(number_format(0,2,'.',',')).'</td>';
                    $html.='<td align="right" width="75">'.(number_format($row[$i]['totalAmount'],2,'.',',')).'</td>';
                }
                $html.='<td align="right" width="80">'.(number_format($balance,2,'.',',')).'</td></tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0.5" cellpadding="1">';
            $html.= '<tr>
                 <td width="315" align="right" colspan="3">Total : </td>
                 <td width="75" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="75" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($balance,2,'.',',').'</td>
             </tr>';
            $pdf::SetFillColor(255, 0, 0);
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
}
