<?php


namespace App\WebRepositories;


use App\MISC\amountToWords;
use App\Models\Customer;
use App\Models\lpo;
use App\Models\lpo_detail;
use App\Models\Product;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\Unit;
use App\WebRepositories\Interfaces\ILpoRepositoryInterface;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use PDF;

class LpoRepository implements ILpoRepositoryInterface
{
    public function index()
    {
        if (request()->ajax()) {
            return datatables()->of(lpo::with('supplier', 'project')->where('company_id', session('company_id'))->where('isActive', 1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '';
                    $button .= '<a href="' . route('lpos.edit', $data->id) . '"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;<a href="' . url('deleteLpo', $data->id) . '" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    $button .= '&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_' . $data->id . '"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></button>';
                    return $button;
                })
                ->addColumn('FromDate', function($data) {
                    return date('d-m-Y', strtotime($data->FromDate)) ?? "No date";
                })
                ->addColumn('DueDate', function($data) {
                    return date('d-m-Y', strtotime($data->DueDate)) ?? "No date";
                })
                ->addColumn('supplier', function ($data) {
                    return $data->supplier->Name ?? "No Name";
                })
                ->addColumn('project', function ($data) {
                    return $data->project->Name ?? "No Name";
                })
                ->rawColumns(
                    [
                        'action',
                        'supplier',
                        'project',
                        'FromDate',
                        'DueDate',
                    ])
                ->make(true);
        }
        return view('admin.lpo.index');
    }

    public function all_lpo(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = lpo::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select l.*,s.Name as supplier_name,p.Name as project_name from lpos as l left join suppliers as s on s.id = l.customer_id left join projects as p on p.id = l.project_id  where l.company_id = '.session('company_id').' and l.isActive = 1 and l.deleted_at is null order by l.id desc limit '.$limit.' offset '.$start ;
            $lpo = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select l.*,s.Name as supplier_name,p.Name as project_name from lpos as l left join suppliers as s on s.id = l.customer_id left join projects as p on p.id = l.project_id  where l.company_id = '.session('company_id').' and l.isActive = 1 and l.deleted_at is null and l.LPONumber LIKE "%'.$search.'%" order by l.id desc limit '.$limit.' offset '.$start ;
            $lpo = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,l.*,s.Name as supplier_name,p.Name as project_name from lpos as l left join suppliers as s on s.id = l.customer_id left join projects as p on p.id = l.project_id  where l.company_id = '.session('company_id').' and l.isActive = 1 and l.deleted_at is null and l.LPONumber LIKE "%'.$search.'%" order by l.id desc limit '.$limit.' offset '.$start ;
            $sales_count = DB::select(DB::raw($sql_count));
            if(!empty($sales_count))
            {
                $totalFiltered = $sales_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($lpo))
        {
            foreach ($lpo as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['LPONumber'] = $single->LPONumber ?? "N.A.";
                $nestedData['FromDate'] = date('d-m-Y', strtotime($single->FromDate));
                $nestedData['DueDate'] = date('d-m-Y', strtotime($single->DueDate));
                $nestedData['supplier'] = $single->supplier_name ?? "N.A.";
                $nestedData['project'] = $single->project_name ?? "N.A.";
                $nestedData['subTotal'] = $single->subTotal ?? 0.00;
                $nestedData['totalVat'] = $single->totalVat ?? 0.00;
                $nestedData['discount'] = $single->discount ?? 0.00;
                $nestedData['grandTotal'] = $single->grandTotal ?? 0.00;
                $button = '';
                $button .= '<a href="' . route('lpos.edit', $single->id) . '"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                $button .= '&nbsp;<a href="' . url('deleteLpo', $single->id) . '" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                $button .= '&nbsp;<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_' . $single->id . '"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></button>';
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
        $LPONumber = $this->LPONumber();
        $customers = Supplier::where('company_type_id',2)->where('company_id',session('company_id'))->get();
        $projects = Project::where('isActive', 1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        return view('admin.lpo.create', compact('LPONumber', 'customers', 'projects', 'products', 'units'));
    }

    public function LPONumber()
    {
        $max_id = lpo::max('id');
        if($max_id)
        {
            $max_id = lpo::where('id',$max_id)->first();
            $last=explode('#',$max_id->LPONumber);
            if(isset($last[1]))
            {
                $newInvoiceID = 'LPO#'.str_pad(($last[1] + 1), 4, '0', STR_PAD_LEFT);
            }
            return $newInvoiceID;
        }
        else
        {
            $newInvoiceID = 'LPO#'.str_pad((0 + 1), 4, '0', STR_PAD_LEFT);
        }
        return $newInvoiceID;
    }

    public function store(Request $request)
    {
        $AllRequestCount = collect($request->Data)->count();
        DB::transaction(function () use ($AllRequestCount, $request) {
            if ($AllRequestCount > 0) {
                $user_id = session('user_id');
                $company_id = session('company_id');

                $lpo = new lpo();
                $lpo->LPONumber = $request->Data['LPONumber'];
                $lpo->user_id = $user_id;
                $lpo->company_id = $company_id;
                $lpo->project_id = $request->Data['project_id'];
                $lpo->customer_id = $request->Data['customer_id'];
                $lpo->FromDate = $request->Data['FromDate'];
                $lpo->DueDate = $request->Data['DueDate'];
                $lpo->TermsAndCondition = $request->Data['TermsAndCondition'];
                $lpo->CustomerNote = $request->Data['CustomerNote'];
                $lpo->IsNeedStampOrSignature = $request->Data['IsNeedStampOrSignature'];
                $lpo->subTotal = $request->Data['subTotal'];
                $lpo->totalVat = $request->Data['totalVat'];
                $lpo->discount = $request->Data['discount'];
                $lpo->grandTotal = $request->Data['grandTotal'];
                $lpo->save();
                $lpo = $lpo->id;

                foreach ($request->Data['orders'] as $detail) {
                    lpo_detail::create([
                        "lpo_id" => $lpo,
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
                        "RemainingQty" => $detail['Quantity'],
                        "createdDate" => date('Y-m-d'),
                    ]);
                }
            }
            $data = array('result' => true, 'message' => 'Record Inserted Successfully.');
            echo json_encode($data);
        });
    }

    public function update(Request $request, $Id)
    {
        $AllRequestCount = collect($request->Data)->count();
        DB::transaction(function () use ($AllRequestCount, $request, $Id) {
            if ($AllRequestCount > 0) {
                $quotation = lpo::find($Id);
                $user_id = session('user_id');
                $company_id = session('company_id');

                $quotation->update([
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

                lpo_detail::where('lpo_id', array($Id))->forceDelete();
                //QuotationDetail::where('quotation_id', array($Id))->delete();
                foreach ($request->Data['orders'] as $detail) {
                    lpo_detail::create([
                        "lpo_id" => $Id,
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
        $lpo = lpo::with('supplier', 'project')->where('id', $Id)->first();
        $customers = Supplier::where('company_type_id',2)->where('company_id',session('company_id'))->get();
        $projects = Project::where('isActive', 1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        $lpo_details = lpo_detail::withTrashed()->with('product', 'unit')->where('lpo_id', $Id)->get();
        return view('admin.lpo.edit', compact('lpo', 'lpo_details', 'customers', 'projects', 'products', 'units'));
    }

    public function delete($Id)
    {
        $quotation = lpo::findOrFail($Id);
        $user_id = session('user_id');
        if ($quotation) {
            DB::transaction(function () use ($quotation, $user_id) {
                lpo_detail::where('lpo_id', $quotation->id)->update(['user_id' => $user_id,]);
                lpo_detail::where('lpo_id', $quotation->id)->delete();
                lpo::where('id', $quotation->id)->update(['user_id' => $user_id,]);
                lpo::where('id', $quotation->id)->delete();
            });
            return redirect()->route('lpos.index');
        } else {
            return redirect()->route('lpos.index');
        }
    }

    public function PrintLpo($id)
    {
        $pdf = new PDF();
        $pdf::setPrintHeader(false);
        $pdf::setPrintFooter(false);
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf::AddPage('', 'A4');
        $pdf::SetFont('helvetica', '', 9);
        $pdf::SetFillColor(255, 255, 0);

        $lpo = lpo::with('lpo_details', 'lpo_details.product', 'lpo_details.unit', 'project', 'supplier')->where('id', $id)->first();
        $user_name = User::select('Name')->where('id', $lpo->user_id)->first();
        //echo "<pre>";print_r($quotation);die;

        $company_title = $lpo->project->Name;
        $company_email = $lpo->project->Email;
        $company_address = $lpo->project->Address;
        $company_mobile = $lpo->project->Contact;
        $company_fax = $lpo->project->FAX;
        $company_trn = $lpo->project->TRN;

        $from = 'Date : -';
        if ($lpo->FromDate != '') {
            $from = 'From : ' . date('d-m-Y', strtotime($lpo->FromDate));
        }
        $to = 'Due Date : -';
        if ($lpo->DueDate != '') {
            $to = 'Due Date : ' . date('d-m-Y', strtotime($lpo->DueDate));
        }
        $str = '<b>' . $lpo->LPONumber . '</b>';

        $base = URL::to('/storage/app/public/project/');
        $logo_url = $base . '/' . $lpo->project->logo;
        //$logo_url='';

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
            <td width="115" align="right" style="font-size: large;">' . $str . '</td>
        </tr>';
        $html .= '<tr>
            <td width="270" style="font-size: large;"> FAX : ' . $company_fax . '</td>
            <td width="115" align="right" style="font-size: large;">' . $from . '</td>
        </tr>';
        $html .= '<tr>
            <td width="270" style="font-size: large;"> TRN : ' . $company_trn . '</td>
            <td width="115" align="right" style="font-size: large;">' . $to . '</td>
        </tr>';
        $html .= '</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::writeHTML("<hr>", true, false, false, false, '');

        $pdf::SetFont('helvetica', 'B', 16);
        $html = '<u>LPO</u>';
        $pdf::writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'C', true);

        $pdf::Ln(2);

        //style="border-color: #0080C0;"
        $pdf::SetFont('helvetica', '', 11);
        $html = '<table cellpadding="2">';
        $html .= '<tr><td width="400" align="left" colspan="2" style="border-left: 2px solid #0080C0;">Supplier Details :</td><td width="135" align="right" style="border-right: 2px solid #0080C0;">Created By : ' . $user_name->Name . '</td></tr>';
        $html .= '<tr><td width="90" align="left" style="border-left: 2px solid #0080C0;">Name :</td><td width="445" align="left" colspan="2" style="border-right: 2px solid #0080C0;">' . $lpo->supplier->Name . '</td></tr>';
        $html .= '<tr><td width="90" align="left" style="border-left: 2px solid #0080C0;">TRN :</td><td width="445" align="left" colspan="2" style="border-right: 2px solid #0080C0;">' . $lpo->supplier->TRNNumber . '</td></tr>';
        $html .= '<tr><td width="90" align="left" style="border-left: 2px solid #0080C0;">Phone :</td><td width="445" align="left" colspan="2" style="border-right: 2px solid #0080C0;">' . $lpo->supplier->Mobile . '</td></tr>';
        $html .= '<tr><td width="90" align="left" style="border-left: 2px solid #0080C0;">Contact Person :</td><td width="445" align="left" colspan="2" style="border-right: 2px solid #0080C0;">' . $lpo->supplier->Representative . '</td></tr>';
        $html .= '<tr style="border-right: 2px solid #0080C0;"><td width="90" align="left" style="border-left: 2px solid #0080C0;">Address :</td><td width="445" align="left" colspan="2">' . $lpo->supplier->Address . '</td></tr>';
        $html .= '</table>';
        $pdf::writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'C', true);
        $pdf::Ln(6);

        $pdf::SetFont('helvetica', 'B', 12);
        $html = '<table border="1" cellpadding="2">
            <tr style="background-color: rgb(22, 90, 145); color: rgb(255,255,255);font-weight: bold;">
                <th align="center" width="45">S/N</th>
                <th align="center" width="50">Item</th>
                <th align="center" width="220">Description</th>
                <th align="center" width="50">Unit</th>
                <th align="center" width="70">Qty</th>
                <th align="center" width="30">Rate</th>
                <th align="center" width="70">Total</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        $i = 0;
        foreach ($lpo->lpo_details as $row) {
            $html .= '<tr>
                <td align="center" width="45">' . ++$i . '</td>
                <td align="center" width="50">' . $row->product->Name . '</td>
                <td align="left" width="220">' . $row->Description . '</td>
                <td align="center" width="50">' . ($row->unit->Name) . '</td>
                <td align="right" width="70">' . ((number_format($row->Quantity, 2, '.', ','))) . '</td>
                <td align="right" width="30">' . ((number_format($row->Price, 2, '.', ','))) . '</td>
                <td align="right" width="70">' . ((number_format($row->rowTotal, 2, '.', ','))) . '</td>
                </tr>';
        }
        $html .= '</table><table cellpadding="1">';
        $html .= '<tr>
                <td align="right" width="355" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;"> Total (AED) </td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($lpo->subTotal, 2, '.', ','))) . '</td>
                </tr>';
        $html .= '<tr>
                <td align="right" width="355" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">VAT (5%)</td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($lpo->totalVat, 2, '.', ','))) . '</td>
                </tr>';
        if ($lpo->discount != 0) {
            $html .= '<tr>
                <td align="right" width="355" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">Subtotal (AED)</td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format(($lpo->subTotal + $lpo->totalVat), 2, '.', ','))) . '</td>
                </tr>';
            $html .= '<tr>
                <td align="right" width="355" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">Discount (AED)</td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($lpo->discount, 2, '.', ','))) . '</td>
                </tr>';
        }
        $result = new amountToWords();
        $html .= '<tr>
                <td align="left" width="355" colspan="3"><b>' . ('Amount In Words : </b>' .$result->AmountInWords($lpo->grandTotal). ' Only') . '</td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">Net Total (AED)</td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($lpo->grandTotal, 2, '.', ','))) . '</td>
                </tr>';
        $html .= '</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $tc = $lpo->TermsAndCondition;
        $note = $lpo->CustomerNote;

        $html = '<table style="border: 0.5px dotted black;">';
        $html .= '<tr style="font-size: large;"><td width="260">  Terms and Condition :</td></tr>';
        $html .= '<tr><td width="260">' . $tc . '</td></tr>';
        $html .= '</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $html = '<table style="border: 0.5px dotted black;">';
        $html .= '<tr style="font-size: large;"><td width="260">  Customer Notes :</td></tr>';
        $html .= '<tr><td width="260">' . $note . '</td></tr>';
        $html .= '</table>';
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
        $pdf::Cell(190, 5, '', '', 0, 'L');
        $pdf::Ln(5);

        $pdf::SetFont('times', 'B', 9);
        $pdf::Cell(190, 5, '', '', 0, 'L');
        $pdf::Ln(5);
        $pdf::SetFont('times', 'B', 9);
        $pdf::Cell(190, 5, '', '', 0, 'L');
        $pdf::Ln(5);

        $pdf::SetFont('times', 'B', 10);
        $pdf::Cell(95, 5, ' Accepted By (Name & Signature) ', 'B', 0, 'C');
        $pdf::Cell(95, 5, ' Issued By Name & Signature ', 'B', 0, 'C');
        $pdf::Ln(5);

        $pdf::lastPage();

        $time = time();
        $name = 'LPO_' . $time;
        $fileLocation = storage_path() . '/app/public/report_files/';
        $fileNL = $fileLocation . '//' . $name . '.pdf';
        $pdf::Output($fileNL, 'F');
        $url = url('/') . '/storage/app/public/report_files/' . $name . '.pdf';
        $url = array('url' => $url);
        return $url;
    }

    public function lpoSupplierDetails($id)
    {
        $data = Supplier::select('Address','Mobile','Email','TRNNumber')->where('id','=',$id)->get();
        if($data->first())
        {
            $result=array('result'=>true,'data'=>$data);
            return Response()->json($result);
        }
        else
        {
            return Response()->json(false);
        }
    }
}
