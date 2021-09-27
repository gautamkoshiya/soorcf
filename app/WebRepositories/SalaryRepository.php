<?php


namespace App\WebRepositories;


use App\Models\AccountTransaction;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Salary;
use App\Models\SalaryDetail;
use App\Models\SaleDetail;
use App\WebRepositories\Interfaces\ISalaryRepositoryInterface;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use PDF;

class SalaryRepository implements ISalaryRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Salary::with(['company'])->latest()->get())
                ->addColumn('action', function ($data) {
                    $button ='&nbsp;<button class="btn btn-dark" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-print"></i></button>';
                    return $button;
                })
                ->addColumn('company', function($data) {
                    return $data->company->Name ?? "N.A.";
                })
                ->addColumn('Month', function($data) {
                    $obj=DateTime::createFromFormat('!m', $data->Month);
                    return $obj->format('F') ?? "N.A.";
                })
                ->addColumn('GeneratedBy', function($data) {
                    return $data->user->name ?? "N.A.";
                })
                ->rawColumns([
                    'action',
                    'company',
                ])
                ->make(true);
        }
        return view('admin.salary.index');
    }

    public function create()
    {
        $companies = Company::get();
        return view('admin.salary.create',compact('companies'));
    }

    public function store(Request $request)
    {
        //echo "<pre>";print_r($request->all());die;
        DB::transaction(function () use($request)
        {
            $user_id = session('user_id');
            $request_month=explode('-',$request->Data['month']);
            $salary = new Salary();
            $salary->user_id = $user_id;
            $salary->company_id = $request->Data['company_id'];
            $salary->TotalAmount = $request->Data['totalAmount'];
            $salary->Month = $request_month[1];
            $salary->Year = $request_month[0];
            $salary->createdDate = date('Y-m-d');
            $salary->isActive = 1;
            $salary->save();
            $salary = $salary->id;

            foreach($request->Data['orders'] as $detail)
            {
                $ref_no=strtoupper(uniqid('SS'));
                SalaryDetail::create([
                    "user_id" => $user_id,
                    "company_id" => $request->Data['company_id'],
                    "salary_id" => $salary,
                    "employee_id" => $detail['employee_id'],
                    "BasicAmount" => $detail['salary_amount'],
                    "Month" => $request_month[1],
                    "Year" => $request_month[0],
                    "isActive" => 1,
                    "ReferenceNo" => $ref_no,
                ]);

                $accountTransaction = AccountTransaction::where(['employee_id'=> $detail['employee_id'],])->get();
                if ($accountTransaction->first())
                {
                    $AccData =
                        [
                            'employee_id' => $detail['employee_id'],
                            'Credit' => 0.00,
                            'Debit' => $request->Data['totalAmount'],
                            'createdDate' => date('Y-m-d'),
                            'user_id' => $user_id,
                            'company_id' => $request->Data['company_id'],
                            'Description'=>'Salary|'.$salary,
                            'referenceNumber'=>$ref_no,
                            'TransactionDesc'=>'Salary Generated for Month of '.date('M-Y', strtotime($request->Data['month'])),
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                }
            }
        });
        $data=array('result'=>true,'message'=>'Record Inserted Successfully.');
        echo json_encode($data);
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
    }

    public function delete($Id)
    {
        // TODO: Implement delete() method.
    }

    public function getCompanyEmployee($Id)
    {
        $employees=Employee::select('id','Name','Basic')->where('company_id',$Id)->where('isActive',1)->get();
        return response()->json(['employees'=>$employees]);
    }

    public function printSalary($id)
    {
        $pdf = new PDF();
        $pdf::setPrintHeader(false);
        $pdf::setPrintFooter(false);
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $salary_details=SalaryDetail::with(['employee'=> function($q){$q->select('id','Name','Mobile','email','designation_id','UpdateDescription');},'employee.designation'=> function($q){$q->select('id','Name');}])->where('salary_id',$id)->get();

        if($salary_details->first())
        {
            foreach ($salary_details as $single)
            {
                //echo "<pre>";print_r($single);die;
                $pdf::AddPage('', 'A4');
                $pdf::SetFont('helvetica', '', 9);
                $pdf::SetFillColor(255,255,0);

                $project=Project::where('id',$single->employee->UpdateDescription)->first();
                $company_title=$project->Name;
                $company_email=$project->Email;
                $company_address=$project->Address;
                $company_mobile=$project->Contact;
                $company_fax=$project->FAX;
                $company_trn=$project->TRN;

                //$from='Date : -';
                $to='Date : _____________';
                $str='<b>'.$single->ReferenceNo.'</b>';

                $base=URL::to('/storage/app/public/project/');
                $logo_url=$base.'/'.$project->logo;
                //$logo_url='';
                $from='';

                $html='<table border="0">';
                $html.='<tr>
            <td width="150" rowspan="6"><img src="'.$logo_url.'" height="100px;" width="100px;"></td>
            <td width="300" style="font-weight: bold;font-size: xx-large;"> '.$company_title.'</td>
            <td width="85"></td>
        </tr>';
                $html.='<tr>
            <td width="300" style="font-size: large;"> Email : '.$company_email.'</td>
            <td width="85"></td>
        </tr>';
                $html.='<tr>
            <td width="300" style="font-size: large;"> Address : '.$company_address.'</td>
            <td width="85"></td>
        </tr>';
                $html.='<tr>
            <td width="270" style="font-size: large;"> Phone : '.$company_mobile.'</td>
            <td width="115" align="right" style="font-size: large;">'.$str.'</td>
        </tr>';
                $html.='<tr>
            <td width="270" style="font-size: large;"> FAX : '.$company_fax.'</td>
            <td width="115" align="right" style="font-size: large;">'.$from.'</td>
        </tr>';
                $html.='<tr>
            <td width="270" style="font-size: large;"> TRN : '.$company_trn.'</td>
            <td width="115" align="right" style="font-size: large;">'.$to.'</td>
        </tr>';
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');

                $pdf::writeHTML("<hr>", true, false, false, false, '');

                $pdf::SetFont('helvetica', 'B', 16);
                $html='<u>Salary Slip</u>';
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

                $pdf::Ln(2);

                $transactions=AccountTransaction::where('employee_id',$single->employee_id)->orderBy('id')->limit(10)->get();
                //get current balance
                $current_credit_sum=AccountTransaction::where('employee_id',$single->employee_id)->sum('Credit');
                $current_debit_sum=AccountTransaction::where('employee_id',$single->employee_id)->sum('Debit');
                $current_balance=$current_debit_sum-$current_credit_sum;
                //get opening balance
                $condition_date=$transactions->first();
                $opening_credit_sum=AccountTransaction::where('employee_id',$single->employee_id)->where('createdDate','<',$condition_date->createdDate)->sum('Credit');
                $opening_debit_sum=AccountTransaction::where('employee_id',$single->employee_id)->where('createdDate','<',$condition_date->createdDate)->sum('Debit');
                $opening_balance=$opening_debit_sum-$opening_credit_sum;

                //style="border-color: #0080C0;"
                $pdf::SetFont('helvetica', '', 11);
                $html = '<table cellpadding="2">';
                $html .= '<tr>
                        <td width="400" align="left" colspan="2">Employee Information :</td>
                        <td width="135" align="right"></td>
                </tr>';
                $html .= '<tr>
                        <td width="80" align="left">Name :</td>
                        <td width="305" align="left" style="font-weight: bold;">'.$single->employee->Name.'</td>
                        <td width="110" align="right" style="font-weight: bold;">Remaining Amount :</td>
                        <td width="60" align="right" style="font-weight: bold;">'.number_format($current_balance,2,'.',',').'</td>
                </tr>';
                $html .= '<tr>
                        <td width="80" align="left">Basic Salary :</td>
                        <td width="305" align="left">'.$single->BasicAmount.'</td>
                        <td width="110" align="right" style="font-weight: bold;">Issued Amount :</td>
                        <td width="60" align="right" style="font-weight: bold;">_________</td>
                </tr>';
                $html .= '<tr>
                        <td width="80" align="left">Contact :</td>
                        <td width="305" align="left">'.$single->employee->Mobile.'</td>
                        <td width="110" align="right" style="font-weight: bold;">Amount in Words :</td>
                        <td width="50"></td>
                </tr>';
                $html .= '<tr>
                        <td width="80" align="left">Email :</td>
                        <td width="105" align="left" >'.$single->employee->email.'</td>
                        <td width="360" colspan="1" align="right" style="font-weight: bold;">_______________________________________</td>
                </tr>';
                $html .= '<tr>
                        <td width="80" align="left">Designation :</td>
                        <td width="455" align="left" colspan="2" style="font-weight: bold;">'.$single->employee->designation->Name.'</td>
                </tr>';
                $obj=DateTime::createFromFormat('!m', $single->Month);
                $description=$obj->format('F').'-'.$single->Year;
                $html .= '<tr>
                        <td width="80" align="left">Description :</td>
                        <td width="455" align="left" colspan="2">'.$description.'</td>
                </tr>';
                $html .= '</table>';
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
                $pdf::Ln(6);

                $pdf::SetFont('helvetica', '', 12);
                $html=' Opening Balance '.number_format($opening_balance,2,'.',',');
                $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

                $pdf::SetFont('helvetica', 'B', 12);
                $html = '<table border="0.5" cellpadding="1">
                <tr style="color: rgb(0,0,0);font-weight: bold;">
                    <th align="center" width="65">Date</th>
                    <th align="center" width="260">Description</th>
                    <th align="center" width="70">Debit</th>
                    <th align="center" width="70">Credit</th>
                    <th align="center" width="70">Balance</th>
                </tr>';

                //echo "<pre>";print_r($transactions);die;
                $balance=0;
                $debit_total=0;
                $credit_total=0;
                foreach($transactions as $single_transaction)
                {
                    if($single_transaction->Debit!=0)
                    {
                        $debit_total += $single_transaction->Debit;
                        $balance = $balance + $single_transaction->Debit;
                    }
                    elseif($single_transaction->Credit!=0)
                    {
                        $credit_total += $single_transaction->Credit;
                        $balance = $balance - $single_transaction->Credit;
                    }
                    else
                    {
                        $balance += 0;
                    }
                    $html.='<tr>';
                    $html.='<td align="center" width="65">'.date('d-M-Y', strtotime($single_transaction->createdDate)).'</td>';
                    $html.='<td align="left" width="260">'.($single_transaction->TransactionDesc).'</td>';
                    $html.='<td align="right" width="70">'.(number_format($single_transaction->Debit,2,'.',',')).'</td>';
                    $html.='<td align="right" width="70">'.(number_format($single_transaction->Credit,2,'.',',')).'</td>';
                    $html.='<td align="right" width="70">'.(number_format($balance,2,'.',',')).'</td>';
                    $html.='</tr>';
                }
                $html.='</table>';
                $pdf::SetFont('helvetica', '', 10);
                $pdf::writeHTML($html, true, false, false, false, '');

                $pdf::SetFont('helvetica', 'B', 13);
                if($balance<0)
                {
                    $html='<table border="0.5" cellpadding="1">';
                    $html.= '
                     <tr>
                         <td width="325" align="right" colspan="3">Total : </td>
                         <td width="70" align="right">'.number_format($debit_total,2,'.',',').'</td>
                         <td width="70" align="right">'.number_format($credit_total,2,'.',',').'</td>
                         <td width="70" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
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
                         <td width="325" align="right" colspan="3">Total : </td>
                         <td width="70" align="right">'.number_format($debit_total,2,'.',',').'</td>
                         <td width="70" align="right">'.number_format($credit_total,2,'.',',').'</td>
                         <td width="70" align="right">'.number_format($balance,2,'.',',').'</td>
                     </tr>';
                    $pdf::SetFillColor(255, 0, 0);
                    $html.='</table>';
                    $pdf::writeHTML($html, true, false, false, false, '');
                }

                $pdf::SetFont('times', 'B', 9);
                $pdf::Cell(190, 5, '','',0,'L');
                $pdf::Ln(5);

                $pdf::SetFont('times', 'B', 9);
                $pdf::Cell(190, 5, '','',0,'L');
                $pdf::Ln(5);
                $pdf::SetFont('times', 'B', 9);
                $pdf::Cell(190, 5, '','',0,'L');
                $pdf::Ln(5);

                $pdf::SetFont('times', 'B', 10);
                $pdf::Cell(95, 5,' Issued By [ Name & Signature ]','B',0,'C');
                $pdf::Cell(95, 5,' Received By [ Name & Signature ]','B',0,'C');
                $pdf::Ln(5);

                $pdf::Ln(5);
                $pdf::Ln(5);
                $pdf::writeHTMLCell(0, 0, '', '', 'Special Note :',0, 1, 0, true, 'L', true);
                $pdf::Ln(5);
                $pdf::Ln(5);
                $pdf::writeHTMLCell(0, 0, '', '', '<hr>',0, 1, 0, true, 'C', true);

                $pdf::lastPage();
            }
        }

        $time=time();
        $name='salary_'.$time;
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$name.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }
}
