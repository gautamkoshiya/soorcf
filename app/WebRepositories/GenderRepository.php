<?php

namespace App\WebRepositories;

use App\Models\Gender;
use App\WebRepositories\Interfaces\IGenderRepositoryInterface;
use Illuminate\Http\Request;

class GenderRepository implements IGenderRepositoryInterface
{
    public function index()
    {
        $gender = Gender::get();
        return view('admin.gender.index',compact('gender'));
    }

    public function create()
    {
        return view('admin.gender.create');
    }

    public function store(Request $request)
    {
        $exist=Gender::where('Name','like',$request->Name)->first();
        if(!$exist) {
            $gender = [
                'Name' => $request->Name,
                'user_id' => session('user_id'),
                'company_id' => session('company_id'),
            ];
            Gender::create($gender);
            return redirect()->route('genders.index')->with('success', 'Record Inserted Successfully');
        }
        else
        {
            return redirect()->route('genders.index')->with('success', 'Failed to Enter Department');
        }
    }

    public function update(Request $request, $Id)
    {
        $gender = Gender::find($Id);
        $user_id = session('user_id');
        $gender->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
        ]);
        return redirect()->route('genders.index')->with('update','Record Updated Successfully');
    }

    public function edit($Id)
    {
        $gender = Gender::find($Id);
        return view('admin.gender.edit',compact('gender'));
    }

    public function delete($Id)
    {
        $data = Gender::findOrFail($Id);
        $data->delete();
        return redirect()->route('genders.index')->with('delete','Record Deleted Successfully');
    }
}
