<?php


namespace App\WebRepositories;


use App\MISC\amountToWords;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProformaInvoice;
use App\Models\ProformaInvoiceDetail;
use App\Models\Project;
use App\Models\Unit;
use App\WebRepositories\Interfaces\IProformaInvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use PDF;

class ProformaInvoiceRepository implements IProformaInvoiceRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(ProformaInvoice::with('customer','project')->where('company_id',session('company_id'))->where('isActive',1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '';
                    $button.='<a href="'.route('proforma_invoices.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button.='&nbsp;<a href="'.url('deleteProformaInvoice', $data->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    $button.='&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></button>';
                    return $button;
                })
                ->addColumn('FromDate', function($data) {
                    return date('d-m-Y', strtotime($data->FromDate)) ?? "No date";
                })
                ->addColumn('DueDate', function($data) {
                    return date('d-m-Y', strtotime($data->DueDate)) ?? "No date";
                })
                ->addColumn('customer', function($data) {
                    return $data->customer->Name ?? "No Name";
                })
                ->addColumn('project', function($data) {
                    return $data->project->Name ?? "No Name";
                })
                ->rawColumns(
                    [
                        'action',
                        'customer',
                        'project',
                        'FromDate',
                        'DueDate',
                    ])
                ->make(true);
        }
        return view('admin.proforma_invoice.index');
    }

    public function all_proforma(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = ProformaInvoice::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select pi.*,c.Name as customer_name,p.Name as project_name from proforma_invoices as pi left join customers as c on c.id = pi.customer_id left join projects as p on p.id = pi.project_id  where pi.company_id = '.session('company_id').' and pi.isActive = 1 and pi.deleted_at is null order by pi.id desc limit '.$limit.' offset '.$start ;
            $proforma = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select pi.*,c.Name as customer_name,p.Name as project_name from proforma_invoices as pi left join customers as c on c.id = pi.customer_id left join projects as p on p.id = pi.project_id  where pi.company_id = '.session('company_id').' and pi.isActive = 1 and pi.deleted_at is null and pi.PFINVNumber LIKE "%'.$search.'%" order by pi.id desc limit '.$limit.' offset '.$start ;
            $proforma = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,pi.*,c.Name as customer_name,p.Name as project_name from proforma_invoices as pi left join customers as c on c.id = pi.customer_id left join projects as p on p.id = pi.project_id  where pi.company_id = '.session('company_id').' and pi.isActive = 1 and pi.deleted_at is null and pi.PFINVNumber LIKE "%'.$search.'%" order by pi.id desc limit '.$limit.' offset '.$start ;
            $sales_count = DB::select(DB::raw($sql_count));
            if(!empty($sales_count))
            {
                $totalFiltered = $sales_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($proforma))
        {
            foreach ($proforma as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['PFINVNumber'] = $single->PFINVNumber ?? "N.A.";
                $nestedData['FromDate'] = date('d-m-Y', strtotime($single->FromDate));
                $nestedData['DueDate'] = date('d-m-Y', strtotime($single->DueDate));
                $nestedData['customer'] = $single->customer_name ?? "N.A.";
                $nestedData['project'] = $single->project_name ?? "N.A.";
                $nestedData['subTotal'] = $single->subTotal ?? 0.00;
                $nestedData['totalVat'] = $single->totalVat ?? 0.00;
                $nestedData['discount'] = $single->discount ?? 0.00;
                $nestedData['grandTotal'] = $single->grandTotal ?? 0.00;
                $button = '';
                $button.='<a href="'.route('proforma_invoices.edit', $single->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                $button.='&nbsp;<a href="'.url('deleteProformaInvoice', $single->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                $button.='&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></button>';
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
        $PFINVNumber = $this->PFINVNumber();
        $customers = Customer::where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
        $projects = Project::where('isActive',1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        return view('admin.proforma_invoice.create',compact('PFINVNumber','customers','projects','products','units'));
    }

    public function PFINVNumber()
    {
        $max_id = ProformaInvoice::max('id');
        if($max_id)
        {
            $max_id = ProformaInvoice::where('id',$max_id)->first();
            $last=explode('#',$max_id->PFINVNumber);
            if(isset($last[1]))
            {
                $newInvoiceID = 'PFINV#'.str_pad(($last[1] + 1), 4, '0', STR_PAD_LEFT);
            }
            return $newInvoiceID;
        }
        else
        {
            $newInvoiceID = 'PFINV#'.str_pad((0 + 1), 4, '0', STR_PAD_LEFT);
        }
        return $newInvoiceID;
    }

    public function store(Request $request)
    {
        $AllRequestCount = collect($request->Data)->count();
        DB::transaction(function () use($AllRequestCount,$request)
        {
            if($AllRequestCount > 0)
            {
                $user_id = session('user_id');
                $company_id = session('company_id');

                $proforma = new ProformaInvoice();
                $proforma->PFINVNumber = $request->Data['PFINVNumber'];
                $proforma->user_id = $user_id;
                $proforma->company_id = $company_id;
                $proforma->project_id = $request->Data['project_id'];
                $proforma->customer_id = $request->Data['customer_id'];
                $proforma->FromDate = $request->Data['FromDate'];
                $proforma->DueDate = $request->Data['DueDate'];
                $proforma->TermsAndCondition = $request->Data['TermsAndCondition'];
                $proforma->CustomerNote = $request->Data['CustomerNote'];
                $proforma->IsNeedStampOrSignature = $request->Data['IsNeedStampOrSignature'];
                $proforma->subTotal = $request->Data['subTotal'];
                $proforma->totalVat = $request->Data['totalVat'];
                $proforma->discount = $request->Data['discount'];
                $proforma->grandTotal = $request->Data['grandTotal'];
                $proforma->save();
                $proforma = $proforma->id;

                foreach($request->Data['orders'] as $detail)
                {
                    ProformaInvoiceDetail::create([
                        "proforma_invoice_id" => $proforma,
                        "user_id" => $user_id,
                        "company_id" => $company_id,
                        "product_id" => $detail['product_id'],
                        "Description" => $detail['Description'],
                        "unit_id" => $detail['unit_id'],
                        "Quantity" => $detail['Quantity'],
                        "Price" => $detail['Price'],
                        "rowTotal" => $detail['rowTotal'],
                        "VAT" => $detail['VAT'],
                        "rowVatAmount" => $detail['rowVatAmount'],
                        "rowSubTotal" => $detail['rowSubTotal'],
                        "createdDate" => date('Y-m-d'),
                    ]);
                }
            }
            $data=array('result'=>true,'message'=>'Record Inserted Successfully.');
            echo json_encode($data);
        });
    }

    public function update(Request $request, $Id)
    {
        $AllRequestCount = collect($request->Data)->count();
        DB::transaction(function () use($AllRequestCount,$request,$Id)
        {
            if($AllRequestCount > 0)
            {
                $proforma = ProformaInvoice::find($Id);
                $user_id = session('user_id');
                $company_id = session('company_id');

                $proforma->update([
                    'project_id' => $request->Data['project_id'],
                    'customer_id' => $request->Data['customer_id'],
                    'FromDate' => $request->Data['FromDate'],
                    'DueDate' => $request->Data['DueDate'],
                    'TermsAndCondition' => $request->Data['TermsAndCondition'],
                    'CustomerNote' => $request->Data['CustomerNote'],
                    'IsNeedStampOrSignature' => $request->Data['IsNeedStampOrSignature'],
                    'subTotal' => $request->Data['subTotal'],
                    'totalVat' => $request->Data['totalVat'],
                    'discount' => $request->Data['discount'],
                    'grandTotal' => $request->Data['grandTotal'],
                    'user_id' => $user_id,
                ]);

                ProformaInvoiceDetail::where('proforma_invoice_id', array($Id))->forceDelete();
                //QuotationDetail::where('quotation_id', array($Id))->delete();
                foreach ($request->Data['orders'] as $detail)
                {
                    ProformaInvoiceDetail::create([
                        "proforma_invoice_id" => $Id,
                        "user_id" => $user_id,
                        "company_id" => $company_id,
                        "product_id" => $detail['product_id'],
                        "Description" => $detail['Description'],
                        "unit_id" => $detail['unit_id'],
                        "Quantity" => $detail['Quantity'],
                        "Price" => $detail['Price'],
                        "rowTotal" => $detail['rowTotal'],
                        "VAT" => $detail['VAT'],
                        "rowVatAmount" => $detail['rowVatAmount'],
                        "rowSubTotal" => $detail['rowSubTotal'],
                        "createdDate" => date('Y-m-d'),
                    ]);
                }
            }
        });
        return Response()->json(true);
    }

    public function edit($Id)
    {
        $proforma = ProformaInvoice::with('customer','project')->where('id',$Id)->first();
        $customers = Customer::where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
        $projects = Project::where('isActive',1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        $proforma_invoice_details = ProformaInvoiceDetail::withTrashed()->with('product','unit')->where('proforma_invoice_id', $Id)->get();
        return view('admin.proforma_invoice.edit',compact('proforma','proforma_invoice_details','customers','projects','products','units'));
    }

    public function delete($Id)
    {
        $quotation = ProformaInvoice::findOrFail($Id);
        $user_id = session('user_id');
        if($quotation)
        {
            DB::transaction(function () use($quotation,$user_id)
            {
                ProformaInvoiceDetail::where('proforma_invoice_id',$quotation->id)->update(['user_id' => $user_id,]);
                ProformaInvoiceDetail::where('proforma_invoice_id',$quotation->id)->delete();
                ProformaInvoice::where('id',$quotation->id)->update(['user_id' => $user_id,]);
                ProformaInvoice::where('id',$quotation->id)->delete();
            });
            return redirect()->route('proforma_invoices.index');
        }
        else
        {
            return redirect()->route('proforma_invoices.index');
        }
    }

    public function PrintProformaInvoice($id)
    {
        $pdf = new PDF();
        $pdf::setPrintHeader(false);
        $pdf::setPrintFooter(false);
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf::AddPage('', 'A4');
        $pdf::SetFont('helvetica', '', 9);
        $pdf::SetFillColor(255,255,0);

        $invoice=ProformaInvoice::with('proforma_invoice_details','proforma_invoice_details.product','proforma_invoice_details.unit','project','customer')->where('id',$id)->first();
        $user_name=User::select('Name')->where('id',$invoice->user_id)->first();
        //echo "<pre>";print_r($quotation);die;

        $company_title=$invoice->project->Name;
        $company_email=$invoice->project->Email;
        $company_address=$invoice->project->Address;
        $company_mobile=$invoice->project->Contact;
        $company_fax=$invoice->project->FAX;
        $company_trn=$invoice->project->TRN;

        $from='Date : -';
        if($invoice->FromDate!='')
        {
            $from='From : '.date('d-m-Y', strtotime($invoice->FromDate));
        }
        $to='Due Date : -';
        if($invoice->DueDate!='')
        {
            $to='Due Date : '.date('d-m-Y', strtotime($invoice->DueDate));
        }
        $str='<b>'.$invoice->PFINVNumber.'</b>';

        $base=URL::to('/storage/app/public/project/');
        $logo_url=$base.'/'.$invoice->project->logo;
        //$logo_url='';

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
        $html='<u>PROFORMA INVOICE</u>';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $pdf::Ln(2);

        //style="border-color: #0080C0;"
        $pdf::SetFont('helvetica', '', 11);
        $html = '<table cellpadding="2">';
        $html .= '<tr><td width="400" align="left" colspan="2" style="border-left: 2px solid #0080C0;">Customer Details :</td><td width="135" align="right" style="border-right: 2px solid #0080C0;">Created By : '.$user_name->Name.'</td></tr>';
        $html .= '<tr><td width="90" align="left" style="border-left: 2px solid #0080C0;">Name :</td><td width="445" align="left" colspan="2" style="border-right: 2px solid #0080C0;">'.$invoice->customer->Name.'</td></tr>';
        $html .= '<tr><td width="90" align="left" style="border-left: 2px solid #0080C0;">TRN :</td><td width="445" align="left" colspan="2" style="border-right: 2px solid #0080C0;">'.$invoice->customer->TRNNumber.'</td></tr>';
        $html .= '<tr><td width="90" align="left" style="border-left: 2px solid #0080C0;">Phone :</td><td width="445" align="left" colspan="2" style="border-right: 2px solid #0080C0;">'.$invoice->customer->Mobile.'</td></tr>';
        $html .= '<tr><td width="90" align="left" style="border-left: 2px solid #0080C0;">Contact Person :</td><td width="445" align="left" colspan="2" style="border-right: 2px solid #0080C0;">'.$invoice->customer->Representative.'</td></tr>';
        $html .= '<tr style="border-right: 2px solid #0080C0;"><td width="90" align="left" style="border-left: 2px solid #0080C0;">Address :</td><td width="445" align="left" colspan="2">'.$invoice->customer->Address.'</td></tr>';
        $html .= '</table>';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
        $pdf::Ln(6);

        $pdf::SetFont('helvetica', 'B', 12);
        $html = '<table border="1" cellpadding="2">
            <tr style="background-color: rgb(22, 90, 145); color: rgb(255,255,255);font-weight: bold;">
                <th align="center" width="45">S/N</th>
                <th align="center" width="50">Item</th>
                <th align="center" width="260">Description</th>
                <th align="center" width="50">Unit</th>
                <th align="center" width="50">Qty</th>
                <th align="center" width="30">Rate</th>
                <th align="center" width="50">Total</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        $i=0;
        foreach ($invoice->proforma_invoice_details as $row)
        {
            $html .='<tr>
                <td align="center" width="45">'.++$i.'</td>
                <td align="center" width="50">'.$row->product->Name.'</td>
                <td align="left" width="260">'.$row->Description.'</td>
                <td align="center" width="50">'.($row->unit->Name).'</td>
                <td align="right" width="50">'.((number_format($row->Quantity,2,'.',','))).'</td>
                <td align="right" width="30">'.((number_format($row->Price,2,'.',','))).'</td>
                <td align="right" width="50">'.((number_format($row->rowTotal,2,'.',','))).'</td>
                </tr>';
        }
        $html.='</table><table cellpadding="1">';
        $html .='<tr>
                <td align="right" width="355" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;"> Total (AED) </td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">'.((number_format($invoice->subTotal,2,'.',','))).'</td>
                </tr>';
        $html .='<tr>
                <td align="right" width="355" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">VAT (5%)</td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">'.((number_format($invoice->totalVat,2,'.',','))).'</td>
                </tr>';
        if($invoice->discount!=0)
        {
            $html .='<tr>
                <td align="right" width="355" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">Subtotal (AED)</td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">'.((number_format(($invoice->subTotal+$invoice->totalVat),2,'.',','))).'</td>
                </tr>';
            $html .='<tr>
                <td align="right" width="355" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">Discount (AED)</td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">'.((number_format($invoice->discount,2,'.',','))).'</td>
                </tr>';
        }
        $result = new amountToWords();
        $html .='<tr>
                <td align="left" width="355" colspan="3"><b>'.('Amount In Words : </b>'.$result->AmountInWords($invoice->grandTotal).' Only').'</td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">Net Amount (AED)</td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">'.((number_format($invoice->grandTotal,2,'.',','))).'</td>
                </tr>';
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $tc=$invoice->TermsAndCondition;
        $note=$invoice->CustomerNote;

        $html='<table style="border: 0.5px dotted black;">';
        $html.='<tr style="font-size: large;"><td width="260">  Terms and Condition :</td></tr>';
        $html.='<tr><td width="260">'.$tc.'</td></tr>';
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $html='<table style="border: 0.5px dotted black;">';
        $html.='<tr style="font-size: large;"><td width="260">  Customer Notes :</td></tr>';
        $html.='<tr><td width="260">'.$note.'</td></tr>';
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

//        $tc=$quotation->TermsAndCondition;
//        $note=$quotation->CustomerNote;
//        $pdf::MultiCell(95, 10,$tc, 1, 'L', 0, 0, '', '', true);
//        $pdf::MultiCell(95, 10,$note, 1, 'L', 0, 0, '', '', true);
//        $pdf::Ln(11);

//        $pdf::Cell(95, 5, '','L',0,'L');
//        $pdf::SetFont('times', 'B', 12);
//        $pdf::Cell(95, 5, ' '.$company_title,'R',0,'R');
//        $pdf::Ln(5);

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
        $pdf::Cell(95, 5,' Accepted By (Name & Signature) ','B',0,'C');
        $pdf::Cell(95, 5,' Issued By Name & Signature ','B',0,'C');
        $pdf::Ln(5);

        $pdf::lastPage();

        $time=time();
        $name='PFINVOICE_'.$time;
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$name.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }
}
