<?php

namespace App\WebRepositories;

use App\Http\Requests\MeterReaderRequest;
use App\Models\FileUpload;
use App\Models\MeterReader;
use App\Models\MeterReading;
use App\Models\MeterReadingDetail;
use App\Models\Sale;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IMeterReaderRepositoryInterface;
use App\WebRepositories\Interfaces\IMeterReadingRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class MeterReadingRepository implements IMeterReadingRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(MeterReading::with('meter_reading_details')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('readingDate', function($data) {
                    return date('d-m-Y', strtotime($data->readingDate)) ?? "No date";
                })
                ->addColumn('action', function ($data) {
                    $button = '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                    //$button .= '&nbsp;<button class="btn btn-danger" onclick="cancel_meter_reading(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '&nbsp;&nbsp;';
                    $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    return $button;
                })
                ->rawColumns(
                    [
                        'action',
                    ])
                ->make(true);
        }
        return view('admin.meterReading.index');
    }

    public function all_meter(Request $request)
    {
        //echo "<pre>";print_r($request->all());die;
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = MeterReading::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select m.* from meter_readings as m  where m.company_id = '.session('company_id').' and m.isActive = 1 and m.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $meter = DB::select( DB::raw($sql));
        }
        else {
            $search = $request->input('search.value');

            $sql = 'select m.* from meter_readings as m  where m.company_id = '.session('company_id').' and m.isActive = 1 and m.deleted_at is null and m.readingDate LIKE "%'.$search.'%" or m.startPad LIKE "%'.$search.'%" or m.endPad LIKE "%'.$search.'%"  order by id desc limit '.$limit.' offset '.$start ;
            $meter = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,m.* from meter_readings as m  where m.company_id = '.session('company_id').' and m.isActive = 1 and m.deleted_at is null and m.readingDate LIKE "%'.$search.'%" or m.startPad LIKE "%'.$search.'%" or m.endPad LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $count = DB::select(DB::raw($sql_count));
            if(!empty($count))
            {
                $totalFiltered = $count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($meter))
        {
            foreach ($meter as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['readingDate'] = date('d-m-Y', strtotime($single->readingDate));
                $nestedData['startPad'] = $single->startPad ?? "N.A";
                $nestedData['endPad'] = $single->endPad ?? "N.A";
                $nestedData['totalPadSale'] = $single->totalPadSale ?? "N.A";
                $nestedData['totalMeterSale'] = $single->totalMeterSale ?? "N.A";
                $nestedData['saleDifference'] = $single->saleDifference ?? "N.A";

                $button='';
                $button.='<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                $button.='&nbsp;';
                $button.='<a href="#" data-id="'.$single->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
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
        $salesData = Sale::with('sale_details')->where('SaleDate', date('Y-m-d'))->get();
        $total = 0;
        if ($salesData->first() != null)
        {
            foreach ($salesData as $data){
                $total += $data->sale_details[0]->Quantity;
             }
            $salesByDate['firstPad'] = $salesData->first()->sale_details->first()->PadNumber;
            $salesByDate['lastPad'] = $salesData->last()->sale_details->last()->PadNumber;
        }
        else
        {
            $salesByDate['sale_details'] = 0;
            $salesByDate['firstPad'] = 0;
            $salesByDate['lastPad'] = 0;
        }
        $meter_readers = MeterReader::where('company_id','=',session('company_id'))->get();
        return view('admin.meterReading.create',compact('salesByDate','meter_readers','total'));
    }

    public function store(Request $request)
    {
        $response=false;
        DB::transaction(function () use($request,&$response){
            $request_refresh=json_decode($request->insert);
            $arr = (array)$request_refresh;
            if(!empty($arr))
            {
                $user_id = session('user_id');
                $company_id = session('company_id');
                $reading = new MeterReading();
                $reading->readingDate = $request_refresh->meterReadingDate;
                $reading->startPad = $request_refresh->startPad;
                $reading->endPad = $request_refresh->endPad;
                $reading->totalMeterSale = $request_refresh->totalSale;
                $reading->totalPadSale = $request_refresh->totalPad;
                $reading->saleDifference = $request_refresh->balance;
                $reading->user_id = $user_id;
                $reading->company_id = $company_id;
                $reading->save();
                $reading = $reading->id;

                foreach($request_refresh->orders as $detail)
                {
                    $data =  MeterReadingDetail::create([
                        "meter_reader_id" => $detail->meter_id,
                        "startReading" => $detail->startReading,
                        "endReading" => $detail->endReading,
                        "netReading" => $detail->netReading,
                        "Purchases" => $detail->purchases,
                        "Sales" => $detail->sales,
                        "Description" => $detail->Description,
                        "meter_reading_id" => $reading,
                        "user_id" => $user_id,
                        "company_id" => $company_id,
                    ]);
                }

                // image upload section
                if($request->TotalFiles > 0)
                {
                    for ($x = 0; $x < $request->TotalFiles; $x++)
                    {
                        if ($request->hasFile('files'.$x))
                        {
                            $file = $request->file('files'.$x);
                            $extension = $file->getClientOriginalExtension();
                            $filename=uniqid('meter_').'.'.$extension;
                            $request->file('files'.$x)->storeAs('meter_images', $filename,'public');

                            FileUpload::create([
                                "Title" => $filename,
                                "RelationTable" => 'meter_readings',
                                "RelationId" => $reading,
                                "user_id" => $user_id,
                                "company_id" => $company_id,
                            ]);
                        }
                    }
                }
                // image upload section

                if ($data)
                {
                    $response=true;
                }
            }
            else
            {
                $response=false;
            }
        });
        return Response()->json($response);
    }

    public function update(Request $request, $Id)
    {
        $AllRequestCount = collect($request->Data)->count();
        if($AllRequestCount > 0)
        {
            $meterd = MeterReading::find($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');
            $meterd->update(
                [
                    'readingDate' => $request->Data['meterReadingDate'],
                    'startPad' => $request->Data['startPad'],
                    'endPad' => $request->Data['endPad'],
                    'totalMeterSale' => $request->Data['totalSale'],
                    'totalPadSale' => $request->Data['totalPad'],
                    'saleDifference' => $request->Data['balance'],
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);

            $update_note = new UpdateNote();
            $update_note->RelationTable = 'meter_readings';
            $update_note->RelationId = $Id;
            $update_note->Description = $request->Data['UpdateDescription'];
            $update_note->user_id = $user_id;
            $update_note->company_id = $company_id;
            $update_note->save();

            $d = MeterReadingDetail::where('meter_reading_id', array($Id))->delete();
            $slct = MeterReadingDetail::where('meter_reading_id', $Id)->get();
            foreach ($request->Data['orders'] as $detail)
            {
                $Details = MeterReadingDetail::create([
                    //"Id" => $detail['Id'],
                    "meter_reader_id"        => $detail['meter_id'],
                    "startReading"        => $detail['startReading'],
                    "endReading"        => $detail['endReading'],
                    "netReading"        => $detail['netReading'],
                    "Purchases"        => $detail['purchases'],
                    "Sales"        => $detail['sales'],
                    "Description"        => $detail['Description'],
                    "meter_reading_id"        => $Id,
                    "user_id" => $user_id,
                    "company_id" => $company_id,
                ]);
            }
            $ss = MeterReadingDetail::where('meter_reading_id', array($Details['meter_reading_id']))->get();
            return Response()->json($ss);
        }
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'meter_readings'])->get();
        $meter_readers = MeterReader::all();
        $meter_details = MeterReadingDetail::withTrashed()->with('meter_reading','user','meter_reader')->where('meter_reading_id', $Id)->get();
        return view('admin.meterReading.edit',compact('meter_details','meter_readers','update_notes'));
    }

    public function delete($Id)
    {
        $meter_reading = MeterReading::findOrFail($Id);
        if($meter_reading)
        {
            DB::transaction(function () use($Id)
            {
                $company_id = session('company_id');
                $user_id = session('user_id');

                MeterReadingDetail::where('meter_reading_id',$Id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                MeterReadingDetail::where('meter_reading_id',$Id)->where('company_id', '=', $company_id)->delete();

                MeterReading::where('id',$Id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                MeterReading::where('id',$Id)->where('company_id', '=', $company_id)->delete();
            });
        }
        return Response()->json(true);
    }

    public function meter_reading_delete_post(Request $request)
    {
        $meter_reading = MeterReading::findOrFail($request->row_id);
        if($meter_reading)
        {
            DB::transaction(function () use($request)
            {
                $company_id = session('company_id');
                $user_id = session('user_id');

                MeterReadingDetail::where('meter_reading_id',$request->row_id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                MeterReadingDetail::where('meter_reading_id',$request->row_id)->where('company_id', '=', $company_id)->delete();

                MeterReading::where('id',$request->row_id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,]);
                MeterReading::where('id',$request->row_id)->where('company_id', '=', $company_id)->delete();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'meter_readings';
                $update_note->RelationId = $request->row_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            });
        }
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

    public function getMeterReadingDetail($Id)
    {
        $base=URL::to('/storage/app/public/meter_images/');
        $meter_reading=MeterReading::with(['meter_images','meter_reading_details','meter_reading_details.meter_reader','user'])->where('id',$Id)->first();
        //echo "<pre>";print_r($meter_reading);die;
        $html='<div class="row"><div class="col-md-12"><label>Reading Date : '.date('d-M-Y',strtotime($meter_reading->readingDate)).'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Start Pad : '.$meter_reading->startPad.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>End Pad : '.$meter_reading->endPad.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Total Pad Sales : '.$meter_reading->totalPadSale.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Total Meter Sales : '.$meter_reading->totalMeterSale.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Diffrance : '.$meter_reading->saleDifference.'</label></div></div>';
        $html.='<div class="row text-right"><div class="col-md-12"><label>(created by : '.$meter_reading->user->name.'-'.$meter_reading->created_at.')</label></div></div>';
        $html.='<table class="table table-sm"><thead><th>SR</th><th>Meter</th><th>StartReading</th><th>EndReading</th><th>NetReading</th><th>Purchase</th><th>Sales</th><th>Description</th></thead><tbody>';
        $i=0;
        foreach ($meter_reading->meter_reading_details as $item)
        {
            $html.='<tr>';
            $html.='<td>'.++$i.'</td>';
            $html.='<td>'.$item->meter_reader->Name??"NA".'</td>';
            $html.='<td>'.$item->startReading??"NA".'</td>';
            $html.='<td>'.$item->endReading??"NA".'</td>';
            $html.='<td>'.$item->netReading??"NA".'</td>';
            $html.='<td>'.$item->Purchases??"NA".'</td>';
            $html.='<td>'.$item->Sales??"NA".'</td>';
            $html.='<td>'.$item->Description??"NA".'</td>';
            $html.='</tr>';
        }
        $html.='</tbody></table>';
        foreach ($meter_reading->meter_images as $item)
        {
            $this_image_url=$base.'/'.$item->Title;
            $html.='<img style="border:1px solid black;" src="'.$this_image_url.'" class="img-fluid" alt="not able to load image">';
            unset($this_image_url);
        }
        return Response()->json($html);
    }
}
