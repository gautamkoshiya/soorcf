<?php


namespace App\WebRepositories;


use App\Http\Requests\DepositRequest;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Deposit;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IDepositRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositRepository implements IDepositRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Deposit::with(['bank'=>function($q){$q->select('id','Name');}])->select('id','Amount','bank_id','Reference','depositDate','company_id','Description')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
//                    $button = '<form action="'.route('deposits.destroy', $data->id).'" method="POST">';
//                    $button .= @csrf_field();
//                    $button .= @method_field('DELETE');
                    $button = '<a href="'.route('deposits.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    //$button .='<a href="'.url('Deposit_delete', $data->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
//                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
//                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('bankName', function($data) {
                    return $data->bank->Name ?? "No Data";
                })
                ->addColumn('depositDate', function($data) {
                    return date('d-m-Y', strtotime($data->depositDate)) ?? "No date";
                })
                ->rawColumns([
                    'action',
                    'Amount',
                    'Reference',
                    'depositDate',
                ])
                ->make(true);
        }
        return view('admin.deposit.index');
    }

    public function create()
    {
        $banks = Bank::where('company_id',session('company_id'))->get();
        return view('admin.deposit.create',compact('banks'));
    }

    public function store(DepositRequest $depositRequest)
    {
        DB::transaction(function () use($depositRequest) {
            $user_id = session('user_id');
            $company_id = session('company_id');

            $deposit = [
                'Amount' =>$depositRequest->Amount,
                'bank_id' =>$depositRequest->bank_id,
                'Reference' =>strip_tags($depositRequest->Reference),
                'depositDate' =>$depositRequest->depositDate,
                'Description' =>$depositRequest->Description,
                'user_id' =>$user_id,
                'company_id' =>$company_id,
            ];
            $deposit = Deposit::create($deposit);
            $deposit = $deposit->id;

            // start accounting //
            if ($deposit)
            {
                // credit cash account and debit bank account
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$deposit;
                $cash_transaction->createdDate=$depositRequest->depositDate;
                $cash_transaction->Type='deposits';
                $cash_transaction->Details='Deposit|'.$deposit;
                $cash_transaction->Credit=$depositRequest->Amount;
                $cash_transaction->Debit=0.00;
                $cash_transaction->Differentiate=$difference-$depositRequest->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = strip_tags($depositRequest->Reference);
                $cash_transaction->save();

                $bankTransaction = BankTransaction::where(['bank_id'=> $depositRequest->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$deposit;
                $bank_transaction->createdDate=$depositRequest->depositDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='deposits';
                $bank_transaction->Details='Deposit|'.$deposit;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$depositRequest->Amount;
                $bank_transaction->Differentiate=$difference+$depositRequest->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $depositRequest->bank_id;
                $bank_transaction->updateDescription = strip_tags($depositRequest->Reference);
                $bank_transaction->save();
            }
            // end accounting //
        });
        return redirect()->route('deposits.index');
    }

    public function update(Request $request, $Id)
    {
        DB::transaction(function () use($request,$Id) {
            $deposited = Deposit::find($Id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            // start reverse accounting //
            if($deposited)
            {
                // credit bank account and debit cash account
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$deposited->id;
                $cash_transaction->createdDate=$deposited->depositDate;
                $cash_transaction->Type='deposits';
                $cash_transaction->Details='DepositReverse|'.$deposited->id;
                $cash_transaction->Credit=0.00;
                $cash_transaction->Debit=$deposited->Amount;
                $cash_transaction->Differentiate=$difference+$deposited->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $deposited->Reference;
                $cash_transaction->save();

                $bankTransaction = BankTransaction::where(['bank_id'=> $deposited->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$deposited->id;
                $bank_transaction->createdDate=$deposited->depositDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='deposits';
                $bank_transaction->Details='DepositReverse|'.$deposited->id;
                $bank_transaction->Credit=$deposited->Amount;
                $bank_transaction->Debit=0.00;
                $bank_transaction->Differentiate=$difference-$deposited->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $deposited->bank_id;
                $bank_transaction->updateDescription = strip_tags($deposited->Reference);
                $bank_transaction->save();
            }
            // end reverse accounting //

            // start accounting //
            if($deposited)
            {
                // credit cash account and debit bank account
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$deposited->id;
                $cash_transaction->createdDate=$request->depositDate;
                $cash_transaction->Type='deposits';
                $cash_transaction->Details='Deposit|'.$deposited->id;
                $cash_transaction->Credit=$request->Amount;
                $cash_transaction->Debit=0.00;
                $cash_transaction->Differentiate=$difference-$request->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = strip_tags($request->Reference);
                $cash_transaction->save();

                $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$deposited->id;
                $bank_transaction->createdDate=$request->depositDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='deposits';
                $bank_transaction->Details='Deposit|'.$deposited->id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$request->Amount;
                $bank_transaction->Differentiate=$difference+$request->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $request->bank_id;
                $bank_transaction->updateDescription = strip_tags($request->Reference);
                $bank_transaction->save();
            }
            // end accounting //

            $deposited->update([
                'Amount' =>$request->Amount,
                'bank_id' =>$request->bank_id,
                'Reference' =>strip_tags($request->Reference),
                'depositDate' =>$request->depositDate,
                'Description' =>$request->Description,
                'user_id' =>$user_id,
            ]);
        });
        return redirect()->route('deposits.index');
    }

    public function edit($Id)
    {
        $banks = Bank::where('company_id',session('company_id'))->get();
        $deposit = Deposit::with('bank')->find($Id);
        return view('admin.deposit.edit',compact('deposit','banks'));
    }

    public function delete($Id)
    {
        DB::transaction(function () use($Id) {
            $deposited = Deposit::find($Id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            // start reverse accounting //
            if($deposited)
            {
                // credit bank account and debit cash account
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$deposited->id;
                $cash_transaction->createdDate=$deposited->depositDate;
                $cash_transaction->Type='deposits';
                $cash_transaction->Details='DepositReverse|'.$deposited->id;
                $cash_transaction->Credit=0.00;
                $cash_transaction->Debit=$deposited->Amount;
                $cash_transaction->Differentiate=$difference+$deposited->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $deposited->Reference;
                $cash_transaction->save();

                $bankTransaction = BankTransaction::where(['bank_id'=> $deposited->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$deposited->id;
                $bank_transaction->createdDate=$deposited->depositDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='deposits';
                $bank_transaction->Details='DepositReverse|'.$deposited->id;
                $bank_transaction->Credit=$deposited->Amount;
                $bank_transaction->Debit=0.00;
                $bank_transaction->Differentiate=$difference-$deposited->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $deposited->bank_id;
                $bank_transaction->updateDescription = strip_tags($deposited->Reference);
                $bank_transaction->save();
            }
            // end reverse accounting //

            $deposited->update(['user_id' =>$user_id,]);
            $deposited->delete();
        });
        return redirect()->route('deposits.index');
    }

    public function deposit_delete_post(Request $request)
    {
        DB::transaction(function () use($request) {
            $deposited = Deposit::find($request->row_id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            // start reverse accounting //
            if($deposited)
            {
                // credit bank account and debit cash account
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$deposited->id;
                $cash_transaction->createdDate=$deposited->depositDate;
                $cash_transaction->Type='deposits';
                $cash_transaction->Details='DepositReverse|'.$deposited->id;
                $cash_transaction->Credit=0.00;
                $cash_transaction->Debit=$deposited->Amount;
                $cash_transaction->Differentiate=$difference+$deposited->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $deposited->Reference;
                $cash_transaction->save();

                $bankTransaction = BankTransaction::where(['bank_id'=> $deposited->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$deposited->id;
                $bank_transaction->createdDate=$deposited->depositDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='deposits';
                $bank_transaction->Details='DepositReverse|'.$deposited->id;
                $bank_transaction->Credit=$deposited->Amount;
                $bank_transaction->Debit=0.00;
                $bank_transaction->Differentiate=$difference-$deposited->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $deposited->bank_id;
                $bank_transaction->updateDescription = strip_tags($deposited->Reference);
                $bank_transaction->save();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'deposits';
                $update_note->RelationId = $request->row_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            }
            // end reverse accounting //

            $deposited->update(['user_id' =>$user_id,]);
            $deposited->delete();
        });
        //return redirect()->route('deposits.index');
        return Response()->json(true);
    }
}
