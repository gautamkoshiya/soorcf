<?php

namespace App\WebRepositories;

use App\Models\Department;
use App\WebRepositories\Interfaces\IDepartmentRepositoryInterface;
use Illuminate\Http\Request;

class DepartmentRepository implements IDepartmentRepositoryInterface
{
    public function index()
    {
        $departments = Department::get();
        return view('admin.department.index',compact('departments'));
    }

    public function create()
    {
        return view('admin.department.create');
    }

    public function store(Request $request)
    {
        $exist=Department::where('Name','like',$request->Name)->first();
        if(!$exist) {
            $department = [
                'Name' => $request->Name,
                'user_id' => session('user_id'),
                'company_id' => session('company_id'),
            ];
            Department::create($department);
            return redirect()->route('departments.index')->with('success', 'Record Inserted Successfully');
        }
        else
        {
            return redirect()->route('departments.index')->with('success', 'Failed to Enter Department');
        }
    }

    public function update(Request $request, $Id)
    {
        $department = Department::find($Id);
        $user_id = session('user_id');
        $department->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
        ]);
        return redirect()->route('departments.index')->with('update','Record Updated Successfully');
    }

    public function edit($Id)
    {
        $department = Department::find($Id);
        return view('admin.department.edit',compact('department'));
    }

    public function delete($Id)
    {
        $data = Department::findOrFail($Id);
        $data->delete();
        return redirect()->route('departments.index')->with('delete','Record Deleted Successfully');
    }
}
