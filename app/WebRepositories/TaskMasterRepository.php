<?php


namespace App\WebRepositories;


use App\Models\Task;
use App\Models\TaskFrequency;
use App\Models\TaskMaster;
use App\Models\UpdateNote;
use App\Models\User;
use App\WebRepositories\Interfaces\ITaskMasterRepositoryInterface;
use DateInterval;
use DatePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;

class TaskMasterRepository implements ITaskMasterRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(TaskMaster::with(['frequency'=>function($q){$q->select('id','Name');},'user'=>function($q){$q->select('id','name');},'assigned_user'=>function($q){$q->select('id','name');}])->select('id','Name','user_id','company_id','frequency_id','assigned_to','Description','StartDate','EndDate','CompletionTime')->where('user_id',session('user_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    //$button = '<a href="'.route('task_masters.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    //$button .= '&nbsp;&nbsp;';
                    $button='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    return $button;
                })
                ->addColumn('StartDate', function($data) {
                    return date('d-m-Y', strtotime($data->StartDate)) ?? "No date";
                })
                ->addColumn('EndDate', function($data) {
                    return date('d-m-Y', strtotime($data->EndDate)) ?? "No date";
                })
                ->addColumn('frequency', function($data) {
                    return $data->frequency->Name ?? "No Data";
                })
                ->addColumn('assigned_by', function($data) {
                    return $data->user->name ?? "No Data";
                })
                ->addColumn('assigned_to', function($data) {
                    return $data->assigned_user->name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'StartDate',
                    'EndDate',
                    'frequency',
                    'assigned_by',
                    'assigned_to',
                ])
                ->make(true);
        }
        return view('admin.task_master.index');
    }

    public function create()
    {
        $task_frequency = TaskFrequency::get();
        $users = User::where('company_id',session('company_id'))->where('isActive',1)->get();
        return view('admin.task_master.create',compact('task_frequency','users'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use($request) {
            $user_id = session('user_id');
            $company_id = session('company_id');

            $master_task = [
                'frequency_id' =>$request->frequency_id,
                'assigned_to' =>$request->assigned_to,
                'StartDate' =>$request->StartDate,
                'EndDate' =>$request->EndDate,
                'CompletionTime' =>$request->CompletionTime,
                'Name' =>$request->Name,
                'Description' =>$request->Description,
                'user_id' =>$user_id,
                'company_id' =>$company_id,
            ];
            $master_task = TaskMaster::create($master_task);

            //get the name of frequency and make task entry accordingly
            $frequency_name=TaskFrequency::select('Name')->where('id',$request->frequency_id)->first();
            if($frequency_name->Name==='daily')
            {
                $begin = new DateTime($request->StartDate);
                $end   = new DateTime($request->EndDate);
                $all_dates=array();
                for($i = $begin; $i <= $end; $i->modify('+1 day'))
                {
                    $all_dates[]=$i->format("Y-m-d");

                    Task::create([
                        "master_id" => $master_task->id,
                        "assigned_to" => $request->assigned_to,
                        "assigned_by" => $user_id,
                        "Date" => $i->format("Y-m-d"),
                        "CompletionTime" => $request->CompletionTime,
                        "code" => strtoupper(uniqid('TSK')),
                    ]);
                }
            }
            elseif($frequency_name->Name==='onetime')
            {
                Task::create([
                    "master_id" => $master_task->id,
                    "assigned_to" => $request->assigned_to,
                    "assigned_by" => $user_id,
                    "Date" => $request->StartDate,
                    "CompletionTime" => $request->CompletionTime,
                    "code" => strtoupper(uniqid('TSK')),
                ]);
            }
            elseif($frequency_name->Name==='weekly')
            {
                $dates=$this->getDatesFromRange($request->StartDate,$request->EndDate,'P7D');
                for($i = 0; $i < count($dates); $i++)
                {
                    Task::create([
                        "master_id" => $master_task->id,
                        "assigned_to" => $request->assigned_to,
                        "assigned_by" => $user_id,
                        "Date" => $dates[$i],
                        "CompletionTime" => $request->CompletionTime,
                        "code" => strtoupper(uniqid('TSK')),
                    ]);
                }
            }
            elseif($frequency_name->Name==='biweekly')
            {
                $dates=$this->getDatesFromRange($request->StartDate,$request->EndDate,'P15D');
                for($i = 0; $i < count($dates); $i++)
                {
                    Task::create([
                        "master_id" => $master_task->id,
                        "assigned_to" => $request->assigned_to,
                        "assigned_by" => $user_id,
                        "Date" => $dates[$i],
                        "CompletionTime" => $request->CompletionTime,
                        "code" => strtoupper(uniqid('TSK')),
                    ]);
                }
            }
            elseif($frequency_name->Name==='monthly')
            {
                $dates=$this->getDatesFromRange($request->StartDate,$request->EndDate,'P30D');
                for($i = 0; $i < count($dates); $i++)
                {
                    Task::create([
                        "master_id" => $master_task->id,
                        "assigned_to" => $request->assigned_to,
                        "assigned_by" => $user_id,
                        "Date" => $dates[$i],
                        "CompletionTime" => $request->CompletionTime,
                        "code" => strtoupper(uniqid('TSK')),
                    ]);
                }
            }
        });
        return redirect()->route('task_masters.index');
    }

    function getDatesFromRange($start, $end, $days_interval,$format = 'Y-m-d') {
        $array = array();
        $interval = new DateInterval($days_interval);

        $realEnd = new DateTime($end);
        $realEnd->add($interval);

        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
        foreach($period as $date) {
            $array[] = $date->format($format);
        }
        return $array;
    }

    public function update(Request $request, $Id)
    {
        DB::transaction(function () use($request,$Id) {
            $task_master = TaskMaster::find($Id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            // start reverse accounting //
            if($task_master)
            {
                $task_master->update([
                    'Name' =>$request->Name,
                    'user_id' =>$user_id,
                    'company_id' =>$company_id,
                    'frequency_id' =>$request->frequency_id,
                    'assigned_to' =>$request->assigned_to,
                    'Description' =>$request->Description,
                    'StartDate' =>$request->StartDate,
                    'EndDate' =>$request->EndDate,
                    'CompletionTime' =>$request->CompletionTime,
                ]);
            }
            // end reverse accounting //
        });
        return redirect()->route('task_masters.index');
    }

    public function edit($Id)
    {
        $task_frequency = TaskFrequency::get();
        $users = User::where('isActive',1)->get();
        $task_master = TaskMaster::with(['frequency'=>function($q){$q->select('id','Name');},'user'=>function($q){$q->select('id','name');},'assigned_user'=>function($q){$q->select('id','name');}])->find($Id);
        return view('admin.task_master.edit',compact('task_master','task_frequency','users'));
    }

    public function task_master_delete_post($request)
    {
        DB::transaction(function () use($request) {
            $task_master = TaskMaster::find($request->row_id);
            $user_id = session('user_id');
            $company_id = session('company_id');

            // start reverse accounting //
            if($task_master)
            {
                $update_note = new UpdateNote();
                $update_note->RelationTable = 'task_masters';
                $update_note->RelationId = $request->row_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            }
            // end reverse accounting //
            Task::where('master_id', '=', $request->row_id)->delete();

            $task_master->update(['user_id' =>$user_id,]);
            $task_master->delete();
        });
        return Response()->json(true);
    }
}
