<?php

namespace App\WebRepositories;

use App\Http\Requests\MeterReaderRequest;
use App\Models\MeterReader;
use App\WebRepositories\Interfaces\IMeterReaderRepositoryInterface;
use Hamcrest\Description;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class MeterReaderRepository implements  IMeterReaderRepositoryInterface
{

    public function index()
    {
        $meter_readers = MeterReader::where('company_id','=',session('company_id'))->get();
        return view('admin.meter.create',compact('meter_readers'));
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function store(MeterReaderRequest $meterReaderRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data = [
            'Name' => $meterReaderRequest->Name,
            'shortDescriptionForm' =>  $meterReaderRequest->Description,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ];
        MeterReader::create($data);
        return redirect()->route('meter_readers.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data = MeterReader::find($Id);
        $data->update([
            'Name' => $request->Name,
            'shortDescriptionForm' =>  $request->Description,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        return redirect()->route('meter_readers.index')->with('update','Record Updated Successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $meter_reader = MeterReader::find($Id);
        return view('admin.meter.edit',compact('meter_reader'));
    }

    public function delete(Request $request, $Id)
    {
        $Update = MeterReader::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $Update->update([
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        $state = MeterReader::withoutTrashed()->find($Id);
        if($state->trashed())
        {
            return redirect()->route('meter_readers.index');
        }
        else
        {
            $state->delete();
            return redirect()->route('meter_readers.index')->with('delete','Record deleted Successfully');
        }
    }

    public function restore($Id)
    {
        $state = MeterReader::onlyTrashed()->find($Id);
        if (!is_null($state))
        {
            $state->restore();
            return redirect()->route('meter_readers.index')->with('restore','Record Restored Successfully');
        }
    }

    public function trashed()
    {
        $trashes = MeterReader::with('user')->onlyTrashed()->get();
        return view('admin.meter_readers.edit',compact('trashes'));
    }
}
