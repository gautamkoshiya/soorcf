<?php

namespace App\WebRepositories;

use App\Models\Nationality;
use App\WebRepositories\Interfaces\INationalityRepositoryInterface;
use Illuminate\Http\Request;

class NationalityRepository implements INationalityRepositoryInterface
{
    public function index()
    {
        $nationality = Nationality::get();
        return view('admin.nationality.index',compact('nationality'));
    }

    public function create()
    {
        return view('admin.nationality.create');
    }

    public function store(Request $request)
    {
        $exist=Nationality::where('Name','like',$request->Name)->first();
        if(!$exist) {
            $nationality = [
                'Name' => $request->Name,
                'user_id' => session('user_id'),
                'company_id' => session('company_id'),
            ];
            Nationality::create($nationality);
            return redirect()->route('nationalities.index')->with('success', 'Record Inserted Successfully');
        }
        else
        {
            return redirect()->route('nationalities.index')->with('success', 'Failed to Enter Department');
        }
    }

    public function update(Request $request, $Id)
    {
        $nationality = Nationality::find($Id);
        $user_id = session('user_id');
        $nationality->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
        ]);
        return redirect()->route('nationalities.index')->with('update','Record Updated Successfully');
    }

    public function edit($Id)
    {
        $nationality = Nationality::find($Id);
        return view('admin.nationality.edit',compact('nationality'));
    }

    public function delete($Id)
    {
        $data = Nationality::findOrFail($Id);
        $data->delete();
        return redirect()->route('nationalities.index')->with('delete','Record Deleted Successfully');
    }
}
