<?php


namespace App\WebRepositories;


use App\Http\Requests\SupplierAdvanceRequest;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierAdvance;
use App\Models\SupplierAdvanceDetail;
use App\Models\SupplierPayment;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierAdvanceRepository implements ISupplierAdvanceRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(SupplierAdvance::with('supplier')->where('company_id',session('company_id'))->where('isActive',1)->latest()->get())
                ->addColumn('supplier', function($data) {
                    return $data->supplier->Name ?? "No Data";
                })
                ->addColumn('push', function($data) {
                    if($data->isPushed == false){

                        $button = '<a href="'.route('supplier_advances.edit', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                        $button .='&nbsp;';
                        $button .= '<button class="btn btn-danger btn-sm" onclick="push_payment(this.id)" type="button" id="pay_'.$data->id.'"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        //$button .= '<button class="btn btn-danger" onclick="cancel_supplier_advance(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                        $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="paymentDelete btn btn-danger btn-sm" data-target="#deleteSupplierAdvance"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                        return $button;
                    }else{
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        //$button .= '<button class="btn btn-danger" onclick="cancel_supplier_advance(this.id)" type="button" id="cancel_'.$data->id.'"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                        $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="paymentDelete btn btn-danger btn-sm" data-target="#deleteSupplierAdvance"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                        return $button;
                    }
                })
                ->addColumn('disburse', function($data) {
                    if($data->isPushed == true){
                        if($data->IsSpent == 0){
                            $button = '<a href="'.route('supplier_advances_get_disburse', $data->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-battery-full"> Disburse</i></a>';
                            $button .= '&nbsp;&nbsp;';
                            $button .= '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                            return $button;
                        }else{
                            $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-battery-empty"> Disbursed</i></button>';
                            $button .= '&nbsp;&nbsp;';
                            $button .= '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                            return $button;
                        }
                    }
                    else
                    {
                        return 'Not Available';
                    }
                })
                ->addColumn('TransferDate', function($data) {
                    return date('d-m-Y', strtotime($data->TransferDate)) ?? "No date";
                })
                ->rawColumns(
                    [
                        'push',
                        'disburse',
                        'supplier',
                        'TransferDate',
                    ])
                ->make(true);
        }
        return view('admin.supplierAdvance.index');
    }

    public function all_supplier_advance(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = SupplierAdvance::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select sa.id,sa.Amount,sa.supplier_id,sa.company_id,sa.bank_id,sa.accountNumber,sa.TransferDate,sa.paymentType,sa.receiptNumber,sa.receiverName,sa.Description,sa.IsSpent,sa.spentBalance,sa.remainingBalance,sa.isPushed,sa.isActive,sa.deleted_at,s.Name from supplier_advances as sa left join suppliers as s on s.id = sa.supplier_id  where sa.company_id = '.session('company_id').' and sa.isActive = 1 and sa.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $supplier_advance = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select sa.id,sa.Amount,sa.supplier_id,sa.company_id,sa.bank_id,sa.accountNumber,sa.TransferDate,sa.paymentType,sa.receiptNumber,sa.receiverName,sa.Description,sa.IsSpent,sa.spentBalance,sa.remainingBalance,sa.isPushed,sa.isActive,sa.deleted_at,s.Name from supplier_advances as sa left join suppliers as s on s.id = sa.supplier_id  where sa.company_id = '.session('company_id').' and sa.isActive = 1 and sa.deleted_at is null and sa.receiptNumber LIKE "%'.$search.'%" or s.Name LIKE "%'.$search.'%" and sa.deleted_at is null order by id desc limit '.$limit.' offset '.$start ;
            $supplier_advance = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,sa.id,sa.Amount,sa.supplier_id,sa.company_id,sa.bank_id,sa.accountNumber,sa.TransferDate,sa.paymentType,sa.receiptNumber,sa.receiverName,sa.Description,sa.IsSpent,sa.spentBalance,sa.remainingBalance,sa.isPushed,sa.isActive,sa.deleted_at,s.Name from supplier_advances as sa left join suppliers as s on s.id = sa.supplier_id  where sa.company_id = '.session('company_id').' and sa.isActive = 1 and sa.deleted_at is null and sa.receiptNumber LIKE "%'.$search.'%" or s.Name LIKE "%'.$search.'%" and sa.deleted_at is null order by id desc limit '.$limit.' offset '.$start ;
            $supplier_advance_count = DB::select(DB::raw($sql_count));
            if(!empty($supplier_advance_count))
            {
                $totalFiltered = $supplier_advance_count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($supplier_advance))
        {
            foreach ($supplier_advance as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['supplier'] = $single->Name ?? "N.A.";
                $nestedData['Amount'] = $single->Amount ?? 0.00;
                $nestedData['spentBalance'] = $single->spentBalance ?? 0.00;
                $nestedData['remainingBalance'] = $single->remainingBalance ?? 0.00;
                $nestedData['paymentType'] = $single->paymentType ?? 'N.A.';
                $nestedData['TransferDate'] = date('d-m-Y', strtotime($single->TransferDate));
                $nestedData['Description'] = mb_strimwidth($single->Description, 0, 50, '...') ?? "N.A.";
                $nestedData['receiptNumber'] = $single->receiptNumber ?? "N.A.";
                $push='';
                if($single->isPushed == false)
                {
                    $push .= '<a href="'.route('supplier_advances.edit', $single->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $push .='&nbsp;';
                    $push .= '<button class="btn btn-danger btn-sm" onclick="push_payment(this.id)" type="button" id="pay_'.$single->id.'"><i style="font-size: 20px" class="fa fa-arrow-up"> Push</i></button>';
                    $push .= '&nbsp;&nbsp;';
                    $push.='<a href="#" data-id="'.$single->id.'" data-toggle="modal" class="paymentDelete btn btn-danger btn-sm" data-target="#deleteSupplierAdvance"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                }
                else
                {
                    $push = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-ban"> Pushed</i></button>';
                    $push .= '&nbsp;&nbsp;';
                    $push.='<a href="#" data-id="'.$single->id.'" data-toggle="modal" class="paymentDelete btn btn-danger btn-sm" data-target="#deleteSupplierAdvance"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                }
                $nestedData['push']=$push;
                $button='';
                if($single->isPushed == true)
                {
                    if($single->IsSpent == 0)
                    {
                        $button = '<a href="'.route('supplier_advances_get_disburse', $single->id).'"  class=" btn btn-warning btn-sm"><i style="font-size: 20px" class="fa fa-battery-full"> Disburse</i></a>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                    }
                    else
                    {
                        $button = '<button type="submit" class="btn btn-default btn-sm"><i style="font-size: 20px" class="fa fa-battery-empty"> Disbursed</i></button>';
                        $button .= '&nbsp;&nbsp;';
                        $button .= '<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                    }
                }
                $nestedData['disburse']=$button;
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function create()
    {
        $suppliers = Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
        $banks = Bank::where('company_id',session('company_id'))->get();
        return view('admin.supplierAdvance.create',compact('suppliers','banks'));
    }

    public function store(SupplierAdvanceRequest $supplierAdvanceRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $advance = [
            'receiptNumber' =>preg_replace("/\s+/", "", $supplierAdvanceRequest->receiptNumber),
            'paymentType' =>$supplierAdvanceRequest->paymentType,
            'Amount' =>$supplierAdvanceRequest->amount,
            'spentBalance' =>0.00,
            'remainingBalance' =>$supplierAdvanceRequest->amount,
            'IsSpent' =>0,
            'IsPartialSpent' =>0,
            'sumOf' =>$supplierAdvanceRequest->amountInWords,
            'receiverName' =>$supplierAdvanceRequest->receiverName,
            'accountNumber' =>$supplierAdvanceRequest->accountNumber,
            'ChequeNumber' =>$supplierAdvanceRequest->ChequeNumber,
            'TransferDate' =>$supplierAdvanceRequest->TransferDate,
            'registerDate' =>$supplierAdvanceRequest->registerDate,
            'bank_id' =>$supplierAdvanceRequest->bank_id ?? 0,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'supplier_id' =>$supplierAdvanceRequest->supplier_id ?? 0,
            'Description' =>$supplierAdvanceRequest->Description,
        ];
        SupplierAdvance::create($advance);
        return redirect()->route('supplier_advances.index');
    }

    public function update(Request $request, $Id)
    {
        $advance = SupplierAdvance::find($Id);

        $user_id = session('user_id');
        $advance->update([
            'receiptNumber' =>preg_replace("/\s+/", "", $request->receiptNumber),
            'paymentType' =>$request->paymentType,
            'Amount' =>$request->amount,
            'spentBalance' =>0.00,
            'remainingBalance' =>$request->amount,
            'IsSpent' =>0,
            'IsPartialSpent' =>0,
            'sumOf' =>$request->amountInWords,
            'receiverName' =>$request->receiverName,
            'accountNumber' =>$request->accountNumber,
            'ChequeNumber' =>$request->ChequeNumber ?? 0,
            'TransferDate' =>$request->TransferDate,
            'registerDate' =>$request->registerDate,
            'bank_id' =>$request->bank_id ?? 0,
            'user_id' =>$user_id,
            'supplier_id' =>$request->supplier_id ?? 0,
            'Description' =>$request->Description,
        ]);
        return redirect()->route('supplier_advances.index');
    }

    public function edit($Id)
    {
        $suppliers = Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
        $banks = Bank::where('company_id',session('company_id'))->get();
        $supplierAdvance = SupplierAdvance::with('supplier')->find($Id);
        return view('admin.supplierAdvance.edit',compact('suppliers','supplierAdvance','banks'));
    }

    public function supplier_advances_get_disburse($Id)
    {
        $supplierAdvance = SupplierAdvance::with('supplier')->find($Id);
        return view('admin.supplierAdvance.create_disburse',compact('supplierAdvance'));
    }

    public function supplier_advances_save_disburse(Request $request)
    {
        DB::transaction(function () use($request) {
            $user_id = session('user_id');
            $company_id = session('company_id');
            $AllRequestCount = collect($request->Data)->count();
            if ($AllRequestCount > 0)
            {
                $advance = SupplierAdvance::with('supplier')->find($request->Data['supplier_advance_id']);
                if ($advance->IsSpent == 0 and $advance->remainingBalance > 0)
                {
                    $total_i_have = $advance->remainingBalance;
                    foreach ($request->Data['orders'] as $detail)
                    {
                        $this_purchase = Purchase::where('id', $detail['purchase_id'])->get()->first();
                        if ($this_purchase->IsPaid == 0 and $this_purchase->remainingBalance != 0)
                        {
                            $total_you_need = $this_purchase->remainingBalance;
                            $still_payable_to_you = 0;
                            $total_giving_to_you = 0;
                            $isPartialPaid = 0;
                            if ($total_i_have >= $total_you_need)
                            {
                                $isPaid = 1;
                                $isPartialPaid = 0;
                                $total_i_have = $total_i_have - $total_you_need;

                                $this_purchase->update([
                                    "paidBalance" => $this_purchase->grandTotal,
                                    "remainingBalance" => $still_payable_to_you,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "TermsAndCondition"=>0,
                                    "supplierNote"=>0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'FromAdvance|' . $advance->id,
                                ]);

                                $data = SupplierAdvanceDetail::create([
                                    "amountPaid" => $this_purchase->grandTotal,
                                    "supplier_advances_id" => $advance->id,
                                    "user_id" => $user_id,
                                    "company_id" => $company_id,
                                    "purchase_id" => $this_purchase->id,
                                    'advanceReceiveDetailDate' => $advance->TransferDate,
                                    'createdDate' => date('Y-m-d')
                                ]);
                            }
                            else
                            {
                                $isPaid = 0;
                                $isPartialPaid = 1;
                                $total_giving_to_you = $total_i_have;
                                $total_i_have = $total_i_have - $total_giving_to_you;

                                $this_purchase->update([
                                    "paidBalance" => $this_purchase->paidBalance + $total_giving_to_you,
                                    "remainingBalance" => $this_purchase->remainingBalance - $total_giving_to_you,
                                    "IsPaid" => $isPaid,
                                    "IsPartialPaid" => $isPartialPaid,
                                    "TermsAndCondition"=>0,
                                    "supplierNote"=>0,
                                    "IsNeedStampOrSignature" => false,
                                    "Description" => 'FromAdvance|' . $advance->id,
                                ]);

                                $data = SupplierAdvanceDetail::create([
                                    "amountPaid" => $total_giving_to_you,
                                    "supplier_advances_id" => $advance->id,
                                    "user_id" => $user_id,
                                    "company_id" => $company_id,
                                    "purchase_id" => $this_purchase->id,
                                    'advanceReceiveDetailDate' => $advance->TransferDate,
                                    'createdDate' => date('Y-m-d')
                                ]);
                            }
                        }
                        if ($total_i_have <= 0)
                        {
                            break;
                        }
                    }

                    if ($total_i_have != 0)
                    {
                        $advance->update([
                            'IsSpent' => 0,
                            'IsPartialSpent' => 1,
                            'spentBalance' => $advance->spentBalance + ($advance->remainingBalance - $total_i_have),
                            'remainingBalance' => $total_i_have,
                        ]);
                    }
                    else
                    {
                        $advance->update([
                            'IsSpent' => 1,
                            'IsPartialSpent' => 0,
                            'spentBalance' => $advance->Amount,
                            'remainingBalance' => 0,
                        ]);
                    }
                }
                return redirect()->route('supplier_advances.index')->with('pushed', 'Your Account Debit Successfully');
            }
        });
    }

    public function supplier_advances_push(Request $request, $Id)
    {
        DB::transaction(function () use($request,$Id) {
            $advance = SupplierAdvance::with('supplier')->find($Id);

            $user_id = session('user_id');
            $company_id = session('company_id');

            if ($advance->Amount > 0) {
                $accountTransaction = AccountTransaction::where(['supplier_id' => $advance->supplier_id,])->get();
                $closing_before_advance_debit = $accountTransaction->last()->Differentiate;

                $accountTransaction_ref = 0;
                // account section by gautam //
                if ($advance->paymentType == 'cash') {
                    $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference = $Id;
                    $cash_transaction->createdDate = $advance->TransferDate;
                    $cash_transaction->Type = 'supplier_advances';
                    $cash_transaction->Details = 'SupplierCashAdvance|' . $Id;
                    $cash_transaction->Credit = $advance->Amount;
                    $cash_transaction->Debit = 0.00;
                    $cash_transaction->Differentiate = $difference - $advance->Amount;
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->PadNumber = $advance->receiptNumber;
                    $cash_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['supplier_id' => $advance->supplier_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'supplier_id' => $advance->supplier_id,
                            'referenceNumber' => $advance->receiptNumber,
                            'Debit' => $advance->Amount,
                            'Credit' => 0.00,
                            'Differentiate' => $last_closing - $advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'SupplierCashAdvance|' . $Id,
                            'TransactionDesc' => $advance->Description,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref = $AccountTransactions->id;
                    // new entry done
                } elseif ($advance->paymentType == 'bank') {
                    $bankTransaction = BankTransaction::where(['bank_id' => $advance->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference = $Id;
                    $bank_transaction->createdDate = $advance->TransferDate;
                    $bank_transaction->Type = 'supplier_advances';
                    $bank_transaction->Details = 'SupplierBankAdvance|' . $Id;
                    $bank_transaction->Credit = $advance->Amount;
                    $bank_transaction->Debit = 0.00;
                    $bank_transaction->Differentiate = $difference - $advance->Amount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $advance->bank_id;
                    $bank_transaction->updateDescription = $advance->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['supplier_id' => $advance->supplier_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'supplier_id' => $advance->supplier_id,
                            'referenceNumber' => $advance->receiptNumber,
                            'Debit' => $advance->Amount,
                            'Credit' => 0.00,
                            'Differentiate' => $last_closing - $advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'SupplierBankAdvance|' . $Id,
                            'TransactionDesc' => $advance->Description,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref = $AccountTransactions->id;
                    // new entry done
                } elseif ($advance->paymentType == 'cheque') {
                    $bankTransaction = BankTransaction::where(['bank_id' => $advance->bank_id])->get();
                    $difference = $bankTransaction->last()->Differentiate;
                    $bank_transaction = new BankTransaction();
                    $bank_transaction->Reference = $Id;
                    $bank_transaction->createdDate = $advance->TransferDate;
                    $bank_transaction->Type = 'supplier_advances';
                    $bank_transaction->Details = 'SupplierChequeAdvance|' . $Id;
                    $bank_transaction->Credit = $advance->Amount;
                    $bank_transaction->Debit = 0.00;
                    $bank_transaction->Differentiate = $difference - $advance->Amount;
                    $bank_transaction->user_id = $user_id;
                    $bank_transaction->company_id = $company_id;
                    $bank_transaction->bank_id = $advance->bank_id;
                    $bank_transaction->updateDescription = $advance->ChequeNumber;
                    $bank_transaction->save();

                    // start new entry
                    $accountTransaction = AccountTransaction::where(['supplier_id' => $advance->supplier_id,])->get();
                    $last_closing = $accountTransaction->last()->Differentiate;
                    $AccData =
                        [
                            'supplier_id' => $advance->supplier_id,
                            'referenceNumber' => $advance->receiptNumber,
                            'Debit' => $advance->Amount,
                            'Credit' => 0.00,
                            'Differentiate' => $last_closing - $advance->Amount,
                            'createdDate' => $advance->TransferDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'SupplierChequeAdvance|' . $Id,
                            'TransactionDesc' => $advance->Description,
                        ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                    $accountTransaction_ref = $AccountTransactions->id;
                    // new entry done
                }
                // account section by gautam //

                //now since account is affected we need to auto pay same amount to purchase entries only if last closing is positive value

                /*if($closing_before_advance_debit>0)
                {
                     //we have entries without payment made so make it paid until advance amount becomes zero
                    // bring all unpaid purchase records
                    $all_purchase = Purchase::with('supplier','purchase_details')->where([
                        'supplier_id'=>$Id,
                        'IsPaid'=> false,
                    ])->orderBy('PurchaseDate')->get();
                    //dd($all_purchase);
                    $total_i_have=$advance->Amount;

                    foreach($all_purchase as $purchase)
                    {
                        $total_you_need = $purchase->remainingBalance;
                        $still_payable_to_you=0;
                        $total_giving_to_you=0;
                        $isPartialPaid = 0;
                        if ($total_i_have >= $total_you_need)
                        {
                            $isPaid = 1;
                            $isPartialPaid = 0;
                            $total_i_have = $total_i_have - $total_you_need;

                            $this_sale = Purchase::find($purchase->id);
                            $this_sale->update([
                                "paidBalance"        => $purchase->grandTotal,
                                "remainingBalance"   => $still_payable_to_you,
                                "IsPaid" => $isPaid,
                                "IsPartialPaid" => $isPartialPaid,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid|'.$advance->id,
                                "account_transaction_payment_id" => $accountTransaction_ref,
                            ]);
                        }
                        else
                        {
                            $isPaid = 0;
                            $isPartialPaid = 1;
                            $total_giving_to_you=$total_i_have;
                            $total_i_have = $total_i_have - $total_giving_to_you;

                            $this_purchase = Purchase::find($purchase->id);
                            $this_purchase->update([
                                "paidBalance"        => $purchase->paidBalance+$total_giving_to_you,
                                "remainingBalance"   => $purchase->remainingBalance-$total_giving_to_you,
                                "IsPaid" => $isPaid,
                                "IsPartialPaid" => $isPartialPaid,
                                "IsNeedStampOrSignature" => false,
                                "Description" => 'AutoPaid|'.$advance->id,
                                "account_transaction_payment_id" => $accountTransaction_ref,
                            ]);
                        }

                        if($total_i_have<=0)
                        {
                            break;
                        }
                    }
                }*/
            }

            /* auto pay purchase till advance amount is sufficient for all older purchases */
            //        $accountTransaction = AccountTransaction::where(['supplier_id'=> $advance->supplier_id,])->get();
            //        $last_closing=$accountTransaction->last()->Differentiate;
            //
            //        $total_i_have=$last_closing;
            //        if($last_closing<0)
            //        {
            //            // we are payable to supplier
            //            // bring all unpaid purchase records
            //            $all_purchase = Purchase::with('supplier','purchase_details')->where([
            //                'supplier_id'=>$Id,
            //                'IsPaid'=> false,
            //            ])->orderBy('PurchaseDate')->get();
            //            //dd($all_purchase);
            //
            //
            //            foreach($all_purchase as $purchase)
            //            {
            //                $total_you_need = $purchase->remainingBalance;
            //                $still_payable_to_you=0;
            //                $total_giving_to_you=0;
            //                if ($total_i_have >= $total_you_need)
            //                {
            //                    $isPaid = true;
            //                    $isPartialPaid = false;
            //                    $total_i_have -= $total_you_need;
            //                    $total_giving_to_you=$total_you_need;
            //                }
            //                elseif($total_i_have <= $total_you_need){
            //                    $isPaid = false;
            //                    $isPartialPaid = true;
            //                    $total_giving_to_you=$total_i_have;
            //                    $still_payable_to_you=$total_you_need-$total_i_have;
            //                    $total_i_have -= $total_giving_to_you;
            //                }
            //                /*SupplierPaymentDetail::create([
            //                    "amountPaid"        => $totalAmount,
            //                    "purchase_id"        => $purchase->purchase_id,
            //                    "company_id" => $company_id,
            //                    "user_id"      => $user_id,
            //                    "supplier_payment_id"      => $payment,
            //                    'createdDate' => date('Y-m-d')
            //                ]);*/
            //
            //                /*account entry start*/
            //                // start new entry
            //                $accountTransaction = AccountTransaction::where(['supplier_id'=> $advance->supplier_id,])->get();
            //                $last_closing=$accountTransaction->last()->Differentiate;
            //                $AccData =
            //                    [
            //                        'supplier_id' => $advance->supplier_id,
            //                        'Debit' => $total_giving_to_you,
            //                        'Credit' => 0.00,
            //                        'Differentiate' => $last_closing-$total_giving_to_you,
            //                        'createdDate' => date('Y-m-d'),
            //                        'user_id' => $user_id,
            //                        'company_id' => $company_id,
            //                        'Description'=>'SupplierAutoPaymentFromAdvance|'.$advance->id,
            //                    ];
            //                $AccountTransactions = AccountTransaction::Create($AccData);
            //                // new entry done
            //                /*account entry end*/
            //
            //                $this_purchase = Purchase::find($purchase->id);
            //                $this_purchase->update([
            //                    "paidBalance"        => $total_giving_to_you,
            //                    "remainingBalance"   => $still_payable_to_you,
            //                    "IsPaid" => $isPaid,
            //                    "IsPartialPaid" => $isPartialPaid,
            //                    "IsNeedStampOrSignature" => false,
            //                    "Description" => 'FromAdvance|'.$advance->id,
            //                    "account_transaction_payment_id" => $AccountTransactions->id,
            //                ]);
            //            }
            //        }
            //
            /* auto pay purchase till advance amount is sufficient for all older purchases */

            /* AFTER AUTO PAID PURCHASE IF ANYTHING REMAINS LEFT THAT IS NEED TO RECORD AS ADVANCE*/
            //$advance->Amount=$total_i_have;

            $advance->update([
                'isPushed' => true,
                'user_id' => $user_id,
            ]);
        });

//        ////////////////// account section ////////////////
//        if ($advance)
//        {
//            $accountTransaction = AccountTransaction::where(
//                [
//                    'supplier_id'=> $advance->supplier_id,
//                    'createdDate' => date('Y-m-d'),
//                ])->first();
//            if (!is_null($accountTransaction)) {
//                if ($accountTransaction->createdDate != date('Y-m-d')) {
//                    $totalCredit = $advance->Amount;
//                }
//                else
//                {
//                    $totalCredit = $accountTransaction->Credit + $advance->Amount;
//                }
//                $difference = $accountTransaction->Differentiate + $advance->Amount;
//            }
//            else
//            {
//                $accountTransaction = AccountTransaction::where(
//                    [
//                        'supplier_id'=> $advance->supplier_id,
//                    ])->get();
//                $totalCredit = $advance->Amount;
//                $difference = $accountTransaction->last()->Differentiate + $advance->Amount;
//            }
//            $AccData =
//                [
//                    'supplier_id' => $advance->supplier_id,
//                    'Credit' => $totalCredit,
//                    'Differentiate' => $difference,
//                    'createdDate' => date('Y-m-d'),
//                    'user_id' => $user_id,
//                ];
//            $AccountTransactions = AccountTransaction::updateOrCreate(
//                [
//                    'createdDate'   => date('Y-m-d'),
//                    'supplier_id'   => $advance->supplier_id,
//                ],
//                $AccData);
//            //return Response()->json($AccountTransactions);
//            // return Response()->json("");
//        }
//        ////////////////// end of account section ////////////////
        return redirect()->route('supplier_advances.index')->with('pushed','Your Account Debit Successfully');
    }

    public function CheckSupplierAdvanceReferenceExist($request)
    {
        $data = SupplierAdvance::where('company_id',session('company_id'))->where('receiptNumber','like','%'.$request->receiptNumber.'%')->get();
        $data1 = SupplierPayment::where('company_id',session('company_id'))->where('referenceNumber','like','%'.$request->receiptNumber.'%')->get();
        if($data->first() || $data1->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function getSupplierAdvanceDetail($Id)
    {
        $payment=SupplierAdvance::with(['supplier'])->where('id',$Id)->first();
        $payment_detail=SupplierAdvanceDetail::with(['purchase','purchase.purchase_details'])->where('supplier_advances_id',$Id)->get();
        $html='<div class="row"><div class="col-md-12"><label>Supplier Name : '.$payment->supplier->Name.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Date : '.date('d-M-Y',strtotime($payment->TransferDate)).'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Type : '.$payment->paymentType.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Reference No. : '.$payment->receiptNumber.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Amount : '.$payment->Amount.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Amount (in words) : '.$payment->sumOf.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Description : '.$payment->Description.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Disbursed Amount  : '.$payment->spentBalance.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Remaining Amount : '.$payment->remainingBalance.'</label></div></div>';
        $html.='<div class="row text-right"><div class="col-md-12"><label>(created by : '.$payment->user->name.'-'.$payment->created_at.')</label></div></div>';
        $html.='<table class="table table-sm"><thead><th>SR</th><th>Disbursed</th><th>Purchase Date</th><th>PAD</th><th>Total</th><th>Paid</th><th>Balance</th></thead><tbody>';
        $i=0;
        foreach ($payment_detail as $item)
        {
            $html.='<tr>';
            $html.='<td>'.++$i.'</td>';
            $html.='<td>'.$item->amountPaid??"NA".'</td>';
            $html.='<td>'.date('d-M-Y',strtotime($item->purchase->PurchaseDate))??"NA".'</td>';
            $html.='<td>'.$item->purchase->purchase_details[0]->PadNumber??"NA".'</td>';
            $html.='<td>'.$item->purchase->grandTotal??"NA".'</td>';
            $html.='<td>'.$item->purchase->paidBalance??"NA".'</td>';
            $html.='<td>'.$item->purchase->remainingBalance??"NA".'</td>';
            $html.='</tr>';
        }
        $html.='</tbody>';
        return Response()->json($html);
    }

    public function cancelSupplierAdvance($id)
    {
        $response=false;
        DB::transaction(function () use($id,&$response){
            $company_id = session('company_id');
            $payment=SupplierAdvance::with(['supplier_advance_details'])->where('id',$id)->first();

            if($payment)
            {
                if($payment->isPushed==0)
                {
                    $payment->update(['user_id'=>session('user_id')]);
                    SupplierAdvance::where('id', array($id))->delete();
                    $response=true;
                }
                elseif($payment->isPushed==1)
                {
                    if($payment->IsSpent!=0 and $payment->IsPartialSpent!=0)
                    {
                        if($payment->paymentType == 'cash')
                        {
                            $description_string='SupplierCashAdvance|'.$id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $previous_probable_cash_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_cash_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'bank')
                        {
                            $description_string='SupplierBankAdvance|'.$id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'cheque')
                        {
                            $description_string='SupplierChequeAdvance|'.$id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }

                        $payment->update(['user_id'=>session('user_id')]);
                        SupplierAdvance::where('id', array($id))->delete();
                        $response=true;
                    }
                    else
                    {
                        foreach($payment->supplier_advance_details as $single)
                        {
                            $purchase=Purchase::where('id',$single->purchase_id)->get()->first();
                            $remaining_paid_balance=$purchase->paidBalance-$single->amountPaid;
                            $updated_remaining_balance=$purchase->remainingBalance+$single->amountPaid;

                            $TermsAndCondition=0;
                            $supplierNote=0;
                            if($remaining_paid_balance==0.00)
                            {
                                $is_paid=0;
                                $is_partial_paid=0;
                                $TermsAndCondition=1;
                                $supplierNote=1;
                            }
                            else
                            {
                                $is_paid=0;
                                $is_partial_paid=1;
                            }

                            $purchase->update([
                                'paidBalance'=>$remaining_paid_balance,
                                'remainingBalance'=>$updated_remaining_balance,
                                'Description'=>null,
                                'IsPaid'=>$is_paid,
                                'IsPartialPaid'=>$is_partial_paid,
                                'TermsAndCondition'=>$TermsAndCondition,
                                'supplierNote'=>$supplierNote,
                            ]);

                        }
                        if($payment->paymentType == 'cash')
                        {
                            $description_string='SupplierCashAdvance|'.$id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $previous_probable_cash_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_cash_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'bank')
                        {
                            $description_string='SupplierBankAdvance|'.$id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'cheque')
                        {
                            $description_string='SupplierChequeAdvance|'.$id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }

                        $payment->update(['user_id'=>session('user_id')]);
                        SupplierAdvance::where('id', array($id))->delete();
                        $response=true;
                    }
                }
            }
        });
        return Response()->json($response);
    }

    public function supplier_advance_delete_post(Request $request)
    {
        $response=false;
        DB::transaction(function () use($request,&$response){
            $company_id = session('company_id');
            $payment=SupplierAdvance::with(['supplier_advance_details'])->where('id',$request->payment_id)->first();

            if($payment)
            {
                if($payment->isPushed==0)
                {
                    $payment->update(['user_id'=>session('user_id')]);
                    SupplierAdvance::where('id', array($request->payment_id))->delete();
                    $response=true;
                }
                elseif($payment->isPushed==1)
                {
                    if($payment->IsSpent!=0 and $payment->IsPartialSpent!=0)
                    {
                        if($payment->paymentType == 'cash')
                        {
                            $description_string='SupplierCashAdvance|'.$request->payment_id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $previous_probable_cash_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_cash_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'bank')
                        {
                            $description_string='SupplierBankAdvance|'.$request->payment_id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'cheque')
                        {
                            $description_string='SupplierChequeAdvance|'.$request->payment_id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }

                        $payment->update(['user_id'=>session('user_id')]);
                        SupplierAdvance::where('id', array($request->payment_id))->delete();
                        $response=true;
                    }
                    else
                    {
                        foreach($payment->supplier_advance_details as $single)
                        {
                            $purchase=Purchase::where('id',$single->purchase_id)->get()->first();
                            $remaining_paid_balance=$purchase->paidBalance-$single->amountPaid;
                            $updated_remaining_balance=$purchase->remainingBalance+$single->amountPaid;

                            $TermsAndCondition=0;
                            $supplierNote=0;
                            if($remaining_paid_balance==0.00)
                            {
                                $is_paid=0;
                                $is_partial_paid=0;
                                $TermsAndCondition=1;
                                $supplierNote=1;
                            }
                            else
                            {
                                $is_paid=0;
                                $is_partial_paid=1;
                            }

                            $purchase->update([
                                'paidBalance'=>$remaining_paid_balance,
                                'remainingBalance'=>$updated_remaining_balance,
                                'Description'=>null,
                                'IsPaid'=>$is_paid,
                                'IsPartialPaid'=>$is_partial_paid,
                                'TermsAndCondition'=>$TermsAndCondition,
                                'supplierNote'=>$supplierNote,
                            ]);

                        }
                        if($payment->paymentType == 'cash')
                        {
                            $description_string='SupplierCashAdvance|'.$request->payment_id;
                            $previous_probable_cash_entry = CashTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_cash_entry)
                            {
                                $previous_probable_cash_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_cash_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'bank')
                        {
                            $description_string='SupplierBankAdvance|'.$request->payment_id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }
                        elseif ($payment->paymentType == 'cheque')
                        {
                            $description_string='SupplierChequeAdvance|'.$request->payment_id;
                            $previous_probable_bank_entry = BankTransaction::where('company_id','=',$company_id)->where('Details','like',$description_string)->get()->first();
                            if($previous_probable_bank_entry)
                            {
                                $previous_probable_bank_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_bank_entry->delete();
                            }

                            $previous_probable_account_entry = AccountTransaction::where('company_id','=',$company_id)->where('Description','like',$description_string)->get()->first();
                            if($previous_probable_account_entry)
                            {
                                $previous_probable_account_entry->update(['user_id'=>session('user_id')]);
                                $previous_probable_account_entry->delete();
                            }
                        }

                        SupplierAdvanceDetail::where('supplier_advances_id',$request->payment_id)->update(['user_id'=>session('user_id')]);
                        SupplierAdvanceDetail::where('supplier_advances_id',$request->payment_id)->delete();
                        $payment->update(['user_id'=>session('user_id')]);
                        SupplierAdvance::where('id', array($request->payment_id))->delete();
                        $response=true;

                        $update_note = new UpdateNote();
                        $update_note->RelationTable = 'supplier_advances';
                        $update_note->RelationId = $request->payment_id;
                        $update_note->UpdateDescription = $request->deleteDescription;
                        $update_note->user_id = session('user_id');
                        $update_note->company_id = $company_id;
                        $update_note->save();
                    }
                }
            }
        });
        return Response()->json($response);
    }
}
