<?php

namespace App\WebRepositories;

use App\MISC\CustomeFooter;
use App\Models\Company;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\Supplier;
use App\Models\Unit;
use App\WebRepositories\Interfaces\IPurchaseInvoiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use PDF;

class PurchaseInvoiceRepository implements IPurchaseInvoiceRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(TaxInvoice::with('customer','project')->where('company_id',session('company_id'))->where('isActive',1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '';
                    $button.='<a href="'.route('tax_invoices.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button.='&nbsp;<a href="'.url('deleteTaxInvoice', $data->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    $button.='&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></button>';
                    $button.='&nbsp;<button class="btn btn-primary" onclick="payment(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-money"></i></button>';
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
        return view('admin.purchase_invoice.index');
    }

    public function all_purchase_invoice(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = PurchaseInvoice::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select pi.*,s.Name as supplier_name,p.Name as project_name from purchase_invoices as pi left join suppliers as s on s.id = pi.supplier_id left join projects as p on p.id = pi.project_id  where pi.company_id = '.session('company_id').' and pi.isActive = 1 and pi.deleted_at is null order by pi.id desc limit '.$limit.' offset '.$start ;
            $tax_invoice = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select pi.*,s.Name as supplier_name,p.Name as project_name from purchase_invoices as pi left join suppliers as s on s.id = pi.supplier_id left join projects as p on p.id = pi.project_id  where pi.company_id = '.session('company_id').' and pi.isActive = 1 and pi.deleted_at is null and pi.InvoiceNumber LIKE "%'.$search.'%" or pi.ReferenceNumber LIKE "%'.$search.'%" order by pi.id desc limit '.$limit.' offset '.$start ;;
            $tax_invoice = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,ti.*,c.Name as customer_name,p.Name as project_name from tax_invoices as ti left join customers as c on c.id = ti.customer_id left join projects as p on p.id = ti.project_id  where ti.company_id = '.session('company_id').' and ti.isActive = 1 and ti.deleted_at is null and ti.InvoiceNumber LIKE "%'.$search.'%" order by ti.id desc limit '.$limit.' offset '.$start ;
            $sales_count = DB::select(DB::raw($sql_count));
            if(!empty($sales_count))
            {
                $totalFiltered = $sales_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($tax_invoice))
        {
            foreach ($tax_invoice as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['InvoiceNumber'] = $single->InvoiceNumber ?? "N.A.";
                $nestedData['ReferenceNumber'] = $single->ReferenceNumber ?? "N.A.";
                $nestedData['FromDate'] = date('d-m-Y', strtotime($single->FromDate));
                $nestedData['DueDate'] = date('d-m-Y', strtotime($single->DueDate));
                $nestedData['supplier'] = $single->supplier_name ?? "N.A.";
                $nestedData['project'] = $single->project_name ?? "N.A.";
                $nestedData['subTotal'] = $single->subTotal ?? 0.00;
                $nestedData['totalVat'] = $single->totalVat ?? 0.00;
                $nestedData['discount'] = $single->discount ?? 0.00;
                $nestedData['grandTotal'] = $single->grandTotal ?? 0.00;
                $button = '';
                $button.='<a href="'.route('purchase_invoices.edit', $single->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                $button.='&nbsp;<a href="'.url('deleteTaxInvoice', $single->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
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
        $InvoiceNumber = $this->InvoiceNumber();
        $supplier = Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->where('isActive',1)->orderBy('id', 'desc')->get();
        $projects = Project::where('isActive',1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        return view('admin.purchase_invoice.create',compact('InvoiceNumber','supplier','projects','products','units'));
    }

    public function InvoiceNumber()
    {
        $max_id = PurchaseInvoice::max('id');
        if($max_id)
        {
            $max_id = PurchaseInvoice::where('id',$max_id)->first();
            $last=explode('-',$max_id->InvoiceNumber);
            if(isset($last[1]))
            {
                $newInvoiceID = 'PUR-'.($last[1] + 1);
            }
            return $newInvoiceID;
        }
        else
        {
            $newInvoiceID = 'PUR-'.(0 + 1);
        }
        return $newInvoiceID;
    }

    public function getInvoiceNumberByProject($Id)
    {
        $prefix=null;
        switch($Id)
        {
            case 1:
                $prefix='AWF-';
                break;
            case 2:
                $prefix='AWB-';
                break;
            case 3:
                $prefix='ANA-';
                break;
        }
        $max_id = TaxInvoice::where('project_id',$Id)->max('id');
        if($max_id)
        {
            $max_id = TaxInvoice::where('id',$max_id)->first();
            $last=explode('-',$max_id->InvoiceNumber);
            if(isset($last[1]))
            {
                $newInvoiceID = $prefix.str_pad(($last[1] + 1), 4, '0', STR_PAD_LEFT);
            }
            return Response()->json(['invoice'=>$newInvoiceID]);
        }
        else
        {
            $newInvoiceID = $prefix.str_pad((0 + 1), 4, '0', STR_PAD_LEFT);
            return Response()->json(['invoice'=>$newInvoiceID]);
        }
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

                $invoice = new PurchaseInvoice();
                $invoice->InvoiceNumber = $request->Data['InvoiceNumber'];
                $invoice->ReferenceNumber = $request->Data['ReferenceNumber'];
                $invoice->user_id = $user_id;
                $invoice->company_id = $company_id;
                $invoice->project_id = $request->Data['project_id'];
                $invoice->supplier_id = $request->Data['supplier_id'];
                $invoice->FromDate = $request->Data['FromDate'];
                $invoice->DueDate = $request->Data['DueDate'];
                $invoice->TermsAndCondition = $request->Data['TermsAndCondition'];
                $invoice->CustomerNote = $request->Data['CustomerNote'];
                $invoice->IsNeedStampOrSignature = $request->Data['IsNeedStampOrSignature'];
                $invoice->subTotal = $request->Data['subTotal'];
                $invoice->totalVat = $request->Data['totalVat'];
                $invoice->discount = $request->Data['discount'];
                $invoice->grandTotal = $request->Data['grandTotal'];
                $invoice->save();
                $invoice = $invoice->id;

                foreach($request->Data['orders'] as $detail)
                {
                    PurchaseInvoiceDetail::create([
                        "purchase_invoice_id" => $invoice,
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
                $invoice = PurchaseInvoice::find($Id);
                $user_id = session('user_id');
                $company_id = session('company_id');

                $invoice->update([
                    'project_id' => $request->Data['project_id'],
                    'ReferenceNumber' => $request->Data['ReferenceNumber'],
                    'supplier_id' => $request->Data['supplier_id'],
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

                PurchaseInvoiceDetail::where('purchase_invoice_id', array($Id))->forceDelete();
                //QuotationDetail::where('quotation_id', array($Id))->delete();
                foreach ($request->Data['orders'] as $detail)
                {
                    PurchaseInvoiceDetail::create([
                        "purchase_invoice_id" => $Id,
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
        $invoice = PurchaseInvoice::with('supplier','project')->where('id',$Id)->first();
        $supplier = Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->where('isActive',1)->orderBy('id', 'desc')->get();
        $projects = Project::where('isActive',1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        $tax_invoice_details = PurchaseInvoiceDetail::withTrashed()->with('product','unit')->where('purchase_invoice_id', $Id)->get();
        return view('admin.purchase_invoice.edit',compact('invoice','tax_invoice_details','supplier','projects','products','units'));
    }

    public function delete($Id)
    {
        $quotation = TaxInvoice::findOrFail($Id);
        $user_id = session('user_id');
        if($quotation)
        {
            DB::transaction(function () use($quotation,$user_id)
            {
                TaxInvoiceDetail::where('tax_invoice_id',$quotation->id)->update(['user_id' => $user_id,]);
                TaxInvoiceDetail::where('tax_invoice_id',$quotation->id)->delete();
                TaxInvoice::where('id',$quotation->id)->update(['user_id' => $user_id,]);
                TaxInvoice::where('id',$quotation->id)->delete();
            });
            return redirect()->route('tax_invoices.index');
        }
        else
        {
            return redirect()->route('tax_invoices.index');
        }
    }

    public function GetPurchaseInvoiceReport()
    {
        $companies= Company::get();
        return view('admin.purchase_invoice.purchase_invoice_vat_report',compact('companies'));
    }

    public function PrintPurchaseInvoiceReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!=''  && $request->filter=='all' && $request->company=='all')
        {
            $invoice=PurchaseInvoice::with(['supplier'])->whereBetween('FromDate', [$request->fromDate, $request->toDate])->orderBy('FromDate')->get();
        }
        else if ($request->fromDate!='' && $request->toDate!=''  && $request->filter!='all' && $request->company!='all')
        {
            if($request->filter=='with')
            {
                $invoice=PurchaseInvoice::with(['supplier'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('FromDate')->get();
            }
            elseif($request->filter=='without')
            {
                $invoice=PurchaseInvoice::with(['supplier'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('FromDate')->get();
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->company=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $invoice=PurchaseInvoice::with(['supplier'])->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('FromDate')->get();
            }
            elseif($request->filter=='without')
            {
                $invoice=PurchaseInvoice::with(['supplier'])->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('FromDate')->get();
            }
            else
            {
                $invoice=PurchaseInvoice::with(['supplier'])->whereBetween('FromDate', [$request->fromDate, $request->toDate])->orderBy('FromDate')->get();
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->company!='all')
        {
            if($request->filter=='with')
            {
                $invoice=PurchaseInvoice::with(['supplier'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('FromDate')->get();
            }
            elseif($request->filter=='without')
            {
                $invoice=PurchaseInvoice::with(['supplier'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('FromDate')->get();
            }
            else
            {
                $invoice=PurchaseInvoice::with(['supplier'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->orderBy('FromDate')->get();
            }
        }
        else
        {
            return FALSE;
        }

        if($invoice->first())
        {
            $row=json_decode(json_encode($invoice), true);
            //echo "<pre>";print_r($row);die;
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $lpo=Project::where('id',1)->first();
            $company_title = $lpo->Name;
            $company_email = $lpo->Email;
            $company_address = $lpo->Address;
            $company_mobile = $lpo->Contact;
            $company_fax = $lpo->FAX;
            $company_trn = $lpo->TRN;

            $base = URL::to('/storage/app/public/project/');
            $logo_url = $base . '/' . $lpo->logo;

            $html = '<table border="0">';
            $html .= '<tr>
            <td width="150" rowspan="6"><img src="' . $logo_url . '" height="100px;" width="100px;"></td>
            <td width="300" style="font-weight: bold;font-size: xx-large;"> ' . $company_title . '</td>
            <td width="85"></td>
        </tr>';
            $html .= '<tr>
            <td width="300" style="font-size: large;"> Email : ' . $company_email . '</td>
            <td width="85"></td>
        </tr>';
            $html .= '<tr>
            <td width="300" style="font-size: large;"> Address : ' . $company_address . '</td>
            <td width="85"></td>
        </tr>';
            $html .= '<tr>
            <td width="270" style="font-size: large;"> Phone : ' . $company_mobile . '</td>
            <td width="115" align="right" style="font-size: large;"></td>
        </tr>';
            $html .= '<tr>
            <td width="270" style="font-size: large;"> FAX : ' . $company_fax . '</td>
            <td width="115" align="right" style="font-size: large;"></td>
        </tr>';
            $html .= '<tr>
            <td width="270" style="font-size: large;"> TRN : ' . $company_trn . '</td>
            <td width="115" align="right" style="font-size: large;"></td>
        </tr>';
            $html .= '</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $pdf::SetFont('helvetica', '', 12);
            $title='Purchase Invoice Report - From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y', strtotime($request->toDate));
            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 8);
            $pdf::Ln(6);

            $sub_total_sum=0.0;
            $vat_sum=0.0;
            $grand_total_sum=0.0;

            $pdf::SetFont('helvetica', '', 8);

            $html = '<table border="0.5" cellpadding="1">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="45">Date</th>
                <th align="center" width="60">REF#</th>
                <th align="center" width="225">Supplier</th>
                <th align="center" width="70">TRN</th>
                <th align="center" width="50">Taxable</th>
                <th align="center" width="45">VAT</th>
                <th align="center" width="55">TotalAmount</th>
            </tr>';
            for($i=0;$i<count($row);$i++)
            {
                $sub_total_sum+=$row[$i]['subTotal'];
                $vat_sum+=$row[$i]['totalVat'];
                $grand_total_sum+=$row[$i]['grandTotal'];
                $html .='<tr>
                <td align="center" width="45">'.(date('d-M-y', strtotime($row[$i]['FromDate']))).'</td>
                <td align="left" width="60">'.($row[$i]['ReferenceNumber']).'</td>
                <td align="left" width="225">'.($row[$i]['supplier']['Name']).'</td>
                <td align="left" width="70">'.($row[$i]['supplier']['TRNNumber']).'</td>
                <td align="right" width="50">'.(number_format($row[$i]['subTotal'],2,'.',',')).'</td>
                <td align="right" width="45">'.(number_format($row[$i]['totalVat'],2,'.',',')).'</td>
                <td align="right" width="55">'.(number_format($row[$i]['grandTotal'],2,'.',',')).'</td>
                </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $html = '<table cellpadding="1">';
            $html .= '<tr>
                <td align="right" width="370" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;"> Sub Total </td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($sub_total_sum, 2, '.', ','))) . '</td>
                </tr>';
            $html .= '<tr>
                <td align="right" width="370" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;"> Total VAT </td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($vat_sum, 2, '.', ','))) . '</td>
                </tr>';
            $html .= '<tr>
                <td align="right" width="370" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;"> Grand Total </td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($grand_total_sum, 2, '.', ','))) . '</td>
                </tr>';
            $html .= '</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            $pdf::SetFillColor(255, 0, 0);

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }
}
