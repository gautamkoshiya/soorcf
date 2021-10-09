<?php

namespace App\WebRepositories;

use App\Models\gst;
use App\WebRepositories\Interfaces\IGstRepositoryInterface;
use Illuminate\Http\Request;


class GstRepository implements IGstRepositoryInterface
{
    public function index()
    {
        $gsts = gst::select('id','Name','percentage','IsCombined')->orderBy('id')->get();
        return view('admin.gst.index',compact('gsts'));
    }

    public function create()
    {
        return view('admin.gst.create');
    }

    public function store(Request $request)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $gst = [
            'Name' => $request->Name,
            'percentage' => $request->percentage,
            'IsCombined' => $request->IsCombined,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ];
        gst::create($gst);
        return redirect()->route('gsts.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        $gst = gst::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $gst->update([
            'Name' => $request->Name,
            'percentage' => $request->percentage,
            'IsCombined' => $request->IsCombined,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        return redirect()->route('gsts.index')->with('update','Record Updated Successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $gst = gst::find($Id);
        return view('admin.gst.edit',compact('gst'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Unit::findOrFail($Id);
        $data->delete();
        return redirect()->route('units.index')->with('delete','Record Deleted Successfully');
    }
}
