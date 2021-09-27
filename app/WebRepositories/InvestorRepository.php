<?php


namespace App\WebRepositories;


use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\Company;
use App\Models\Investor;
use App\WebRepositories\Interfaces\IInvestorRepositoryInterface;
use Illuminate\Http\Request;

class InvestorRepository implements IInvestorRepositoryInterface
{

    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Investor::with('user','company')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    //$button = '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                    $button='';
                    return $button;
                })
                /*->addColumn('push', function($data) {
                    if($data->isPushed == false){
                        $button = '<form action="'. url('customer_payments_push',$data->id) .'" method="POST"  id="">';
                        $button .= @csrf_field();
                        $button .= @method_field('PUT');
                        $button .= '<a href="'.route('payment_receives.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        $button .= '&nbsp;';
                        $button .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm()"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-danger" onclick="cancel_customer_payment(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-danger" onclick="cancel_customer_payment(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                        return $button;
                    }
                })*/
                ->rawColumns(
                    [
                        'action',
                    ])
                ->make(true);
        }
        return view('admin.investor.index');
    }

    public function create()
    {
        return view('admin.investor.create');
    }

    public function store(Request $request)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $investor = [
            'Name' =>$request->Name,
            'Mobile' =>$request->Mobile,
            'SharePercentage' =>$request->SharePercentage,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
        ];
        $investor = Investor::create($investor);
        if ($investor)
        {
            $account = [
                'investor_id' => $investor->id,
                'user_id' => $user_id,
                'createdDate' => date('Y-m-d'),
                'company_id' =>$company_id,
                'Description' =>'initial',
                'Credit' =>0.00,
                'Debit' =>0.00,
                'Differentiate' =>0.00,
            ];
            AccountTransaction::create($account);
        }
        return redirect()->route('investor.index');
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
    }

    public function delete(Request $request)
    {
        // TODO: Implement delete() method.
    }

    public function getInvestorForCompany($Id)
    {
        $investors = Investor::select('id','Name')->where('company_id',session('company_id'))->get();
        //echo "<pre>";print_r($investors);die;
        return response()->json(array('investors'=>$investors));
    }
}
