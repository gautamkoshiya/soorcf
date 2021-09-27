<?php


namespace App\WebRepositories;


use App\MISC\CustomeFooter;
use App\Models\Company;
use App\Models\OtherStock;
use App\WebRepositories\Interfaces\IOtherStockRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class OtherStockRepository implements IOtherStockRepositoryInterface
{
    public function index()
    {
        if (request()->ajax()) {
            return datatables()->of(OtherStock::where('company_id', session('company_id'))->where('isActive', 1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '';
                    $button .= '&nbsp;<a href="' . url('deleteOtherStock', $data->id) . '" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    return $button;
                })
                ->rawColumns(
                    [
                        'action',
                    ])
                ->make(true);
        }
        return view('admin.other_stock.index');
    }

    public function create()
    {
        return view('admin.other_stock.create');
    }

    public function store(Request $request)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $in=0;$out=0;$differance=0;
        if($request->in==0)
        {
            //out entry
            $out=$request->out;
            $sum_of_inward=OtherStock::where('company_id',$company_id)->whereNull('deleted_at')->sum('in');
            $sum_of_outward=OtherStock::where('company_id',$company_id)->whereNull('deleted_at')->sum('out');
            $differance=$sum_of_inward-$sum_of_outward;
            $differance-=$out;
        }
        else
        {
            //in entry
            $in=$request->in;
            $sum_of_inward=OtherStock::where('company_id',$company_id)->whereNull('deleted_at')->sum('in');
            $sum_of_outward=OtherStock::where('company_id',$company_id)->whereNull('deleted_at')->sum('out');
            $differance=$sum_of_inward-$sum_of_outward;
            $differance+=$in;
        }
        $entry = new OtherStock();
        $entry->createdDate = $request->createdDate;
        $entry->in = $in;
        $entry->out = $out;
        $entry->differance = $differance;
        $entry->Description = $request->Description;
        $entry->user_id = $user_id;
        $entry->company_id = $company_id;
        $entry->save();
        return true;
    }

    public function delete($Id)
    {
        $entry = OtherStock::findOrFail($Id);
        $user_id = session('user_id');
        if ($entry) {
            DB::transaction(function () use ($entry, $user_id) {
                OtherStock::where('id', $entry->id)->update(['user_id' => $user_id,]);
                OtherStock::where('id', $entry->id)->delete();
            });
            return redirect()->route('other_stocks.index');
        } else {
            return redirect()->route('other_stocks.index');
        }
    }

    public function GetOtherStockReport()
    {
        return view('admin.other_stock.other_stock_report');
    }

    public function PrintOtherStockStatement(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            //$all_transactions=OtherStock::where('company_id',session('company_id'))->whereNull('deleted_at')->orderBy('createdDate')->orderBy('id')->get();

            $all_transactions=OtherStock::where('company_id','=',session('company_id'))->whereBetween('createdDate', [$request->fromDate, $request->toDate])->whereNull('deleted_at')->orderBy('createdDate')->orderBy('id')->get();
            $sum_of_in_before_from_date=OtherStock::where('company_id','=',session('company_id'))->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('in');
            $sum_of_out_before_from_date=OtherStock::where('company_id','=',session('company_id'))->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('out');
            $closing_stock=$sum_of_in_before_from_date-$sum_of_out_before_from_date;

            if($all_transactions->first())
            {
                $footer=new CustomeFooter;
                $footer->footer();
                $pdf = new PDF();
                $pdf::setPrintHeader(false);
                $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf::SetAutoPageBreak(TRUE, 14);

                $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
                $pdf::SetFillColor(255,255,0);
                $row=json_decode(json_encode($all_transactions), true);

                $pdf::SetFont('helvetica', '', 12);
                $title='Other Stock Statement ';
                $time='Date : '.date('d-m-Y h:i:s A');

                $pdf::Cell(95,5,$title,'',0,'L');
                $pdf::Cell(95,5,$time,'',0,'R');
                $pdf::Ln(6);

                $opb=' Opening Stock  '.number_format($closing_stock,2,'.',',');
                //$pdf::Cell(95,5,$html,'',0,'L');
                $pdf::Cell(190,5,$opb,'',0,'R');
                $pdf::Ln(6);

                $balance=0;

                $in_total=0.0;
                $out_total=0.0;

                $pdf::SetFont('helvetica', 'B', 14);
                $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="60">Date</th>
                <th align="center" width="285">Description</th>
                <th align="center" width="60">In</th>
                <th align="center" width="60">Out</th>
                <th align="right" width="70">Difference</th>
            </tr>';
                $pdf::SetFont('helvetica', '', 10);
                $last_closing=0.0;
                for($i=0;$i<count($row);$i++)
                {
                    if($i==0 && $closing_stock!=0)
                    {
                        $balance += $closing_stock;
                    }
                    if($row[$i]['in']!=0)
                    {
                        $in_total += $row[$i]['in'];
                        $balance = $balance + $row[$i]['in'];
                    }
                    elseif($row[$i]['out']!=0)
                    {
                        $out_total += $row[$i]['out'];
                        $balance = $balance - $row[$i]['out'];
                    }

                    if($row[$i]['out']==0)
                    {
                        $html .='<tr style="background-color: #e3e3e3">
                <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left" width="285">'.($row[$i]['Description']).'</td>
                <td align="right" width="60">'.number_format(($row[$i]['in']),2,'.',',').'</td>
                <td align="right" width="60">'.number_format(($row[$i]['out']),2,'.',',').'</td>
                <td align="right" width="70">'.number_format($balance,2,'.',',').'</td>
                </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left" width="285">'.($row[$i]['Description']).'</td>
                <td align="right" width="60">'.number_format(($row[$i]['in']),2,'.',',').'</td>
                <td align="right" width="60">'.number_format(($row[$i]['out']),2,'.',',').'</td>
                <td align="right" width="70">'.number_format($balance,2,'.',',').'</td>
                </tr>';
                    }
                    $last_closing=$balance;
                }
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');

                $pdf::SetFont('helvetica', 'B', 12);

                $html='<table border="0.5" cellpadding="2">';
                $html.= '
             <tr>
             <td width="345" align="right" colspan="2">Total : </td>
             <td width="60" align="right">'.number_format($in_total,2,'.',',').'</td>
             <td width="60" align="right">'.number_format($out_total,2,'.',',').'</td>
             <td width="70" align="right">'.number_format($last_closing,2,'.',',').'</td>
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
        else
        {
            return FALSE;
        }
    }
}
