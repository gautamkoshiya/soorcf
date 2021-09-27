<?php


namespace App\WebRepositories;


use App\Models\TaskFrequency;
use App\WebRepositories\Interfaces\ITaskFrequencyRepositoryInterface;
use Illuminate\Http\Request;

class TaskFrequencyRepository implements ITaskFrequencyRepositoryInterface
{
    public function index()
    {
        $frequency = TaskFrequency::get();
        return view('admin.task_frequency.index',compact('frequency'));
    }

    public function create()
    {
        return view('admin.task_frequency.create');
    }

    public function store(Request $request)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $task_frequency = [
            'Name' => strtolower(preg_replace("/\s+/", "", $request->Name)),
            'user_id' => $user_id,
            'company_id' => $company_id,
        ];
        TaskFrequency::create($task_frequency);
        return redirect()->route('task_frequencies.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        $task_frequency = TaskFrequency::find($Id);
        $user_id = session('user_id');
        $task_frequency->update([
            'Name' => strtolower(preg_replace("/\s+/", "", $request->Name)),
            'user_id' => $user_id,
        ]);
        return redirect()->route('task_frequencies.index')->with('update','Record Updated Successfully');
    }

    public function edit($Id)
    {
        $task_frequency = TaskFrequency::find($Id);
        return view('admin.task_frequency.edit',compact('task_frequency'));
    }

    public function delete(Request $request, $Id)
    {
        $data = TaskFrequency::findOrFail($Id);
        $data->delete();
        return redirect()->route('task_frequencies.index')->with('delete','Record Deleted Successfully');
    }
}
