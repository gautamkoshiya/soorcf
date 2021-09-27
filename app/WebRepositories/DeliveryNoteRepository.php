<?php


namespace App\WebRepositories;


use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Product;
use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use App\WebRepositories\Interfaces\IDeliveryNoteRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use PDF;

class DeliveryNoteRepository implements IDeliveryNoteRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(DeliveryNote::with('customer','project','product','unit')->where('company_id',session('company_id'))->where('isActive',1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '';
                    $button.='<a href="'.route('delivery_notes.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button.='&nbsp;&nbsp;';
                    //$button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    $button.='&nbsp;<a href="'.url('deleteDeliveryNote', $data->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    $button.='&nbsp;&nbsp;';
                    $button.='<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></button>';
                    return $button;
                })
                ->addColumn('createdDate', function($data) {
                    return date('d-m-Y', strtotime($data->createdDate)) ?? "No date";
                })
                ->addColumn('customer', function($data) {
                    return $data->customer->Name ?? "No Name";
                })
                ->addColumn('project', function($data) {
                    return $data->project->Name ?? "No Name";
                })
                ->addColumn('product', function($data) {
                    return $data->product->Name ?? "No Name";
                })
                ->addColumn('unit', function($data) {
                    return $data->unit->Name ?? "No Name";
                })
                ->rawColumns(
                    [
                        'action',
                        'customer',
                        'project',
                        'createdDate',
                        'product',
                        'unit',
                    ])
                ->make(true);
        }
        return view('admin.delivery_note.index');
    }

    public function all_delivery_note(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = DeliveryNote::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select dn.*,c.Name as customer_name,p.Name as project_name,pd.Name as product_name,u.Name as unit_name from delivery_notes as dn left join customers as c on c.id = dn.customer_id left join projects as p on p.id = dn.project_id left join products as pd on pd.id = dn.product_id left join units as u on u.id = dn.unit_id  where dn.company_id = '.session('company_id').' and dn.isActive = 1 and dn.deleted_at is null order by dn.id desc limit '.$limit.' offset '.$start ;
            $delivery_note = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select dn.*,c.Name as customer_name,p.Name as project_name,pd.Name as product_name,u.Name as unit_name from delivery_notes as dn left join customers as c on c.id = dn.customer_id left join projects as p on p.id = dn.project_id left join products as pd on pd.id = dn.product_id left join units as u on u.id = dn.unit_id  where dn.company_id = '.session('company_id').' and dn.isActive = 1 and dn.deleted_at is null and dn.DoNumber LIKE "%'.$search.'%" order by dn.id desc limit '.$limit.' offset '.$start ;
            $delivery_note = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,dn.*,c.Name as customer_name,p.Name as project_name,pd.Name as product_name,u.Name as unit_name from delivery_notes as dn left join customers as c on c.id = dn.customer_id left join projects as p on p.id = dn.project_id left join products as pd on pd.id = dn.product_id left join units as u on u.id = dn.unit_id  where dn.company_id = '.session('company_id').' and dn.isActive = 1 and dn.deleted_at is null and dn.DoNumber LIKE "%'.$search.'%" order by dn.id desc limit '.$limit.' offset '.$start ;
            $sales_count = DB::select(DB::raw($sql_count));
            if(!empty($sales_count))
            {
                $totalFiltered = $sales_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($delivery_note))
        {
            foreach ($delivery_note as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['DoNumber'] = $single->DoNumber ?? "N.A.";
                $nestedData['createdDate'] = date('d-m-Y', strtotime($single->createdDate));
                $nestedData['OrderReference'] = $single->OrderReference ?? "N.A.";
                $nestedData['customer'] = $single->customer_name ?? "N.A.";
                $nestedData['project'] = $single->project_name ?? "N.A.";
                $nestedData['product'] = $single->product_name ?? "N.A.";
                $nestedData['unit'] = $single->unit_name ?? "N.A.";
                $nestedData['Quantity'] = $single->Quantity ?? 0.00;
                $button = '';
                $button.='<a href="'.route('delivery_notes.edit', $single->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                $button.='&nbsp;&nbsp;';
                $button.='&nbsp;<a href="'.url('deleteDeliveryNote', $single->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                $button.='&nbsp;&nbsp;';
                $button.='<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></button>';
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
        $DoNumber = $this->InvoiceNumber();
        $customers = Customer::where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
        $projects = Project::where('isActive',1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        return view('admin.delivery_note.create',compact('DoNumber','customers','projects','products','units'));
    }

    public function InvoiceNumber()
    {
        $max_id = DeliveryNote::max('id');
        if($max_id)
        {
            $max_id = DeliveryNote::where('id',$max_id)->first();
            $last=explode('#',$max_id->DoNumber);
            if(isset($last[1]))
            {
                $newInvoiceID = 'DO#'.str_pad(($last[1] + 1), 4, '0', STR_PAD_LEFT);
            }
            return $newInvoiceID;
        }
        else
        {
            $newInvoiceID = 'DO#'.str_pad((0 + 1), 4, '0', STR_PAD_LEFT);
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
                $delivery_note = new DeliveryNote();
                $delivery_note->DoNumber = $request->Data['DoNumber'];
                $delivery_note->user_id = $user_id;
                $delivery_note->company_id = $company_id;
                $delivery_note->project_id = $request->Data['project_id'];
                $delivery_note->customer_id = $request->Data['customer_id'];
                $delivery_note->product_id = $request->Data['product_id'];
                $delivery_note->unit_id = $request->Data['unit_id'];
                $delivery_note->Quantity = $request->Data['Quantity'];
                $delivery_note->OrderReference = $request->Data['OrderReference'];
                $delivery_note->Description = $request->Data['Description'];
                $delivery_note->createdDate = $request->Data['createdDate'];
                $delivery_note->save();
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
                $delivery_note = DeliveryNote::find($Id);
                $user_id = session('user_id');

                $delivery_note->update([
                    'DoNumber' => $request->Data['DoNumber'],
                    'user_id' => $user_id,
                    'project_id' => $request->Data['project_id'],
                    'customer_id' => $request->Data['customer_id'],
                    'product_id' => $request->Data['product_id'],
                    'unit_id' => $request->Data['unit_id'],
                    'Quantity' => $request->Data['Quantity'],
                    'OrderReference' => $request->Data['OrderReference'],
                    'Description' => $request->Data['Description'],
                    'createdDate' => $request->Data['createdDate'],
                ]);
            }
        });
        return Response()->json(true);
    }

    public function edit($Id)
    {
        $delivery_note = DeliveryNote::with('customer','project','product','unit')->where('id',$Id)->first();
        $customers = Customer::where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
        $projects = Project::where('isActive',1)->orderBy('id', 'desc')->get();
        $products = Product::all();
        $units = Unit::all();
        return view('admin.delivery_note.edit',compact('delivery_note','customers','projects','products','units'));
    }

    public function delete($Id)
    {
        $delivery_note = DeliveryNote::findOrFail($Id);
        $user_id = session('user_id');
        if($delivery_note)
        {
            DB::transaction(function () use($delivery_note,$user_id)
            {
                DeliveryNote::where('id',$delivery_note->id)->update(['user_id' => $user_id,]);
                DeliveryNote::where('id',$delivery_note->id)->delete();
            });
            return redirect()->route('delivery_notes.index');
        }
        else
        {
            return redirect()->route('delivery_notes.index');
        }
    }

    public function PrintDeliveryNote($id)
    {
        $pdf = new PDF();
        $pdf::setPrintHeader(false);
        $pdf::setPrintFooter(false);
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf::AddPage('', 'A4');
        $pdf::SetFont('helvetica', '', 9);
        $pdf::SetFillColor(255,255,0);

        $delivery_note=DeliveryNote::with('customer','project','product','unit')->where('id',$id)->first();
        $user_name=User::select('Name')->where('id',$delivery_note->user_id)->first();
        //echo "<pre>";print_r($quotation);die;

        $company_title=$delivery_note->project->Name;
        $company_email=$delivery_note->project->Email;
        $company_address=$delivery_note->project->Address;
        $company_mobile=$delivery_note->project->Contact;
        $company_fax=$delivery_note->project->FAX;
        $company_trn=$delivery_note->project->TRN;

        $base=URL::to('/storage/app/public/project/');
        $logo_url=$base.'/'.$delivery_note->project->logo;

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
            <td width="115" align="right" style="font-size: large;"></td>
        </tr>';
        $html.='<tr>
            <td width="270" style="font-size: large;"> FAX : '.$company_fax.'</td>
            <td width="115" align="right" style="font-size: large;"></td>
        </tr>';
        $html.='<tr>
            <td width="270" style="font-size: large;"> TRN : '.$company_trn.'</td>
            <td width="115" align="right" style="font-size: large;"></td>
        </tr>';
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');
        $pdf::Ln(2);

        $pdf::SetFont('helvetica', '', 11);
        $html = '<table cellpadding="1">';
        $html .= '<tr><td width="535" align="center" colspan="2" style="border: 0.5px solid black;font-size: xx-large;">DELIVERY NOTE</td></tr>';
        $html .= '<tr><td width="385" align="left" style="font-weight: bold;border-left: 0.5px solid black;"> Customer :</td><td width="150" align="left" colspan="2" style="font-weight: bold;border-right: 0.5px solid black;border-left: 0.5px solid black;"> Date :</td></tr>';
        $html .= '<tr><td width="385" align="left" style="border-left: 0.5px solid black;"> '.$delivery_note->customer->Name.'</td><td width="150" align="left" colspan="2" style="border-right: 0.5px solid black;border-left: 0.5px solid black;border-bottom: 0.5px solid black;"> '.date('jS F Y', strtotime($delivery_note->createdDate)).'</td></tr>';
        $html .= '<tr><td width="385" align="left" style="border-left: 0.5px solid black;"> '.$delivery_note->customer->Address.'</td><td width="150" align="left" colspan="2" style="font-weight: bold;border-right: 0.5px solid black;border-left: 0.5px solid black;" > DO Number :</td></tr>';
        $html .= '<tr><td width="385" align="left" style="border-left: 0.5px solid black;"> Tel :'.$delivery_note->customer->Mobile.' | Fax :'.$delivery_note->customer->Phone.'</td><td width="150" align="left" colspan="2" style="border-right: 0.5px solid black;border-left: 0.5px solid black;border-bottom: 0.5px solid black;"> '.$delivery_note->DoNumber.'</td></tr>';
        $html .= '<tr><td width="385" align="left" style="border-left: 0.5px solid black;"> <a>Email :'.$delivery_note->customer->Email.'</a></td><td width="150" align="left" colspan="2" style="font-weight: bold;border-right: 0.5px solid black;border-left: 0.5px solid black;"> Order Reference :</td></tr>';
        $html .= '<tr><td width="385" align="left" style="border-left: 0.5px solid black;border-bottom: 0.5px solid black;"></td><td width="150" align="left" colspan="2" style="border-right: 0.5px solid black;border-left: 0.5px solid black;border-bottom: 0.5px solid black;"> '.$delivery_note->OrderReference.'</td></tr>';
        $html .= '</table>';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
        $pdf::Ln(6);

        $html='<table cellpadding="1" border="0.5">';
        $html.='<tr><td width="435">DESCRIPTION</td><td width="100">Quantity -'.$delivery_note->unit->Name.'</td></tr>';
        $html.='<tr><td width="435" align="left">'.$delivery_note->Description.'</td><td width="100">'.$delivery_note->Quantity.'</td></tr>';
        $html .= '</table>';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
        $pdf::Ln(4);

        $html='<table>';
        $html.='<tr><td width="435" align="left"><u>Received the above materials in good order</u></td><td></td></tr>';
        $html .= '</table>';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
        $pdf::Ln(4);

        $html='<table>';
        $html.='<tr><td width="435" align="left">Receiver\'s Name:</td><td></td></tr>';
        $html .= '</table>';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
        $pdf::Ln(4);

        $html='<table>';
        $html.='<tr><td width="435" align="left">Mobile No: </td><td></td></tr>';
        $html .= '</table>';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
        $pdf::Ln(4);

        $html='<table>';
        $html.='<tr><td width="435" align="left">Receivers Signature & Seal:</td><td></td></tr>';
        $html .= '</table>';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        //$pdf::SetFont('helvetica', 'B', 12);
        /*$html = '<table border="1" cellpadding="2">
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
        foreach ($invoice->tax_invoice_details as $row)
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
        }*/
        /*$result = new amountToWords();
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
        $pdf::writeHTML($html, true, false, false, false, '');*/

        /*$pdf::SetFont('times', 'B', 9);
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
        $pdf::Ln(5);*/

        $pdf::lastPage();

        $time=time();
        $name='DN_'.$time;
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$name.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }
}
