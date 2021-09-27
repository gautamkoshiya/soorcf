<?php


namespace App\WebRepositories;


use App\Http\Requests\CityRequest;
use App\Models\City;
use App\Models\State;
use App\WebRepositories\Interfaces\ICityRepositoryInterface;
use Illuminate\Http\Request;

class CityRepository implements ICityRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(City::with('user','state')->latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('cities.destroy', $data->id).'" method="POST"  id="deleteData">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('cities.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                        if($data->isActive == true){
                            $button = '<form action="'.route('cities.update', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }else{
                            $button = '<form action="'.route('cities.update', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }
                    })
                 ->addColumn('state.Name', function($data) {
                        return $data->state->Name ?? "No State";
                    })
                ->rawColumns(['action','isActive','state.Name'])
                ->make(true);
        }
        return view('admin.city.index');
    }

    public function create()
    {
        $states = State::all();
        return view('admin.city.create',compact('states'));
    }

    public function store(CityRequest $cityRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $city = [
            'Name' =>$cityRequest->Name,
            'state_id' =>$cityRequest->state_id ?? 0,
            'user_id' =>$user_id ?? 0,
            'company_id' =>$company_id ?? 0,
        ];
        City::create($city);
        return redirect()->route('cities.index');
    }

    public function update(Request $request, $Id)
    {
        $city = City::find($Id);
        $user_id = session('user_id');
        $city->update([
            'Name' =>$request->Name,
            'state_id' =>$request->state_id ?? 0,
            'user_id' =>$user_id ?? 0,
        ]);
        return redirect()->route('cities.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $states = State::all();
        $city = City::find($Id);
        return view('admin.city.edit',compact('states','city'));
    }

    public function delete(Request $request, $Id)
    {
        $data = City::findOrFail($Id);
        $data->delete();
        return redirect()->route('cities.index');
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
