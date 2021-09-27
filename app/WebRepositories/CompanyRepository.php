<?php


namespace App\WebRepositories;


use App\Http\Requests\CompanyRequest;
use App\Models\CashTransaction;
use App\Models\Company;
use App\Models\Region;
use App\Models\User;
use App\Models\AccountTransaction;
use App\WebRepositories\Interfaces\ICompanyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyRepository implements ICompanyRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Company::latest()->get())
               ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('companies.destroy', $data->id).'" method="POST"  id="">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('companies.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="submit" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                        if($data->isActive == true){
                            $button = '<form action="'.route('companies.update', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }else{
                            $button = '<form action="'.route('companies.update', $data->id).'" method="POST"  id="deleteData">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" id="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }
                    })
                ->rawColumns([
                    'action',
                    'isActive',
                    // 'state.Name'
                ])
                ->make(true);
        }
        return view('admin.company.index');
    }

    public function create()
    {
        $regions = Region::with('city')->get();
        return view('admin.company.create',compact('regions'));
    }

    public function store(CompanyRequest $companyRequest)
    {
        $user_id = session('user_id');
        $company = [
            'Name' =>$companyRequest->Name,
            'Mobile' =>$companyRequest->Mobile,
            'Representative' =>$companyRequest->Representative,
            'openingBalance' =>$companyRequest->openingBalance,
            'openingBalanceAsOfDate' =>$companyRequest->openingBalanceAsOfDate,
            'Phone' =>$companyRequest->Phone,
            'Address' =>$companyRequest->Address,
            'region_id' =>$companyRequest->region_id ?? 0,
            'postCode' =>$companyRequest->postCode,
            'user_id' =>$user_id ?? 0,
            'Description' =>$companyRequest->Description,
        ];
        $company = Company::create($company);
        if ($company) {
            $account = new AccountTransaction([
                'company_id' => $company->id,
                'user_id' => $user_id,
                'Description' =>'initial',
            ]);
        }
        $company->account_transaction()->save($account);

        //initial cash or cash on hand for the company
        if ($company) {
            CashTransaction::Create([
                'Reference' => $company->id,
                'user_id' => $user_id,
                'createdDate' => $companyRequest->openingBalanceAsOfDate,
                'company_id' =>$company->id,
                'Details' =>'initial',
                'Credit' =>0.00,
                'Debit' =>$companyRequest->openingBalance,
                'Differentiate' =>$companyRequest->openingBalance,
            ]);
        }
        return redirect()->route('companies.index');
    }

    public function update(Request $request, $Id)
    {
        $company = Company::find($Id);
        $user_id = session('user_id');
        $company->update([
            'Name' => $request->Name,
            'Phone' => $request->Phone,
            'Mobile' => $request->Mobile,
            'Representative' => $request->Representative,
            'openingBalance' =>$request->openingBalance,
            'openingBalanceAsOfDate' =>$request->openingBalanceAsOfDate,
            'Address' => $request->Address,
            'region_id' =>$request->region_id ?? 0,
            'postCode' => $request->postCode,
            'Description' => $request->Description,
            'user_id' => $user_id ?? 0,
        ]);

        //initial cash or cash on hand for the company
//        if ($company) {
//            CashTransaction::Create([
//                'Reference' => $company->id,
//                'user_id' => $user_id,
//                'createdDate' => $request->openingBalanceAsOfDate,
//                'company_id' =>$company->id,
//                'Details' =>'initial',
//                'Credit' =>0.00,
//                'Debit' =>0.00,
//                'Differentiate' =>$request->openingBalance,
//            ]);
//        }
        return redirect()->route('companies.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $regions = Region::with('city')->get();
        $company = Company::with('region')->find($Id);
        return view('admin.company.edit',compact('company','regions'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Company::findOrFail($Id);
        $data->delete();
        return redirect()->route('companies.index');
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
