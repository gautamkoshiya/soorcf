<?php

namespace App\WebRepositories;

use App\Models\FileManager;
use App\Models\ReportFileType;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IFileManagerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileManagerRepository implements IFileManagerRepositoryInterface
{

    public function index()
    {
        return view('admin.file_manager.index');
    }

    public function all_files(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = FileManager::where('company_id',session('company_id'))->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select fm.*,type.Name from file_managers as fm left join report_file_types type on type.id=fm.report_type_id  where fm.company_id = '.session('company_id').' and fm.deleted_at is null  order by fm.id desc limit '.$limit.' offset '.$start ;

            $files = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select fm.*,type.Name from file_managers as fm left join report_file_types type on type.id=fm.report_type_id  where fm.company_id = '.session('company_id').' and fm.deleted_at is null and fm.reportDate LIKE "%'.$search.'%" or fm.FileCode LIKE "%'.$search.'%" or fm.Description LIKE "%'.$search.'%" order by fm.id desc limit '.$limit.' offset '.$start ;
            $files = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,fm.*,type.Name from file_managers as fm left join report_file_types type on type.id=fm.report_type_id  where fm.company_id = '.session('company_id').' and fm.deleted_at is null and fm.reportDate LIKE "%'.$search.'%" or fm.FileCode LIKE "%'.$search.'%" or fm.Description LIKE "%'.$search.'%" order by fm.id desc limit '.$limit.' offset '.$start ;
            $count = DB::select(DB::raw($sql_count));
            if(!empty($count))
            {
                $totalFiltered = $count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($files))
        {
            foreach ($files as $single)
            {
                $file_type=explode('.',$single->supplierNote);
                $nestedData['id'] = $single->id;
                $nestedData['FileCode'] = $single->FileCode ?? "N.A";
                $nestedData['report_type'] = $single->Name ?? "N.A";
//                $nestedData['totalPadSale'] = $single->totalPadSale ?? "N.A";
//                $nestedData['totalMeterSale'] = $single->totalMeterSale ?? "N.A";
                $nestedData['Description'] = $single->Description ?? "N.A";
                $nestedData['reportDate'] = date('d-m-Y', strtotime($single->reportDate));
                $nestedData['file_type'] = isset($file_type[1]) ? $file_type[1]:'N.A.';
                $button='';
                $button.='<a href="'.url('/storage/app/public/file_manager/').'/'.$single->supplierNote.'" target="_blank" class="btn btn-primary"><i style="font-size: 20px" class="fa fa-eye"></i></a>';
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
        $max_id = FileManager::max('id');
        if($max_id)
        {
            $max_id = FileManager::where('id',$max_id)->first();
            $last=explode('#',$max_id->FileCode);
            if(isset($last[1]))
            {
                $newFileCode = 'File#'.str_pad(($last[1] + 1), 5, '0', STR_PAD_LEFT);
            }
        }
        else
        {
            $newFileCode = 'File#'.str_pad((0 + 1), 5, '0', STR_PAD_LEFT);
        }
        $report_type = ReportFileType::get();
        return view('admin.file_manager.create',compact('report_type','newFileCode'));
    }

    public function store(Request $request)
    {
        //echo "<pre>";print_r($request->all());die;
        if ($request->hasfile('report_file'))
        {

//            foreach($request->file('report_file') as $document)
//            {
                $document=$request->file('report_file');
                $extension = $document->getClientOriginalExtension();
                $filename=uniqid('file_manager_').'.'.$extension;
                $document->storeAs('file_manager/',$filename,'public');

                $file_upload = new FileManager();
                $file_upload->FileCode = $request->FileCode;
                $file_upload->Description = $request->Description;
                $file_upload->reportDate = $request->reportDate;
                $file_upload->report_type_id = $request->report_type_id;
                $file_upload->supplierNote = $filename;
                $file_upload->user_id = session('user_id');
                $file_upload->company_id = session('company_id');
                $file_upload->save();
//            }
            return redirect()->route('file_managers.index')->with('message','Record Saved Successfully');
        }
        else
        {
            return redirect()->route('file_managers.create')->with('message','File Not Found !!!');
        }
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
        // TODO: Implement edit() method.
    }

    public function file_manager_delete_post(Request $request)
    {
        $file = FileManager::find($request->row_id);
        if($file)
        {
            DB::transaction(function () use($request)
            {
                $user_id = session('user_id');
                $company_id = session('company_id');

                $file=FileManager::where('id',$request->row_id)->first();

                $file->update(['user_id' => $user_id,]);
                $file->delete();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'file_managers';
                $update_note->RelationId = $request->row_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            });
            //return redirect()->route('purchases.index');
            return Response()->json(true);
        }
        else
        {
            echo "Not allowed";die;
        }
    }

    public function trash_files()
    {
        if(request()->ajax())
        {
            return datatables()->of(FileManager::onlyTrashed()->with('user','report_type')->where('company_id',session('company_id'))->latest()->get())
            ->addColumn('action', function ($data) {
                $button='';
                $button.='<a href="'.url('/storage/app/public/file_manager/').'/'.$data->supplierNote.'" target="_blank" class="btn btn-primary"><i style="font-size: 20px" class="fa fa-eye"></i></a>';
                return $button;
            })
            ->addColumn('user', function($data) {
                return $data->user->name ?? "N.A.";
            })
            ->addColumn('file_type', function($data) {
                $file_type=explode('.',$data->supplierNote);
                $file_type=isset($file_type[1]) ? $file_type[1]:'N.A.';
                return $file_type;
            })
            ->addColumn('report_type', function($data) {
                return $data->report_type->Name ?? "N.A.";
            })
            ->addColumn('deleted_at', function($data) {
                return $data->deleted_at->diffForHumans();
            })
            ->rawColumns(
                [
                    'action',
                ])
            ->make(true);
        }
        return view('admin.file_manager.trashed');
    }
}
