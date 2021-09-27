<?php

namespace App\WebRepositories;

use App\Models\Designation;
use App\WebRepositories\Interfaces\IDesignationRepositoryInterface;
use Illuminate\Http\Request;

class DesignationRepository implements IDesignationRepositoryInterface
{
    public function index()
    {
        $designation = Designation::get();
        return view('admin.designation.index',compact('designation'));
    }

    public function create()
    {
        return view('admin.designation.create');
    }

    public function store(Request $request)
    {
        $exist=Designation::where('Name','like',$request->Name)->first();
        if(!$exist) {
            $designation = [
                'Name' => $request->Name,
                'user_id' => session('user_id'),
                'company_id' => session('company_id'),
            ];
            Designation::create($designation);
            return redirect()->route('designations.index')->with('success', 'Record Inserted Successfully');
        }
        else
        {
            return redirect()->route('designations.index')->with('success', 'Failed to Enter Department');
        }
    }

    public function update(Request $request, $Id)
    {
        $designation = Designation::find($Id);
        $user_id = session('user_id');
        $designation->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
        ]);
        return redirect()->route('designations.index')->with('update','Record Updated Successfully');
    }

    public function edit($Id)
    {
        $designation = Designation::find($Id);
        return view('admin.designation.edit',compact('designation'));
    }

    public function delete($Id)
    {
        $data = Designation::findOrFail($Id);
        $data->delete();
        return redirect()->route('designations.index')->with('delete','Record Deleted Successfully');
    }
}
