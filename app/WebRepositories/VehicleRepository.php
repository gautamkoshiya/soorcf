<?php


namespace App\WebRepositories;


use App\Http\Requests\VehicleRequest;
use App\Http\Resources\Vehicle\VehicleResource;
use App\MISC\CustomeFooter;
use App\Models\Customer;
use App\Models\UpdateNote;
use App\Models\Vehicle;
use App\WebRepositories\Interfaces\IVehicleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PDF;

class VehicleRepository implements IVehicleRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Vehicle::with(['customer'=>function($q){$q->select('id','Name');}])->select('id','registrationNumber','Description','customer_id','user_id','company_id','isActive')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('vehicles.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('vehicles.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    //$button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('status_string', function($data) {
                    if($data->isActive == true)
                    {
                        return '<span style="color: green;">Active</span>';
                    }
                    else
                    {
                        return '<span style="color: red;">InActive</span>';;
                    }
                })
                ->addColumn('isActive', function($data) {
                    if($data->isActive == true)
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="vehicle_'.$data->id.'" checked><span class="slider"></span></label>';
                        return $button;
                    }
                    else
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="vehicle_'.$data->id.'"><span class="slider"></span></label>';
                        return $button;
                    }
                })
                ->addColumn('customerName', function($data) {
                    return $data->customer->Name ?? "";
                })
                ->addColumn('Description', function($data) {
                    return $data->Description ?? "a";
                })
                ->rawColumns([
                    'action',
                    'isActive',
                    'status_string',
                    'customerName',
                    'Description',
                ])
                ->make(true);
        }
        //$vehicles = Vehicle::with('customer')->get();
        return view('admin.vehicle.index');
    }

    public function create()
    {
        $customers = Customer::select('id','Name')->where('company_id',session('company_id'))->get();
        return view('admin.vehicle.create',compact('customers'));
    }

    public function store(VehicleRequest $vehicleRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        //check if vehicle number is exist with same customer
        $vehicle = Vehicle::where('customer_id',$vehicleRequest->customer_id)->where('registrationNumber',$vehicleRequest->registrationNumber)->get();
        if($vehicle->first())
        {
            return  redirect()->route('vehicles.create')->with('exist','vehicle already exist');
        }

        $vehicle = [
            'registrationNumber' =>$vehicleRequest->registrationNumber,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'customer_id' =>$vehicleRequest->customer_id,
            'Description' =>$vehicleRequest->Description,
        ];
        Vehicle::create($vehicle);
        return redirect()->route('vehicles.index');
    }

    public function update(Request $request, $Id)
    {
        $vehicle = Vehicle::find($Id);

        $plate=Vehicle::where('registrationNumber',$request->registrationNumber)->get();
        if($plate->first())
        {
            return back()->withInput()->with('exist','Already Exist...');
        }

        $user_id = session('user_id');
        $vehicle->update([
            'registrationNumber' =>$request->registrationNumber,
            'user_id' =>$user_id,
            'customer_id' =>$request->customer_id,
            'Description' =>$request->Description,
        ]);
        return redirect()->route('vehicles.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $customers = Customer::select('id','Name')->where('company_id',session('company_id'))->get();
        $vehicle = Vehicle::select('id','registrationNumber','Description','customer_id')->with(['customer'=>function($q){$q->select('id','Name');}])->find($Id);
        return view('admin.vehicle.edit',compact('customers','vehicle'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Vehicle::findOrFail($Id);
        $data->delete();
        return redirect()->route('vehicles.index');
    }

    public function vehicle_delete_post(Request $request)
    {
        $data = Vehicle::findOrFail($request->row_id);
        $data->delete();
        //return redirect()->route('vehicles.index');
        $update_note = new UpdateNote();
        $update_note->RelationTable = 'vehicles';
        $update_note->RelationId = $request->row_id;
        $update_note->UpdateDescription = $request->deleteDescription;
        $update_note->user_id = session('user_id');
        $update_note->company_id = session('company_id');
        $update_note->save();
        return Response()->json(true);
    }

    public function CheckVehicleExist($request)
    {
        $data = Vehicle::where('registrationNumber','=',$request->registrationNumber)->where('customer_id','=',$request->customer_id)->get();
        if($data->first())
        {
            $result=array('result'=>true);
            return Response()->json(true);
        }
        else
        {
            $result=array('result'=>false);
            return Response()->json(false);
        }
    }

    public function ChangeVehicleStatus($Id)
    {
        $vehicle = Vehicle::find($Id);
        if($vehicle->isActive==1)
        {
            $vehicle->isActive=0;
        }
        else
        {
            $vehicle->isActive=1;
        }
        $vehicle->update();
        return Response()->json(true);
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function getVehicleList()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        return view('admin.vehicle.vehicle_report_by_customer',compact('customers'));
    }

    public function PrintVehicleList(Request $request)
    {
        if ($request->customer_id!='all')
        {
            $vehicles=Vehicle::with(['customer'=>function($q){$q->select('Name','id');}])->select('id','registrationNumber','customer_id','isActive')->get()->where('customer_id', '=', $request->customer_id);
        }
        else
        {
            $vehicles=Vehicle::with(['customer'=>function($q){$q->select('Name','id');}])->select('id','registrationNumber','customer_id','isActive')->where('company_id',session('company_id'))->get();
        }
        //echo "<pre>";print_r($vehicles);die;

        if(!$vehicles->isEmpty())
        {
            $row=json_decode(json_encode($vehicles), true);
            $row=array_values($row);
            //echo "<pre>";print_r($row);die;

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 12);
            $title='CUSTOMER VEHICLE REPORT';
            $time='Date : '.date('d-m-Y h:i:s A');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);


            $pdf::SetFont('helvetica', 'B', 8);
            if($row)
            {
                //for customer selection
                $customer_ids=array();
                $customer_name=array();
                foreach ($row as $item)
                {
                    $customer_ids[]=$item['customer']['id'];
                    $customer_name[]=$item['customer']['Name'];
                }
                $customer_ids=array_unique($customer_ids);
                $customer_name=array_unique($customer_name);
                $customer_ids=array_values($customer_ids);
                $customer_name=array_values($customer_name);
                //echo "<pre>";print_r($customer_name);die;
                for($i=0;$i<count($customer_ids);$i++)
                {
                    $customer_title='<u><b>'.'Customer :- '.$customer_name[$i].'</b></u>';
                    $pdf::SetFont('helvetica', 'B', 10);
                    $pdf::writeHTMLCell(0, 0, '', '', $customer_title,0, 1, 0, true, 'L', true);

                    $pdf::SetFont('helvetica', '', 8);
                    //code will come here
                    $html = '<table border="0.5" cellpadding="1">
                    <tr style="color: black;font-size: large;">
                        <th align="center" width="30">#</th>
                        <th align="center" width="100">Vehicle</th>
                        <th align="center" width="100">Status</th>
                        <th align="center" width="30">#</th>
                        <th align="center" width="100">Vehicle</th>
                        <th align="center" width="100">Status</th>
                    </tr>';
                    $counter=1;
                    for ($j=0;$j<count($row);)
                    {
                        if ($customer_ids[$i]==$row[$j]['customer']['id'])
                        {
                            $style_string='color:green;';
                            $status_string='Active';
                            if($row[$j]['isActive']==0)
                            {
                                $style_string='color:red;';
                                $status_string='InActive';
                            }
                            $html .= '<tr>
                                <td align="center" width="30">'.($counter).'</td>
                                <td align="center" width="100">'.($row[$j]['registrationNumber']).'</td>
                                <td align="center" width="100" style="'.$style_string.'">'.($status_string).'</td>';
                            $counter=$counter+1;
                            if(isset($row[$j+1]))
                            {
                                $style_string='color:green;';
                                $status_string='Active';
                                if($row[$j]['isActive']==0)
                                {
                                    $style_string='color:red;';
                                    $status_string='InActive';
                                }
                                $html .='<td align="center" width="30">'.($counter).'</td>
                                <td align="center" width="100">'.($row[$j+1]['registrationNumber']).'</td>
                                <td align="center" width="100" style="'.$style_string.'">'.($status_string).'</td>';
                                $counter=$counter+1;
                            }
                            $html.='</tr>';
                            $j=$j+2;
                        }
                    }
                    $pdf::SetFillColor(255, 0, 0);
                    $html .= '</table>';
                    //code will come here

                    $pdf::writeHTML($html, true, false, false, false, '');
                }
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
}
