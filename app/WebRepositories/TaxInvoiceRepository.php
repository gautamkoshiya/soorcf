<?php


namespace App\WebRepositories;


use App\MISC\amountToWords;
use App\MISC\CustomeFooter;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Project;
use App\Models\TaxInvoice;
use App\Models\TaxInvoiceDetail;
use App\Models\TaxInvoicePayments;
use App\Models\Unit;
use App\WebRepositories\Interfaces\ITaxInvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use PDF;

class TaxInvoiceRepository implements ITaxInvoiceRepositoryInterface
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
        return view('admin.tax_invoice.index');
    }

    public function all_tax_invoice(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = TaxInvoice::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select ti.*,c.Name as customer_name,p.Name as project_name from tax_invoices as ti left join customers as c on c.id = ti.customer_id left join projects as p on p.id = ti.project_id  where ti.company_id = '.session('company_id').' and ti.isActive = 1 and ti.deleted_at is null order by ti.id desc limit '.$limit.' offset '.$start ;
            $tax_invoice = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select ti.*,c.Name as customer_name,p.Name as project_name from tax_invoices as ti left join customers as c on c.id = ti.customer_id left join projects as p on p.id = ti.project_id  where ti.company_id = '.session('company_id').' and ti.isActive = 1 and ti.deleted_at is null and ti.InvoiceNumber LIKE "%'.$search.'%" order by ti.id desc limit '.$limit.' offset '.$start ;
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
                $nestedData['FromDate'] = date('d-m-Y', strtotime($single->FromDate));
                $nestedData['DueDate'] = date('d-m-Y', strtotime($single->DueDate));
                $nestedData['customer'] = $single->customer_name ?? "N.A.";
                $nestedData['project'] = $single->project_name ?? "N.A.";
                $nestedData['subTotal'] = $single->subTotal ?? 0.00;
                $nestedData['totalVat'] = $single->totalVat ?? 0.00;
                $nestedData['discount'] = $single->discount ?? 0.00;
                $nestedData['grandTotal'] = $single->grandTotal ?? 0.00;
                $nestedData['RemainingBalance'] = $single->RemainingBalance ?? 0.00;
                $button = '';
                $button.='<a href="'.route('tax_invoices.edit', $single->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                $button.='&nbsp;<a href="'.url('deleteTaxInvoice', $single->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                $button.='&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></button>';
                $button.='&nbsp;<button class="btn btn-primary" onclick="payment(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-money"></i></button>';
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
        $customers = Customer::where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
        $projects = Project::where('isActive',1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        return view('admin.tax_invoice.create',compact('InvoiceNumber','customers','projects','products','units'));
    }

    public function InvoiceNumber()
    {
        $max_id = TaxInvoice::max('id');
        if($max_id)
        {
            $max_id = TaxInvoice::where('id',$max_id)->first();
            $last=explode('-',$max_id->InvoiceNumber);
            if(isset($last[1]))
            {
                $newInvoiceID = 'INV-'.str_pad(($last[1] + 1), 4, '0', STR_PAD_LEFT);
            }
            return $newInvoiceID;
        }
        else
        {
            $newInvoiceID = 'INV-'.str_pad((0 + 1), 4, '0', STR_PAD_LEFT);
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

                $invoice = new TaxInvoice();
                $invoice->InvoiceNumber = $request->Data['InvoiceNumber'];
                $invoice->user_id = $user_id;
                $invoice->company_id = $company_id;
                $invoice->project_id = $request->Data['project_id'];
                $invoice->customer_id = $request->Data['customer_id'];
                $invoice->FromDate = $request->Data['FromDate'];
                $invoice->DueDate = $request->Data['DueDate'];
                $invoice->TermsAndCondition = $request->Data['TermsAndCondition'];
                $invoice->CustomerNote = $request->Data['CustomerNote'];
                $invoice->IsNeedStampOrSignature = $request->Data['IsNeedStampOrSignature'];
                $invoice->subTotal = $request->Data['subTotal'];
                $invoice->totalVat = $request->Data['totalVat'];
                $invoice->discount = $request->Data['discount'];
                $invoice->grandTotal = $request->Data['grandTotal'];
                $invoice->RemainingBalance = $request->Data['grandTotal'];
                $invoice->save();
                $invoice = $invoice->id;

                foreach($request->Data['orders'] as $detail)
                {
                    TaxInvoiceDetail::create([
                        "tax_invoice_id" => $invoice,
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
                $invoice = TaxInvoice::find($Id);
                $user_id = session('user_id');
                $company_id = session('company_id');

                $invoice->update([
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

                TaxInvoiceDetail::where('tax_invoice_id', array($Id))->forceDelete();
                //QuotationDetail::where('quotation_id', array($Id))->delete();
                foreach ($request->Data['orders'] as $detail)
                {
                    TaxInvoiceDetail::create([
                        "tax_invoice_id" => $Id,
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
        $invoice = TaxInvoice::with('customer','project')->where('id',$Id)->first();
        $customers = Customer::where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
        $projects = Project::where('isActive',1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        $tax_invoice_details = TaxInvoiceDetail::withTrashed()->with('product','unit')->where('tax_invoice_id', $Id)->get();
        return view('admin.tax_invoice.edit',compact('invoice','tax_invoice_details','customers','projects','products','units'));
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

    public function GetTaxInvoiceDetails($id)
    {
        $remaining=TaxInvoice::select('RemainingBalance','grandTotal')->where('id',$id)->first();
        $payment_detail=TaxInvoicePayments::where('tax_invoice_id',$id)->get();
        $html='<table class="table table-responsive"><thead><th>SR</th><th>Date</th><th>Amount</th><th>Mode</th><th>Note</th></thead><tbody>';
        $i=0;
        foreach ($payment_detail as $item)
        {
            $html.='<tr>';
            $html.='<td>'.++$i.'</td>';
            $html.='<td>'.date('d-M-Y',strtotime($item->PaymentDate))??"NA".'</td>';
            $html.='<td>'.$item->PaymentAmount??"NA".'</td>';
            $html.='<td>'.$item->payment_type??"NA".'</td>';
            $html.='<td>'.$item->Description??"NA".'</td>';
            $html.='</tr>';
        }
        $html.='</tbody>';
        return Response()->json(array('remaining'=>$remaining->RemainingBalance,'total'=>$remaining->grandTotal,'table'=>$html));
    }

    public function SaveTaxInvoiceDetails(Request $request)
    {
        DB::transaction(function () use($request)
        {
            $user_id = session('user_id');
            $company_id = session('company_id');

            $invoice = new TaxInvoicePayments();
            $invoice->tax_invoice_id = $request->tax_invoice_id;
            $invoice->user_id = $user_id;
            $invoice->company_id = $company_id;
            $invoice->PaymentDate = $request->PaymentDate;
            $invoice->PaymentAmount = $request->PaymentAmount;
            $invoice->payment_type = $request->payment_type;
            $invoice->Description = $request->Description;
            $invoice->save();

            $parent = TaxInvoice::find($request->tax_invoice_id);
            $parent->update([
                'RemainingBalance' => $parent->RemainingBalance-$request->PaymentAmount,
            ]);

            echo json_encode(true);
        });
    }

    public function PrintTaxInvoice($id)
    {
        $pdf = new PDF();
        $pdf::setPrintHeader(false);
        $pdf::setPrintFooter(false);
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf::AddPage('', 'A4');
        $pdf::SetFont('helvetica', '', 9);
        $pdf::SetFillColor(255,255,0);

        $invoice=TaxInvoice::with('tax_invoice_details','tax_invoice_details.product','tax_invoice_details.unit','project','customer')->where('id',$id)->first();
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
        $str='<b>'.$invoice->InvoiceNumber.'</b>';

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
        $html='<u>TAX INVOICE</u>';
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
                <th align="center" width="30">S/N</th>
                <th align="center" width="70">Item</th>
                <th align="center" width="205">Description</th>
                <th align="center" width="50">Unit</th>
                <th align="center" width="50">Qty</th>
                <th align="center" width="60">Rate</th>
                <th align="center" width="70">Total</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        $i=0;
        foreach ($invoice->tax_invoice_details as $row)
        {
            $html .='<tr>
                <td align="center" width="30">'.++$i.'</td>
                <td align="center" width="70">'.$row->product->Name.'</td>
                <td align="left" width="205">'.$row->Description.'</td>
                <td align="center" width="50">'.($row->unit->Name).'</td>
                <td align="right" width="50">'.((number_format($row->Quantity,2,'.',','))).'</td>
                <td align="right" width="60">'.((number_format($row->Price,2,'.',','))).'</td>
                <td align="right" width="70">'.((number_format($row->rowTotal,2,'.',','))).'</td>
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
        $name='INVOICE_'.$time;
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$name.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }

    public function GetTaxInvoiceReport()
    {
        $companies= Company::get();
        return view('admin.tax_invoice.tax_invoice_vat_report',compact('companies'));
    }

    public function PrintTaxInvoiceReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!=''  && $request->filter=='all' && $request->company=='all')
        {
            $invoice=TaxInvoice::with(['customer'])->whereBetween('FromDate', [$request->fromDate, $request->toDate])->orderBy('InvoiceNumber')->orderBy('FromDate')->get();
        }
        else if ($request->fromDate!='' && $request->toDate!=''  && $request->filter!='all' && $request->company!='all')
        {
            if($request->filter=='with')
            {
                $invoice=TaxInvoice::with(['customer'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('InvoiceNumber')->get();
            }
            elseif($request->filter=='without')
            {
                $invoice=TaxInvoice::with(['customer'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('InvoiceNumber')->get();
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->company=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $invoice=TaxInvoice::with(['customer'])->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('InvoiceNumber')->get();
            }
            elseif($request->filter=='without')
            {
                $invoice=TaxInvoice::with(['customer'])->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('InvoiceNumber')->get();
            }
            else
            {
                $invoice=TaxInvoice::with(['customer'])->whereBetween('FromDate', [$request->fromDate, $request->toDate])->orderBy('InvoiceNumber')->get();
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->company!='all')
        {
            if($request->filter=='with')
            {
                $invoice=TaxInvoice::with(['customer'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('InvoiceNumber')->get();
            }
            elseif($request->filter=='without')
            {
                $invoice=TaxInvoice::with(['customer'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('InvoiceNumber')->get();
            }
            else
            {
                $invoice=TaxInvoice::with(['customer'])->where('company_id',$request->company)->whereBetween('FromDate', [$request->fromDate, $request->toDate])->orderBy('InvoiceNumber')->get();
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
            $title='Invoice Report - From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y', strtotime($request->toDate));
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
                <th align="center" width="225">Customer</th>
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
                <td align="left" width="60">'.($row[$i]['InvoiceNumber']).'</td>
                <td align="left" width="225">'.($row[$i]['customer']['Name']).'</td>
                <td align="left" width="70">'.($row[$i]['customer']['TRNNumber']).'</td>
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
