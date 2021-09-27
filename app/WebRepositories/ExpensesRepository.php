<?php


namespace App\WebRepositories;


use App\Http\Requests\ExpenseRequest;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\FileUpload;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IExpensesRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class ExpensesRepository implements IExpensesRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Expense::with('expense_details.expense_category','supplier')->where('company_id',session('company_id'))->latest()->get())
                ->addColumn('action', function ($data) {
                    /*$button = '<form action="'.route('expenses.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';*/
                    $button = '<a href="'.route('expenses.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    return $button;
                })
                ->addColumn('expenseCategory', function($data) {
                    return $data->expense_details[0]->expense_category->Name ?? "No Data";
                })
                ->addColumn('supplier', function($data) {
                    return $data->supplier->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'referenceNumber',
                    'subTotal',
                    'totalVat',
                    'grandTotal',
                    'expenseDate',
                    'supplier',
                ])
                ->make(true);
        }
        //$expenses = Expense::with('expense_details.expense_category','supplier')->where('company_id',session('company_id'))->get();
        return view('admin.expense.index');
    }

    public function all_expenses(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'expenseDate',
            2=> 'supplier_id',
            3=> 'id',
        );

        $totalData = Expense::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select e.id,e.company_id,e.supplier_id,e.expenseDate,e.subTotal,e.totalVat,e.grandTotal,e.payment_type,s.Name,e.referenceNumber,ed.cat_name,e.termsAndCondition from expenses as e left join suppliers as s on s.id = e.supplier_id join (SELECT expense_details.*,ec.Name as cat_name FROM expense_details join expense_categories as ec on expense_details.expense_category_id=ec.id WHERE expense_details.deleted_at is null) as ed on e.id = ed.expense_id where e.company_id = '.session('company_id').' and e.isActive = 1 and e.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $expenses = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');
            $sql = 'select e.id,e.company_id,e.supplier_id,e.expenseDate,e.subTotal,e.totalVat,e.grandTotal,e.payment_type,s.Name,e.referenceNumber,ed.cat_name,e.termsAndCondition from expenses as e left join suppliers as s on s.id = e.supplier_id join (SELECT expense_details.*,ec.Name as cat_name FROM expense_details join expense_categories as ec on expense_details.expense_category_id=ec.id WHERE expense_details.deleted_at is null) as ed on e.id = ed.expense_id where e.company_id = '.session('company_id').' and e.referenceNumber LIKE "%'.$search.'%" '.' and e.isActive = 1 and e.deleted_at is null order by id desc limit '.$limit.' offset '.$start;
            $expenses = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,e.id,e.company_id,e.supplier_id,e.expenseDate,e.subTotal,e.totalVat,e.grandTotal,s.Name,e.referenceNumber,ed.cat_name from expenses as e left join suppliers as s on s.id = e.supplier_id join (SELECT expense_details.*,ec.Name as cat_name FROM expense_details join expense_categories as ec on expense_details.expense_category_id=ec.id WHERE expense_details.deleted_at is null) as ed on e.id = ed.expense_id where e.company_id = '.session('company_id').' and e.referenceNumber LIKE "%'.$search.'%" '.' and e.isActive = 1 and e.deleted_at is null  order by id desc limit '.$limit.' offset '.$start;
            $expense_count = DB::select(DB::raw($sql_count));
            if(!empty($expense_count))
            {
                $totalFiltered = $expense_count[0]->TotalCount;
            }
        }

        $data = array();
        if(!empty($expenses))
        {
            foreach ($expenses as $expense)
            {
                $nestedData['id'] = $expense->id;
                $nestedData['expenseDate'] = date('d-m-Y', strtotime($expense->expenseDate));
                $nestedData['supplier'] = $expense->Name ?? "No Name";
                $nestedData['referenceNumber'] = $expense->referenceNumber ?? "No referenceNumber";
                $nestedData['expenseCategory'] = $expense->cat_name ?? "No Number";
                $nestedData['subTotal'] = $expense->subTotal ?? 0.00;
                $nestedData['totalVat'] = $expense->totalVat ?? 0.00;
                $nestedData['grandTotal'] = $expense->grandTotal ?? 0.00;
                $nestedData['payment_type'] = $expense->payment_type ?? "N.A.";
                //$nestedData['payment_type'] = $expense->grandTotal ?? 0.00;
                //$nestedData['action'] = '<a href="'.route('expenses.edit', $expense->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>&nbsp;<button class="btn btn-dark" onclick="show_detail(this.id)" type="button" id="show_'.$expense->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>&nbsp;<a href="'.url('Expense_delete', $expense->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                $button='';
                if($expense->termsAndCondition==1)
                {
                    $button .= '<a href="'.route('expenses.edit', $expense->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                }
                $button .= '&nbsp;';
                $button .='&nbsp;<button class="btn btn-dark" onclick="show_detail(this.id)" type="button" id="show_'.$expense->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                $button .= '&nbsp;';
                $button.='<a href="#" data-id="'.$expense->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                $nestedData['action']=$button;
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

    public function getExpenseDetail($Id)
    {
        $base=URL::to('/storage/app/public/expense_images/');
        $expense=Expense::with(['supplier','api_employee','user','expense_images'])->where('id',$Id)->first();
        $expense_detail=ExpenseDetail::with(['expense_category'])->where('expense_id',$Id)->get();
/*        $html='<div class="row"><div class="col-md-12"><label>Supplier Name : '.$expense->supplier->Name.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Expense Date : '.date('d-M-Y',strtotime($expense->expenseDate)).'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Payment Type : '.$expense->payment_type.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Reference No. : '.$expense->referenceNumber.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Category : '.$expense_detail[0]->expense_category->Name??"NA".'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Description : '.$expense_detail[0]->Description.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Amount : '.$expense->subTotal.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>VAT : '.$expense->totalVat.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Grand Total : '.$expense->grandTotal.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Employee  : '.$expense->api_employee->Name.'</label></div></div>';*/

        $html='<table class="table table-sm"><tbody>';
        $html.='<tr class="bg-success"><td>Reference No </td><td>'.$expense->referenceNumber.'</td></tr>';
        $html.='<tr><td>Supplier Name </td><td>'.$expense->supplier->Name.'</td></tr>';
        $html.='<tr><td>Expense Date </td><td>'.date('d-M-Y',strtotime($expense->expenseDate)).'</td></tr>';
        $html.='<tr><td>Payment Type </td><td>'.$expense->payment_type.'</td></tr>';
        $html.='<tr><td>Category </td><td>'.$expense_detail[0]->expense_category->Name??"NA".'</td></tr>';
        $html.='<tr><td>Description </td><td>'.$expense_detail[0]->Description.'</td></tr>';
        $html.='<tr><td>Amount </td><td>'.$expense->subTotal.'</td></tr>';
        $html.='<tr><td>VAT </td><td>'.$expense->totalVat.'</td></tr>';
        $html.='<tr><td>Grand Total </td><td>'.$expense->grandTotal.'</td></tr>';
        $html.='<tr><td>Employee </td><td>'.$expense->api_employee->Name.'</td></tr>';
        $html.='<div class="row text-right"><div class="col-md-12"><label>(created by : '.$expense->user->name.'-'.$expense->created_at.')</label></div></div>';
        $html.='</tbody></table>';
        foreach ($expense->expense_images as $item)
        {
            $this_image_url=$base.'/'.$item->Title;
            $file_type=explode('.',$item->Title);
            if(isset($file_type[1]) && $file_type[1]=='jpg' || $file_type[1]=='JPG' || $file_type[1]=='JPEG' || $file_type[1]=='jpeg' || $file_type[1]=='PNG' || $file_type[1]=='png')
            {
                $html.='<img src="'.$this_image_url.'" class="img-fluid" alt="not able to load image">';
            }
            else
            {
                $html.='<a href="'.$this_image_url.'" target="_blank"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i> View Document </a>';
            }
            unset($this_image_url);
        }
        return Response()->json($html);
    }

    public function create()
    {
        $expenseNo = $this->invoiceNumber();
        $PadNumber = $this->PadNumber();
        $suppliers = Supplier::all()->where('company_type_id','=',3);
        $employees = Employee::all();
        $expense_categories = ExpenseCategory::all();
        $banks = Bank::where('company_id',session('company_id'))->get();
        $expenseRecords = Expense::with('expense_details.expense_category','supplier')->where('company_id',session('company_id'))->orderBy('id', 'desc')->skip(0)->take(3)->get();
        return view('admin.expense.create',compact('suppliers','expenseNo','employees','expense_categories','PadNumber','banks','expenseRecords'));
    }

    public function CheckExpenseReferenceExist($request)
    {
        $data = Expense::where('referenceNumber','=',$request->referenceNumber)->get();
        if($data->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function store(ExpenseRequest $expenseRequest)
    {
        DB::transaction(function () use($expenseRequest)
        {
            if($expenseRequest['insert']!='')
            {
                $expense_data=json_decode($expenseRequest['insert']);
                //check reference number already exist or not
                $already_exist = Expense::where('company_id',session('company_id'))->where('referenceNumber',$expense_data->referenceNumber)->get();
                if(!$already_exist->isEmpty())
                {
                    $data=array('result'=>false,'message'=>'Reference NUMBER ALREADY EXIST');
                    echo json_encode($data);exit();
                }

                $user_id = session('user_id');
                $company_id = session('company_id');

                $expense = new Expense();
                $expense->expenseNumber = $expense_data->expenseNumber;
                $expense->referenceNumber = preg_replace("/\s+/", "", $expense_data->referenceNumber);
                $expense->expenseDate = $expense_data->expenseDate;
                $expense->Total = $expense_data->Total;
                $expense->subTotal = $expense_data->subTotal;
                $expense->totalVat = $expense_data->totalVat;
                $expense->grandTotal = $expense_data->grandTotal;
                $expense->paidBalance = $expense_data->grandTotal;
                $expense->remainingBalance = 0.00;

                $expense->payment_type = $expense_data->payment_type;
                if($expense_data->payment_type!='cash')
                {
                    $expense->bank_id = $expense_data->bank_id;
                    $expense->accountNumber = $expense_data->accountNumber;
                    $expense->transferDate = $expense_data->transferDate;
                    $expense->ChequeNumber = $expense_data->ChequeNumber;
                }
                $expense->termsAndCondition = 1;
                $expense->supplier_id = $expense_data->supplier_id;
                $expense->employee_id = $expense_data->employee_id;
                $expense->user_id = $user_id;
                $expense->company_id = $company_id;
                $expense->save();
                $expense = $expense->id;

                $expense_cat_update_flag=0;
                foreach($expense_data->orders as $detail)
                {
                    $data =  ExpenseDetail::create([
                        "Total"        => $detail->Total,
                        "expenseDate"        => $expense_data->expenseDate,
                        "expense_category_id"        => $detail->expense_category_id,
                        "Description"        => $detail->description,
                        "Vat"        => $detail->Vat,
                        "rowVatAmount"        => $detail->rowVatAmount,
                        "rowSubTotal"        => $detail->rowSubTotal,
                        "company_id" => $company_id,
                        "user_id"      => $user_id,
                        "expense_id"      => $expense,
                        "PadNumber" => $detail->padNumber,
                    ]);
                    $expense_cat_update_flag=$detail->expense_category_id;
                }
                if($expense_cat_update_flag==31 || $expense_cat_update_flag==12)
                {
                    // need to update non editable flag
                    $exp=Expense::where('id',$expense)->first();
                    $exp->update(['termsAndCondition'=>0]);
                    //make an account transaction entry
                    $AccData =[
                        'employee_id' => $expense_data->employee_id,
                        'Credit' => $expense_data->orders[0]->rowSubTotal,
                        'Debit' => 0.00,
                        'createdDate' => $expense_data->expenseDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'CashExpense|'.$expense,
                        'referenceNumber'=>preg_replace("/\s+/", "", $expense_data->referenceNumber),
                        'TransactionDesc'=>$expense_data->orders[0]->description,
                    ];
                    $AccountTransactions = AccountTransaction::Create($AccData);
                }

                $accountDescriptionString='';
                if($expense_data->payment_type=='cash')
                {
                    $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference=$expense;
                    $cash_transaction->createdDate=$expense_data->expenseDate;
                    $cash_transaction->Type='expenses';
                    $cash_transaction->Details='CashExpense|'.$expense;
                    $cash_transaction->Credit=$expense_data->grandTotal;
                    $cash_transaction->Debit=0.00;
                    $cash_transaction->Differentiate=$difference-$expense_data->grandTotal;
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->PadNumber = $expense_data->referenceNumber;
                    $cash_transaction->save();

                    $accountDescriptionString='CashExpense|';
                }
                else
                {
                    if($expense_data->payment_type=='bank')
                    {
                        $bankTransaction = BankTransaction::where(['bank_id'=> $expense_data->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$expense;
                        $bank_transaction->createdDate=$expense_data->transferDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='BankTransferExpense|'.$expense;
                        $bank_transaction->Credit=$expense_data->grandTotal;
                        $bank_transaction->Debit=0.00;
                        $bank_transaction->Differentiate=$difference-$expense_data->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expense_data->bank_id;
                        $bank_transaction->updateDescription = $expense_data->ChequeNumber;
                        $bank_transaction->save();

                        $accountDescriptionString='BankTransferExpense|';
                    }
                    elseif($expense_data->payment_type=='cheque')
                    {
                        $bankTransaction = BankTransaction::where(['bank_id'=> $expense_data->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$expense;
                        $bank_transaction->createdDate=$expense_data->transferDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='ChequeExpense|'.$expense;
                        $bank_transaction->Credit=$expense_data->grandTotal;
                        $bank_transaction->Debit=0.00;
                        $bank_transaction->Differentiate=$difference-$expense_data->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expense_data->bank_id;
                        $bank_transaction->updateDescription = $expense_data->ChequeNumber;
                        $bank_transaction->save();

                        $accountDescriptionString='ChequeExpense|';
                    }
                }

                ////////////////// start account section gautam ////////////////
                if ($expense)
                {
                    $accountTransaction = AccountTransaction::where(['supplier_id'=> $expense_data->supplier_id])->get();

                    // fully paid with cash or bank

                    $totalCredit = $expense_data->grandTotal;
                    $difference = $accountTransaction->last()->Differentiate + $expense_data->grandTotal;

                    //make credit entry for the expense
                    $AccountTransactions=AccountTransaction::Create([
                        'supplier_id' => $expense_data->supplier_id,
                        'Credit' => $totalCredit,
                        'Debit' => 0.00,
                        'Differentiate' => $difference,
                        'createdDate' => $expense_data->expenseDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'Description'=>'Expense|'.$expense,
                        'referenceNumber'=>$expense_data->referenceNumber,
                    ]);

                    //make debit entry for the whatever cash or bank account is credited
                    $difference=$difference-$expense_data->grandTotal;
                    $AccountTransactions=AccountTransaction::Create([
                        'supplier_id' => $expense_data->supplier_id,
                        'Credit' => 0.00,
                        'Debit' => $expense_data->grandTotal,
                        'Differentiate' => $difference,
                        'createdDate' => $expense_data->expenseDate,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                        'referenceNumber'=>$expense_data->referenceNumber ?? '',
                        'Description'=>$accountDescriptionString.$expense,
                        'updateDescription'=>$expense_data->ChequeNumber ?? '',
                    ]);
                    //return Response()->json($AccountTransactions);
                    $data=array('result'=>true,'message'=>'Record Inserted Successfully.');
                    echo json_encode($data);
                }
                ////////////////// end account section gautam ////////////////

                // image upload section
                if($expenseRequest['TotalFiles'] > 0)
                {
                    for ($x = 0; $x < $expenseRequest['TotalFiles']; $x++)
                    {
                        if ($expenseRequest->hasFile('files'.$x))
                        {
                            $file = $expenseRequest->file('files'.$x);
                            $extension = $file->getClientOriginalExtension();
                            $filename=uniqid('exp_'.$expense.'_').'.'.$extension;
                            $expenseRequest->file('files'.$x)->storeAs('expense_images', $filename,'public');

                            FileUpload::create([
                                "Title" => $filename,
                                "RelationTable" => 'expenses',
                                "RelationId" => $expense,
                                "user_id" => $user_id,
                                "company_id" => $company_id,
                            ]);
                        }
                    }
                }
                // image upload section
            }
            //return false;
        });

    }

    public function update(Request $request, $Id)
    {
        //echo "<pre>";print_r($request->all());die;
        DB::transaction(function () use($request,$Id){

            if($request['insert']!='')
            {
                $expense_data=json_decode($request['insert']);

                $expensed = Expense::find($Id);
                $user_id = session('user_id');
                $company_id = session('company_id');
                $cash_ref_update_flag=false;

                ////////////////// account section gautam ////////////////
                $accountTransaction = AccountTransaction::where(['supplier_id'=> $expensed->supplier_id,])->get();
                if (!is_null($accountTransaction))
                {
                    // identify only and only payment method or date is changing
                    if($expensed->payment_type!=$expense_data->payment_type ||  $expensed->expenseDate!=$expense_data->expenseDate &&$expensed->supplier_id==$expense_data->supplier_id && $expensed->grandTotal==$expense_data->grandTotal)
                    {
                        $cash_ref_update_flag=true;
                        // start reverse entry for wrong payment method
                        if($expensed->payment_type=='cash')
                        {
                            $description_string='CashExpense|'.$Id;
                            $cash_transaction=CashTransaction::where('Type','expenses')->where('Reference',$Id)->where('company_id',$company_id)->first();
                            $cash_transaction->update(['user_id'=>$user_id]);
                            $cash_transaction->delete();

                        }
                        elseif($expensed->payment_type=='bank')
                        {
                            $description_string='BankTransferExpense|'.$Id;
                            $bank_transaction=BankTransaction::where('Type','expenses')->where('Reference',$Id)->where('company_id',$company_id)->first();
                            $bank_transaction->update(['user_id'=>$user_id]);
                            $bank_transaction->delete();
                        }
                        elseif($expensed->payment_type=='cheque')
                        {
                            $description_string='ChequeExpense|'.$Id;
                            $bank_transaction=BankTransaction::where('Type','expenses')->where('Reference',$Id)->where('company_id',$company_id)->first();
                            $bank_transaction->update(['user_id'=>$user_id]);
                            $bank_transaction->delete();
                        }
                        $previous_entry = AccountTransaction::get()->where('company_id','=',$company_id)->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                        if($previous_entry)
                        {
                            $new_description_string='';
                            $new_update_description='';
                            if($expense_data->payment_type=='cash')
                            {
                                $new_description_string='CashExpense|'.$Id;
                                $new_update_description=$expense_data->referenceNumber;

                                $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                                $difference = $cashTransaction->last()->Differentiate;
                                $cash_transaction = new CashTransaction();
                                $cash_transaction->Reference=$Id;
                                $cash_transaction->createdDate=$expense_data->expenseDate;
                                $cash_transaction->Type='expenses';
                                $cash_transaction->Details='CashExpense|'.$Id;
                                $cash_transaction->Credit=$expense_data->grandTotal;
                                $cash_transaction->Debit=0.00;
                                $cash_transaction->Differentiate=$difference-$expense_data->grandTotal;
                                $cash_transaction->user_id = $user_id;
                                $cash_transaction->company_id = $company_id;
                                $cash_transaction->PadNumber = $expense_data->referenceNumber;
                                $cash_transaction->save();
                            }
                            elseif($expense_data->payment_type=='bank')
                            {
                                $new_description_string='BankTransferExpense|'.$Id;
                                $new_update_description=$expense_data->ChequeNumber;

                                $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
                                $difference = $bankTransaction->last()->Differentiate;
                                $bank_transaction = new BankTransaction();
                                $bank_transaction->Reference=$Id;
                                $bank_transaction->createdDate=$expense_data->transferDate ?? date('Y-m-d h:i:s');
                                $bank_transaction->Type='expenses';
                                $bank_transaction->Details='BankTransferExpense|'.$Id;
                                $bank_transaction->Credit=$expense_data->grandTotal;
                                $bank_transaction->Debit=0.00;
                                $bank_transaction->Differentiate=$difference-$expense_data->grandTotal;
                                $bank_transaction->user_id = $user_id;
                                $bank_transaction->company_id = $company_id;
                                $bank_transaction->bank_id = $request->Data['bank_id'];
                                $bank_transaction->updateDescription = $expense_data->ChequeNumber;
                                $bank_transaction->save();
                            }
                            elseif($expense_data->payment_type=='cheque')
                            {
                                $new_description_string='ChequeExpense|'.$Id;
                                $new_update_description=$expense_data->ChequeNumber;

                                $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
                                $difference = $bankTransaction->last()->Differentiate;
                                $bank_transaction = new BankTransaction();
                                $bank_transaction->Reference=$Id;
                                $bank_transaction->createdDate=$expense_data->transferDate ?? date('Y-m-d h:i:s');
                                $bank_transaction->Type='expenses';
                                $bank_transaction->Details='ChequeExpense|'.$Id;
                                $bank_transaction->Credit=$expense_data->grandTotal;
                                $bank_transaction->Debit=0.00;
                                $bank_transaction->Differentiate=$difference-$expense_data->grandTotal;
                                $bank_transaction->user_id = $user_id;
                                $bank_transaction->company_id = $company_id;
                                $bank_transaction->bank_id = $request->Data['bank_id'];
                                $bank_transaction->updateDescription = $expense_data->ChequeNumber;
                                $bank_transaction->save();
                            }
                            $previous_entry->update(
                                [
                                    'Description' => $new_description_string,
                                    'updateDescription' => $new_update_description,
                                ]);
                        }
                    }
                    // identify only payment method is not changing
                    elseif($expensed->payment_type!=$expense_data->payment_type || $expensed->supplier_id!=$expense_data->supplier_id || $expensed->grandTotal!=$expense_data->grandTotal)
                    {
                        $cash_ref_update_flag=true;
                        $description_string='Expense|'.$Id;
                        $previous_entry = AccountTransaction::get()->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                        $last_closing = $accountTransaction->last()->Differentiate;
                        $previously_credited = $previous_entry->Credit;
                        $AccData =
                            [
                                'supplier_id' => $expensed->supplier_id,
                                'Debit' => $previously_credited,
                                'Credit' => 0.00,
                                'Differentiate' => $last_closing-$previously_credited,
                                'createdDate' => $expense_data->expenseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Expense|'.$Id,
                                'updateDescription'=>'hide',
                                'referenceNumber'=>$expensed->referenceNumber,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                        // also hide previous entry start
                        AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                        // also hide previous entry end

                        if($expensed->payment_type=='cash')
                        {
                            $description_string='CashExpense|'.$Id;
                            $cash_transaction=CashTransaction::where('Type','expenses')->where('Reference',$Id)->where('company_id',$company_id)->first();
                            $cash_transaction->update(['user_id'=>$user_id]);
                            $cash_transaction->delete();

                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $expensed->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $AccData =
                                [
                                    'supplier_id' => $expensed->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing+$previously_debited,
                                    'createdDate' => $expense_data->expenseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'CashExpense|'.$Id,
                                    'updateDescription'=>'hide',
                                    'referenceNumber'=>$expensed->referenceNumber,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                        }
                        elseif($expensed->payment_type=='bank')
                        {
                            $description_string='BankTransferExpense|'.$Id;

                            $bank_transaction=BankTransaction::where('Type','expenses')->where('Reference',$Id)->where('company_id',$company_id)->first();
                            $bank_transaction->update(['user_id'=>$user_id]);
                            $bank_transaction->delete();

                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $expensed->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $AccData =
                                [
                                    'supplier_id' => $expensed->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing+$previously_debited,
                                    'createdDate' => $expense_data->expenseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'BankTransferExpense|'.$Id,
                                    'updateDescription'=>'hide',
                                    'referenceNumber'=>$expensed->referenceNumber,

                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                        }
                        elseif($expensed->payment_type=='cheque')
                        {
                            $description_string='ChequeExpense|'.$Id;

                            $bank_transaction=BankTransaction::where('Type','expenses')->where('Reference',$Id)->where('company_id',$company_id)->first();
                            $bank_transaction->update(['user_id'=>$user_id]);
                            $bank_transaction->delete();

                            $previous_entry = AccountTransaction::get()->where('supplier_id','=',$expensed->supplier_id)->where('Description','like',$description_string)->last();
                            $previously_debited = $previous_entry->Debit;
                            $accountTransaction = AccountTransaction::where(['supplier_id'=> $expensed->supplier_id,])->get();
                            $last_closing = $accountTransaction->last()->Differentiate;
                            $AccData =
                                [
                                    'supplier_id' => $expensed->supplier_id,
                                    'Debit' => 0.00,
                                    'Credit' => $previously_debited,
                                    'Differentiate' => $last_closing+$previously_debited,
                                    'createdDate' => $expense_data->expenseDate,
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description'=>'ChequeExpense|'.$Id,
                                    'updateDescription'=>'hide',
                                    'referenceNumber'=>$expensed->referenceNumber,
                                ];
                            $AccountTransactions = AccountTransaction::Create($AccData);
                            // also hide previous entry start
                            AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                            // also hide previous entry end
                        }

                        // new entry start
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $expense_data->supplier_id,])->get();
                        $difference = $accountTransaction->last()->Differentiate + $expense_data->grandTotal;
                        $AccData =
                            [
                                'supplier_id' => $expense_data->supplier_id,
                                'Credit' => $expense_data->grandTotal,
                                'Debit' => 0.00,
                                'Differentiate' => $difference,
                                'createdDate' => $expense_data->expenseDate,
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description'=>'Expense|'.$Id,
                                'referenceNumber'=>$expense_data->referenceNumber,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);

                        $new_description_string='';
                        $new_update_description='';
                        if($expense_data->payment_type=='cash')
                        {
                            $new_description_string='CashExpense|'.$Id;
                            $new_update_description=$expense_data->referenceNumber;

                            $cashTransaction = CashTransaction::where(['company_id'=> $company_id])->get();
                            $difference = $cashTransaction->last()->Differentiate;
                            $cash_transaction = new CashTransaction();
                            $cash_transaction->Reference=$Id;
                            $cash_transaction->createdDate=$expense_data->expenseDate;
                            $cash_transaction->Type='expenses';
                            $cash_transaction->Details='CashExpense|'.$Id;
                            $cash_transaction->Credit=$expense_data->grandTotal;
                            $cash_transaction->Debit=0.00;
                            $cash_transaction->Differentiate=$difference-$expense_data->grandTotal;
                            $cash_transaction->user_id = $user_id;
                            $cash_transaction->company_id = $company_id;
                            $cash_transaction->PadNumber = $expense_data->referenceNumber;
                            $cash_transaction->save();
                        }
                        elseif($expense_data->payment_type=='bank')
                        {
                            $new_description_string='BankTransferExpense|'.$Id;
                            $new_update_description=$expense_data->ChequeNumber;

                            $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
                            $difference = $bankTransaction->last()->Differentiate;
                            $bank_transaction = new BankTransaction();
                            $bank_transaction->Reference=$Id;
                            $bank_transaction->createdDate=$expense_data->transferDate ?? date('Y-m-d h:i:s');
                            $bank_transaction->Type='expenses';
                            $bank_transaction->Details='BankTransferExpense|'.$Id;
                            $bank_transaction->Credit=$expense_data->grandTotal;
                            $bank_transaction->Debit=0.00;
                            $bank_transaction->Differentiate=$difference-$expense_data->grandTotal;
                            $bank_transaction->user_id = $user_id;
                            $bank_transaction->company_id = $company_id;
                            $bank_transaction->bank_id = $request->Data['bank_id'];
                            $bank_transaction->updateDescription = $expense_data->ChequeNumber;
                            $bank_transaction->save();
                        }
                        elseif($expense_data->payment_type=='cheque')
                        {
                            $new_description_string='ChequeExpense|'.$Id;
                            $new_update_description=$expense_data->ChequeNumber;

                            $bankTransaction = BankTransaction::where(['bank_id'=> $request->Data['bank_id']])->get();
                            $difference = $bankTransaction->last()->Differentiate;
                            $bank_transaction = new BankTransaction();
                            $bank_transaction->Reference=$Id;
                            $bank_transaction->createdDate=$expense_data->transferDate ?? date('Y-m-d h:i:s');
                            $bank_transaction->Type='expenses';
                            $bank_transaction->Details='ChequeExpense|'.$Id;
                            $bank_transaction->Credit=$expense_data->grandTotal;
                            $bank_transaction->Debit=0.00;
                            $bank_transaction->Differentiate=$difference-$expense_data->grandTotal;
                            $bank_transaction->user_id = $user_id;
                            $bank_transaction->company_id = $company_id;
                            $bank_transaction->bank_id = $request->Data['bank_id'];
                            $bank_transaction->updateDescription = $expense_data->ChequeNumber;
                            $bank_transaction->save();
                        }
                        //make debit entry for the whatever cash or bank account is credited
                        $accountTransaction = AccountTransaction::where(['supplier_id'=> $expense_data->supplier_id,])->get();
                        $difference = $accountTransaction->last()->Differentiate - $expense_data->grandTotal;
                        $AccountTransactions=AccountTransaction::Create([
                            'supplier_id' => $expense_data->supplier_id,
                            'Credit' => 0.00,
                            'Debit' => $expense_data->grandTotal,
                            'Differentiate' => $difference,
                            'createdDate' => $expense_data->expenseDate,
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description'=>$new_description_string,
                            'updateDescription'=>$new_update_description ?? '',
                            'referenceNumber'=>$new_update_description ?? '',
                        ]);

                        //new entry end
                    }
                }
                ////////////////// end of account section gautam ////////////////

                if($cash_ref_update_flag==false)
                {
                    $description_string='Expense|'.$Id;
                    // find out relevant cash transaction entry and update pad number with incoming reference number
                    $cash_transaction=CashTransaction::where('company_id',session('company_id'))->where('Details','like','%'.$description_string.'%')->first();
                    $cash_transaction->update(['PadNumber' => preg_replace("/\s+/", "", $expense_data->referenceNumber),]);
                }
                //here will come cash transaction record update if scenario will come by
                $bank_id=0;
                $accountNumber=NULL;
                $transferDate=$expense_data->expenseDate;
                $ChequeNumber=NULL;
                if($expense_data->payment_type!='cash')
                {
                    $bank_id=$request->Data['bank_id'];
                    $accountNumber=$expense_data->accountNumber;
                    $transferDate=$expense_data->transferDate;
                    $ChequeNumber=$expense_data->ChequeNumber;
                }

                $expensed->update(
                    [
                        'expenseNumber' => $expense_data->expenseNumber,
                        'referenceNumber' => preg_replace("/\s+/", "", $expense_data->referenceNumber),
                        'expenseDate' => $expense_data->expenseDate,
                        'Total' => $expense_data->Total,
                        'subTotal' => $expense_data->subTotal,
                        'totalVat' => $expense_data->totalVat,
                        'grandTotal' => $expense_data->grandTotal,
                        'paidBalance' => $expense_data->grandTotal,
                        'remainingBalance' => 0.00,
                        'payment_type' => $expense_data->payment_type,
                        'bank_id' => $bank_id,
                        'accountNumber' => $accountNumber,
                        'transferDate' => $transferDate,
                        'ChequeNumber' => $ChequeNumber,
                        'supplier_id' => $expense_data->supplier_id,
                        'employee_id' => $expense_data->employee_id,
                        'user_id' => $user_id,
                        'company_id' => $company_id,
                    ]);

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'expenses';
                $update_note->RelationId = $Id;
                $update_note->Description = $expense_data->UpdateDescription;
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                $d = ExpenseDetail::where('expense_id', array($Id))->delete();
                $slct = ExpenseDetail::where('expense_id', $Id)->get();
                foreach ($expense_data->orders as $detail)
                {
                    /*$expenseDetails = ExpenseDetail::create([
                        "Total" => $detail['Total'],
                        "expenseDate" => $expense_data->expenseDate,
                        "expense_category_id" => $detail['expense_category_id'],
                        "Vat" => $detail['Vat'],
                        "rowVatAmount" => $detail['rowVatAmount'],
                        "rowSubTotal" => $detail['rowSubTotal'],
                        "company_id" => $company_id,
                        "user_id" => $user_id,
                        "expense_id" => $Id,
                        "PadNumber" => $detail['padNumber'],
                        "description" => $expense_data->orders[0]['Description'],
                    ]);*/

                    $data =  ExpenseDetail::create([
                        "Total"        => $detail->Total,
                        "expenseDate"        => $expense_data->expenseDate,
                        "expense_category_id"        => $detail->expense_category_id,
                        "Description"        => $detail->Description,
                        "Vat"        => $detail->Vat,
                        "rowVatAmount"        => $detail->rowVatAmount,
                        "rowSubTotal"        => $detail->rowSubTotal,
                        "company_id" => $company_id,
                        "user_id"      => $user_id,
                        "expense_id"      => $Id,
                        "PadNumber" => $detail->padNumber,
                    ]);
                }
            }

            // image upload section
            if($request['TotalFiles'] > 0)
            {
                for ($x = 0; $x < $request['TotalFiles']; $x++)
                {
                    if ($request->hasFile('files'.$x))
                    {
                        $file = $request->file('files'.$x);
                        $extension = $file->getClientOriginalExtension();
                        $filename=uniqid('exp_'.$Id.'_').'.'.$extension;
                        $request->file('files'.$x)->storeAs('expense_images', $filename,'public');

                        FileUpload::create([
                            "Title" => $filename,
                            "RelationTable" => 'expenses',
                            "RelationId" => $Id,
                            "user_id" => $user_id,
                            "company_id" => $company_id,
                        ]);
                    }
                }
            }
            // image upload section
        });
        $ss = ExpenseDetail::where('expense_id', $Id)->get();
        return Response()->json($ss);
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'expenses'])->get();
        $suppliers = Supplier::all();
        $employees = Employee::all();
        $expense_categories = ExpenseCategory::all();
        $expense_details = ExpenseDetail::withTrashed()->with('expense.supplier','user')->where('expense_id', $Id)->get();
        $banks = Bank::where('company_id',session('company_id'))->get();
        return view('admin.expense.edit',compact('expense_details','suppliers','update_notes','employees','expense_categories','banks'));
    }

    public function delete($Id)
    {
        DB::transaction(function () use($Id)
        {
            $expense = Expense::findOrFail($Id);
            $user_id = session('user_id');
            $company_id = session('company_id');
            if ($expense) {
                //cash expense
                if ($expense->bank_id == 0)
                {
                    /*$cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference = $expense->id;
                    $cash_transaction->createdDate = $expense->expenseDate;
                    $cash_transaction->Type = 'expenses';
                    $cash_transaction->Details = 'CashExpenseReversal|' . $expense->id;
                    $cash_transaction->Credit = 0.00;
                    $cash_transaction->Debit = $expense->grandTotal;
                    $cash_transaction->Differentiate = $difference + $expense->grandTotal;
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->PadNumber = $expense->referenceNumber;
                    $cash_transaction->save();*/

                    $cash_transaction=CashTransaction::where('Type','expenses')->where('Reference',$Id)->where('company_id',$company_id)->first();
                    $cash_transaction->update(['user_id'=>$user_id]);
                    $cash_transaction->delete();

                    AccountTransaction::where('Description','like','%'.'Expense|'.$expense->id.'%')->update(['updateDescription'=>'hide']);
                    $expense->update(['user_id'=>$user_id]);
                    $expense->delete();
                }
                else
                {
                    if($expense->payment_type=='bank')
                    {
                        /*$bankTransaction = BankTransaction::where(['bank_id'=> $expense->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$expense->id;
                        $bank_transaction->createdDate=$expense->expenseDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='BankTransferExpenseReversal|'.$expense->id;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$expense->grandTotal;
                        $bank_transaction->Differentiate=$difference+$expense->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expense->bank_id;
                        $bank_transaction->updateDescription = $expense->ChequeNumber;
                        $bank_transaction->save();*/

                        $bank_transaction=BankTransaction::where('Type','expenses')->where('Reference',$Id)->where('company_id',$company_id)->first();
                        $bank_transaction->update(['user_id'=>$user_id]);
                        $bank_transaction->delete();

                        AccountTransaction::where('Description','like','%'.'Expense|'.$expense->id.'%')->update(['updateDescription'=>'hide']);
                        $expense->update(['user_id'=>$user_id]);
                        $expense->delete();
                    }
                    elseif($expense->payment_type=='cheque')
                    {
                        /*$bankTransaction = BankTransaction::where(['bank_id'=> $expense->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$expense->id;
                        $bank_transaction->createdDate=$expense->expenseDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='ChequeExpenseReversal|'.$expense;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$expense->grandTotal;;
                        $bank_transaction->Differentiate=$difference+$expense->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expense->bank_id;
                        $bank_transaction->updateDescription = $expense->ChequeNumber;
                        $bank_transaction->save();*/

                        $bank_transaction=BankTransaction::where('Type','expenses')->where('Reference',$Id)->where('company_id',$company_id)->first();
                        $bank_transaction->update(['user_id'=>$user_id]);
                        $bank_transaction->delete();

                        AccountTransaction::where('Description','like','%'.'Expense|'.$expense->id.'%')->update(['updateDescription'=>'hide']);
                        $expense->update(['user_id'=>$user_id]);
                        $expense->delete();
                    }
                }
            }
        });
        return redirect()->route('expenses.index');
    }

    public function expense_delete_post(Request $request)
    {
        $response=false;
        DB::transaction(function () use($request,&$response)
        {
            $expense = Expense::findOrFail($request->row_id);
            $user_id = session('user_id');
            $company_id = session('company_id');
            if ($expense) {
                //cash expense
                if ($expense->bank_id == 0)
                {
                    /*$cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference = $expense->id;
                    $cash_transaction->createdDate = $expense->expenseDate;
                    $cash_transaction->Type = 'expenses';
                    $cash_transaction->Details = 'CashExpenseReversal|' . $expense->id;
                    $cash_transaction->Credit = 0.00;
                    $cash_transaction->Debit = $expense->grandTotal;
                    $cash_transaction->Differentiate = $difference + $expense->grandTotal;
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->PadNumber = $expense->referenceNumber;
                    $cash_transaction->save();*/

                    $cash_transaction=CashTransaction::where('Type','expenses')->where('Reference',$request->row_id)->where('company_id',$company_id)->first();
                    $cash_transaction->update(['user_id'=>$user_id]);
                    $cash_transaction->delete();

                    AccountTransaction::where('Description','like','%'.'Expense|'.$expense->id.'%')->update(['updateDescription'=>'hide']);
                    $expense->update(['user_id'=>$user_id]);
                    $expense->delete();
                }
                else
                {
                    if($expense->payment_type=='bank')
                    {
                        /*$bankTransaction = BankTransaction::where(['bank_id'=> $expense->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$expense->id;
                        $bank_transaction->createdDate=$expense->expenseDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='BankTransferExpenseReversal|'.$expense->id;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$expense->grandTotal;
                        $bank_transaction->Differentiate=$difference+$expense->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expense->bank_id;
                        $bank_transaction->updateDescription = $expense->ChequeNumber;
                        $bank_transaction->save();*/

                        $bank_transaction=BankTransaction::where('Type','expenses')->where('Reference',$request->row_id)->where('company_id',$company_id)->first();
                        $bank_transaction->update(['user_id'=>$user_id]);
                        $bank_transaction->delete();

                        AccountTransaction::where('Description','like','%'.'Expense|'.$expense->id.'%')->update(['updateDescription'=>'hide']);
                        $expense->update(['user_id'=>$user_id]);
                        $expense->delete();
                    }
                    elseif($expense->payment_type=='cheque')
                    {
                        /*$bankTransaction = BankTransaction::where(['bank_id'=> $expense->bank_id])->get();
                        $difference = $bankTransaction->last()->Differentiate;
                        $bank_transaction = new BankTransaction();
                        $bank_transaction->Reference=$expense->id;
                        $bank_transaction->createdDate=$expense->expenseDate ?? date('Y-m-d h:i:s');
                        $bank_transaction->Type='expenses';
                        $bank_transaction->Details='ChequeExpenseReversal|'.$expense;
                        $bank_transaction->Credit=0.00;
                        $bank_transaction->Debit=$expense->grandTotal;;
                        $bank_transaction->Differentiate=$difference+$expense->grandTotal;
                        $bank_transaction->user_id = $user_id;
                        $bank_transaction->company_id = $company_id;
                        $bank_transaction->bank_id = $expense->bank_id;
                        $bank_transaction->updateDescription = $expense->ChequeNumber;
                        $bank_transaction->save();*/

                        $bank_transaction=BankTransaction::where('Type','expenses')->where('Reference',$request->row_id)->where('company_id',$company_id)->first();
                        $bank_transaction->update(['user_id'=>$user_id]);
                        $bank_transaction->delete();

                        AccountTransaction::where('Description','like','%'.'Expense|'.$expense->id.'%')->update(['updateDescription'=>'hide']);
                        $expense->update(['user_id'=>$user_id]);
                        $expense->delete();
                    }
                }
                $response=true;

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'expenses';
                $update_note->RelationId = $request->row_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            }
        });
        return Response()->json($response);
        //return redirect()->route('expenses.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function invoiceNumber()
    {
        $invoice = new Expense();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'EXP-00'.($lastInvoiceID + 1);
        return $newInvoiceID;
    }

    public function PadNumber()
    {
        $PadNumber = new ExpenseDetail();
        $lastPad = $PadNumber->where('company_id',session('company_id'))->orderByDesc('PadNumber')->pluck('PadNumber')->first();
        $newPad = ($lastPad + 1);
        return $newPad;
    }
}
