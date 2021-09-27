<?php


namespace App\WebRepositories;


use App\Http\Requests\DriverRequest;
use App\Models\Customer;
use App\Models\Driver;
use App\WebRepositories\Interfaces\IDriverRepositoryInterface;
use Illuminate\Http\Request;

class DriverRepository implements IDriverRepositoryInterface
{

    public function index()
    {
        $drivers = Driver::with('user','customer')->get();
        return view('admin.driver.index',compact('drivers'));
    }

    public function create()
    {
        $customers = Customer::all();
        return view('admin.driver.create',compact('customers'));
    }

    public function store(DriverRequest $driverRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $driver = [
            'driverName' =>$driverRequest->driverName,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'customer_id' =>$driverRequest->customer_id,
            'Description' =>$driverRequest->Description,
        ];
        Driver::create($driver);
        return redirect()->route('drivers.index');
    }

    public function update(Request $request, $Id)
    {
        $driver = Driver::find($Id);

        $user_id = session('user_id');
        $driver->update([
            'driverName' =>$request->driverName,
            'user_id' =>$user_id,
            'customer_id' =>$request->customer_id,
            'Description' =>$request->Description,

        ]);
        return redirect()->route('drivers.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $customers = Customer::all();
        $driver = Driver::with('customer')->find($Id);
        return view('admin.driver.edit',compact('driver','customers'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Driver::findOrFail($Id);
        $data->delete();
        return redirect()->route('drivers.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }
}
