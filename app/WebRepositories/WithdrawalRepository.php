<?php


namespace App\WebRepositories;


use App\Http\Requests\WithdrawalRequest;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\UpdateNote;
use App\Models\Withdrawal;
use App\WebRepositories\Interfaces\IWithdrawalRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalRepository implements IWithdrawalRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Withdrawal::with(['bank'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->select('id','Amount','bank_id','Reference','withdrawalDate','company_id')->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<a href="'.route('withdrawals.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    //$button .='<a href="'.url('Withdrawal_delete', $data->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                    $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    return $button;
                })
                ->addColumn('bankName', function($data) {
                    return $data->bank->Name ?? "No Data";
                })
                ->addColumn('withdrawalDate', function($data) {
                    return date('d-m-Y', strtotime($data->withdrawalDate)) ?? "No date";
                })
                ->rawColumns([
                    'action',
                    'Amount',
                    'Reference',
                    'withdrawalDate',
                ])
                ->make(true);
        }
        return view('admin.withdrawal.index');
    }

    public function create()
    {
        $banks = Bank::where('company_id',session('company_id'))->get();
        return view('admin.withdrawal.create',compact('banks'));
    }

    public function store(WithdrawalRequest $withdrawalRequest)
    {
        DB::transaction(function () use($withdrawalRequest) {
            $user_id = session('user_id');
            $company_id = session('company_id');

            $withdrawal = [
                'Amount' =>$withdrawalRequest->Amount,
                'bank_id' =>$withdrawalRequest->bank_id,
                'Reference' =>strip_tags($withdrawalRequest->Reference),
                'withdrawalDate' =>$withdrawalRequest->withdrawalDate,
                'Description' =>$withdrawalRequest->Description,
                'user_id' =>$user_id,
                'company_id' =>$company_id,
            ];
            $withdrawal = Withdrawal::create($withdrawal);
            $withdrawal = $withdrawal->id;

            // start accounting //
            if ($withdrawal)
            {
                // debit cash account and credit bank account
                $bankTransaction = BankTransaction::where(['bank_id'=> $withdrawalRequest->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$withdrawal;
                $bank_transaction->createdDate=$withdrawalRequest->withdrawalDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='withdrawals';
                $bank_transaction->Details='Withdrawal|'.$withdrawal;
                $bank_transaction->Credit=$withdrawalRequest->Amount;
                $bank_transaction->Debit=0.00;
                $bank_transaction->Differentiate=$difference-$withdrawalRequest->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $withdrawalRequest->bank_id;
                $bank_transaction->updateDescription = strip_tags($withdrawalRequest->Reference);
                $bank_transaction->save();

                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$withdrawal;
                $cash_transaction->createdDate=$withdrawalRequest->withdrawalDate;
                $cash_transaction->Type='withdrawals';
                $cash_transaction->Details='Withdrawal|'.$withdrawal;
                $cash_transaction->Credit=0.00;
                $cash_transaction->Debit=$withdrawalRequest->Amount;
                $cash_transaction->Differentiate=$difference+$withdrawalRequest->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = strip_tags($withdrawalRequest->Reference);
                $cash_transaction->save();
            }
            // end accounting //
        });
        return redirect()->route('withdrawals.index');
    }

    public function update(Request $request, $Id)
    {
        DB::transaction(function () use($request,$Id) {
            $withdrawn = Withdrawal::find($Id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            // start reverse accounting //
            if($withdrawn)
            {
                // credit cash account and debit bank account
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$withdrawn->id;
                $cash_transaction->createdDate=$withdrawn->withdrawalDate;
                $cash_transaction->Type='withdrawals';
                $cash_transaction->Details='WithdrawalReverse|'.$withdrawn->id;
                $cash_transaction->Credit=$withdrawn->Amount;
                $cash_transaction->Debit=0.00;
                $cash_transaction->Differentiate=$difference-$withdrawn->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $withdrawn->Reference;
                $cash_transaction->save();

                $bankTransaction = BankTransaction::where(['bank_id'=> $withdrawn->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$withdrawn->id;
                $bank_transaction->createdDate=$withdrawn->withdrawalDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='withdrawals';
                $bank_transaction->Details='WithdrawalReverse|'.$withdrawn->id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$withdrawn->Amount;
                $bank_transaction->Differentiate=$difference+$withdrawn->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $withdrawn->bank_id;
                $bank_transaction->updateDescription = strip_tags($withdrawn->Reference);
                $bank_transaction->save();
            }
            // end reverse accounting //

            // start accounting //
            if($withdrawn)
            {
                // credit cash account and debit bank account
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$withdrawn->id;
                $cash_transaction->createdDate=$request->withdrawalDate;
                $cash_transaction->Type='withdrawals';
                $cash_transaction->Details='Withdrawal|'.$withdrawn->id;
                $cash_transaction->Credit=0.00;
                $cash_transaction->Debit=$request->Amount;
                $cash_transaction->Differentiate=$difference+$request->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = strip_tags($request->Reference);
                $cash_transaction->save();

                $bankTransaction = BankTransaction::where(['bank_id'=> $request->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$withdrawn->id;
                $bank_transaction->createdDate=$request->withdrawalDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='withdrawals';
                $bank_transaction->Details='Withdrawal|'.$withdrawn->id;
                $bank_transaction->Credit=$request->Amount;
                $bank_transaction->Debit=0.00;
                $bank_transaction->Differentiate=$difference-$request->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $request->bank_id;
                $bank_transaction->updateDescription = strip_tags($request->Reference);
                $bank_transaction->save();
            }
            // end accounting //

            $withdrawn->update([
                'Amount' =>$request->Amount,
                'bank_id' =>$request->bank_id,
                'Reference' =>strip_tags($request->Reference),
                'withdrawalDate' =>$request->withdrawalDate,
                'Description' =>$request->Description,
                'user_id' =>$user_id,
            ]);
        });
        return redirect()->route('withdrawals.index');
    }

    public function edit($Id)
    {
        $banks = Bank::where('company_id',session('company_id'))->get();
        $withdrawal = Withdrawal::with('bank')->find($Id);
        return view('admin.withdrawal.edit',compact('withdrawal','banks'));
    }

    public function delete($Id)
    {
        DB::transaction(function () use($Id) {
            $withdrawn = Withdrawal::find($Id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            // start reverse accounting //
            if($withdrawn)
            {
                // credit cash account and debit bank account
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$withdrawn->id;
                $cash_transaction->createdDate=$withdrawn->withdrawalDate;
                $cash_transaction->Type='withdrawals';
                $cash_transaction->Details='WithdrawalReverse|'.$withdrawn->id;
                $cash_transaction->Credit=$withdrawn->Amount;
                $cash_transaction->Debit=0.00;
                $cash_transaction->Differentiate=$difference-$withdrawn->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $withdrawn->Reference;
                $cash_transaction->save();

                $bankTransaction = BankTransaction::where(['bank_id'=> $withdrawn->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$withdrawn->id;
                $bank_transaction->createdDate=$withdrawn->withdrawalDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='withdrawals';
                $bank_transaction->Details='WithdrawalReverse|'.$withdrawn->id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$withdrawn->Amount;
                $bank_transaction->Differentiate=$difference+$withdrawn->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $withdrawn->bank_id;
                $bank_transaction->updateDescription = strip_tags($withdrawn->Reference);
                $bank_transaction->save();
            }
            // end reverse accounting //
            $withdrawn->update(['user_id' =>$user_id,]);
            $withdrawn->delete();
        });
        return redirect()->route('withdrawals.index');
    }

    public function withdrawal_delete_post(Request $request)
    {
        DB::transaction(function () use($request) {
            $withdrawn = Withdrawal::find($request->row_id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            // start reverse accounting //
            if($withdrawn)
            {
                // credit cash account and debit bank account
                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                $difference = $cashTransaction->last()->Differentiate;
                $cash_transaction = new CashTransaction();
                $cash_transaction->Reference=$withdrawn->id;
                $cash_transaction->createdDate=$withdrawn->withdrawalDate;
                $cash_transaction->Type='withdrawals';
                $cash_transaction->Details='WithdrawalReverse|'.$withdrawn->id;
                $cash_transaction->Credit=$withdrawn->Amount;
                $cash_transaction->Debit=0.00;
                $cash_transaction->Differentiate=$difference-$withdrawn->Amount;
                $cash_transaction->user_id = $user_id;
                $cash_transaction->company_id = $company_id;
                $cash_transaction->PadNumber = $withdrawn->Reference;
                $cash_transaction->save();

                $bankTransaction = BankTransaction::where(['bank_id'=> $withdrawn->bank_id])->get();
                $difference = $bankTransaction->last()->Differentiate;
                $bank_transaction = new BankTransaction();
                $bank_transaction->Reference=$withdrawn->id;
                $bank_transaction->createdDate=$withdrawn->withdrawalDate ?? date('Y-m-d h:i:s');
                $bank_transaction->Type='withdrawals';
                $bank_transaction->Details='WithdrawalReverse|'.$withdrawn->id;
                $bank_transaction->Credit=0.00;
                $bank_transaction->Debit=$withdrawn->Amount;
                $bank_transaction->Differentiate=$difference+$withdrawn->Amount;
                $bank_transaction->user_id = $user_id;
                $bank_transaction->company_id = $company_id;
                $bank_transaction->bank_id = $withdrawn->bank_id;
                $bank_transaction->updateDescription = strip_tags($withdrawn->Reference);
                $bank_transaction->save();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'withdrawals';
                $update_note->RelationId = $request->row_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            }
            // end reverse accounting //
            $withdrawn->update(['user_id' =>$user_id,]);
            $withdrawn->delete();
        });
        //return redirect()->route('withdrawals.index');
        return Response()->json(true);
    }
}
