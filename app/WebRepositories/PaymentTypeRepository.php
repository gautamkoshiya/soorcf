<?php


namespace App\WebRepositories;


use App\Http\Requests\PaymentTypeRequest;
use App\WebRepositories\Interfaces\IPaymentTypeRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\PaymentType;

class PaymentTypeRepository implements IPaymentTypeRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(PaymentType::latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('payment_types.destroy', $data->id).'" method="POST"  id="deleteData">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('payment_types.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
               ->addColumn('isActive', function($data) {
                    if($data->isActive == true){
                        $button = '<form action="" method="POST"  id="">';
                        $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                        return $button;
                    }else{
                        $button = '<form action="" method="POST"  id="">';
                        $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                        return $button;
                    }
                })
                ->rawColumns(['action','isActive'])
                ->make(true);
        }
        return view('admin.payment_type.index');
    }

    public function create()
    {
         return view('admin.payment_type.create');
    }

    public function store(PaymentTypeRequest $paymentTypeRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data = [
            'Name' => $paymentTypeRequest->Name,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ];
         PaymentType::create($data);
        return redirect()->route('payment_types.index')->with('success','Record Inerted successfully');
    }

    public function update(Request $request, $Id)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data = PaymentType::find($Id);
        $data->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        return redirect()->route('payment_types.index')->with('update','Record updated successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $payment_type = PaymentType::find($Id);
        return view('admin.payment_type.edit',compact('payment_type'));
    }

    public function delete(Request $request, $Id)
    {
        $Update = PaymentType::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $Update->update([
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        $data = PaymentType::withoutTrashed()->find($Id);
        if($data->trashed())
        {
            return redirect()->route('payment_types.index');
        }
        else
        {
            $data->delete();
            return redirect()->route('payment_types.index')->with('delete','Record Update Successfully');
        }
    }

    public function restore($Id)
    {
        $data = PaymentType::onlyTrashed()->find($Id);
        if (!is_null($data))
        {
            $data->restore();
            return redirect()->route('payment_types.index')->with('restore','Record Restore Successfully');
        }
        return redirect()->route('payment_types.index');
    }

    public function trashed()
    {
        $trashes = PaymentType::with('user')->onlyTrashed()->get();
        return view('admin.payment_type.edit',compact('trashes'));
    }
}
