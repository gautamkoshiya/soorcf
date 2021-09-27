<?php


namespace App\WebRepositories;


use App\Models\Task;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\ITaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskRepository implements ITaskRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Task::with(['master_task'=>function($q){$q->select('id','Name','user_id','StartDate','EndDate','CompletionTime');},'master_task.user'])->where('assigned_to',session('user_id'))->where('status',0)->where('Date','<=',date('Y-m-d'))->get())
                ->addColumn('status', function ($data) {
                    if($data->status == false)
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'" checked><span class="slider"></span></label>';
                        return $button;
                    }
                    else
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'"><span class="slider"></span></label>';
                        return $button;
                    }
                })
                ->addColumn('Name', function($data) {
                    return $data->master_task->Name ?? "No date";
                })
                ->addColumn('assigned_by', function($data) {
                    return $data->master_task->user->name ?? "No Data";
                })
                ->addColumn('Date', function($data) {
                    return date('d-m-Y', strtotime($data->Date)) ?? "No date";
                })
                ->rawColumns([
                    'status',
                    'Name',
                    'assigned_by',
                    'Date',
                ])
                ->make(true);
        }
        return view('admin.task.index');
    }

    public function ChangeTaskStatus($Id)
    {
        $task = Task::find($Id);
        if($task->status==1)
        {
            $task->status=0;
        }
        else
        {
            $task->status=1;
        }
        $task->update();
        return Response()->json(true);
    }

    public function update(Request $request, $Id)
    {
        $task = Task::find($Id);
        if($task)
        {
            $task->update([
                'Date' =>$request->Date,
                'CompletionTime' =>$request->CompletionTime,
                'status' =>$request->status,
                'Note' =>$request->Note,
            ]);
        }
        return redirect()->route('review_task');
    }

    public function edit($Id)
    {
        $task = Task::find($Id);
        return view('admin.task.edit',compact('task'));
    }

    public function task_delete_post($request)
    {
        DB::transaction(function () use($request) {
            $task = Task::find($request->row_id);
            $company_id = session('company_id');
            if($task)
            {
                $update_note = new UpdateNote();
                $update_note->RelationTable = 'tasks';
                $update_note->RelationId = $request->row_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            }
            $task->delete();
        });
        //return redirect()->route('deposits.index');
        return Response()->json(true);
    }

    public function review_task()
    {
        return view('admin.task.review_task');
    }

    public function get_review_task(Request $request)
    {
        $columns = array(
            0 =>'id',
        );
        $totalData = Task::where('assigned_by',session('user_id'))->where('status',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select t.id,t.master_id,t.assigned_to,t.Date,t.CompletionTime,t.status,t.Note,t.updated_at,t.assigned_by,t.code,u.name,tm.Name from tasks as t join users as u on t.assigned_to=u.id join task_masters as tm on t.master_id=tm.id where t.assigned_by = '.session('user_id').' and t.status = 1 and t.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $task = DB::select( DB::raw($sql));
        }
        else {
            $search = $request->input('search.value');

            $sql = 'select t.id,t.master_id,t.assigned_to,t.Date,t.CompletionTime,t.status,t.Note,t.updated_at,t.assigned_by,t.code,u.name,tm.Name from tasks as t join users as u on t.assigned_to=u.id join task_masters as tm on t.master_id=tm.id where t.assigned_by = '.session('user_id').' and t.status = 1 and t.deleted_at is null and t.code LIKE "%'.$search.'%"  order by id desc limit '.$limit.' offset '.$start ;
            $task = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,t.id,t.master_id,t.assigned_to,t.Date,t.CompletionTime,t.status,t.Note,t.updated_at,t.assigned_by,t.code,u.name,tm.Name from tasks as t join users as u on t.assigned_to=u.id join task_masters as tm on t.master_id=tm.id where t.assigned_by = '.session('user_id').' and t.status = 1 and t.deleted_at is null and t.code LIKE "%'.$search.'%"  order by id desc limit '.$limit.' offset '.$start ;
            $sales_count = DB::select(DB::raw($sql_count));
            if(!empty($sales_count))
            {
                $totalFiltered = $sales_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($task))
        {
            foreach ($task as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['Name'] = $single->Name ?? "N.A.";
                $nestedData['assigned_to'] = $single->name ?? "N.A.";
                $nestedData['Date'] = date('d-m-Y', strtotime($single->Date)) ?? "No Date";
                $nestedData['CompletionTime'] = $single->CompletionTime ?? "No Time";
                $nestedData['updated_at'] = $single->updated_at ?? "N.A.";
                $nestedData['code'] = $single->code ?? "N.A.";
                $nestedData['Note'] = $single->Note ?? "N.A.";
                $nestedData['status'] = ($single->status == 0) ? 'Pending':'Completed';
                $button='';
                $button.='<a href="'.route('tasks.edit', $single->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
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
}
