<?php

namespace App\WebRepositories;

use App\Models\ReportFileType;
use App\WebRepositories\Interfaces\IReportFileTypeRepositoryInterface;
use Illuminate\Http\Request;

class ReportFileTypeRepository implements IReportFileTypeRepositoryInterface
{
    public function index()
    {
        $types = ReportFileType::with('user','company')->get();
        return view('admin.report_file_types.index',compact('types'));
    }

    public function create()
    {
        return view('admin.report_file_types.create');
    }

    public function store(Request $request)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $type = [
            'Name' => $request->Name,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ];
        ReportFileType::create($type);
        return redirect()->route('report_file_types.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        $type = ReportFileType::find($Id);
        $user_id = session('user_id');
        $type->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
        ]);
        return redirect()->route('report_file_types.index')->with('update','Record Updated Successfully');
    }

    public function edit($Id)
    {
        $type = ReportFileType::find($Id);
        return view('admin.report_file_types.edit',compact('type'));
    }

    public function delete($Id)
    {
        $data = ReportFileType::findOrFail($Id);
        $data->delete();
        return redirect()->route('report_file_types.index')->with('delete','Record Deleted Successfully');
    }
}
