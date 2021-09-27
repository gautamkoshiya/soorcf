<?php


namespace App\WebRepositories;


use App\Http\Requests\RegionRequest;
use App\Models\City;
use App\Models\Region;
use App\WebRepositories\Interfaces\IRegionRepositoryInterface;
use Illuminate\Http\Request;

class RegionRepository implements IRegionRepositoryInterface
{
    public function index()
    {
        $regions = Region::with('user','city')->get();
        return view('admin.region.index',compact('regions'));
    }

    public function create()
    {
        $cities = City::all();
        return view('admin.region.create',compact('cities'));
    }

    public function store(RegionRequest $regionRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $region = [
            'Name' =>$regionRequest->Name,
            'city_id' =>$regionRequest->city_id ?? 0,
            'user_id' =>$user_id ?? 0,
            'company_id' =>$company_id ?? 0,
        ];
        Region::create($region);
        return redirect()->route('regions.index');
    }

    public function update(Request $request, $Id)
    {
        $region = Region::find($Id);
        $user_id = session('user_id');
        $region->update([
            'Name' =>$request->Name,
            'city_id' =>$request->city_id ?? 0,
            'user_id' =>$user_id ?? 0,
        ]);
        return redirect()->route('regions.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $cities = City::all();
        $region = Region::find($Id);
        return view('admin.region.edit',compact('region','cities'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Region::findOrFail($Id);
        $data->delete();
        return redirect()->route('regions.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function locationDetails($id)
    {
        $regions = Region::with('city.state.country')->find($id);
        return response()->json($regions);
    }
}
