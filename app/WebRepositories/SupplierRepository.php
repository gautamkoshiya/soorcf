<?php


namespace App\WebRepositories;


use App\Http\Requests\SupplierRequest;
use App\Models\CompanyType;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\lpo;
use App\Models\lpo_detail;
use App\Models\PaymentType;
use App\Models\PaymentTerm;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Region;
use App\Models\Supplier;
use App\Models\AccountTransaction;
use App\Models\Unit;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\ISupplierRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierRepository implements ISupplierRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Supplier::with(['company_type'=>function($q){$q->select('id','Name');}])->select('id','Name','TRNNumber','company_type_id','Address','isActive','user_id','company_id','Mobile')->where('company_type_id',2)->where('company_id',session('company_id'))->orWhere('company_type_id',3)->latest()->get())
               ->addColumn('action', function ($data) {
                    /*$button = '<form action="'.route('suppliers.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('suppliers.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';*/

                   $button= '<a href="'.route('suppliers.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                   $button.= '&nbsp;&nbsp;';
                   $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    return $button;
                })
                ->addColumn('isActive', function($data) {
                        if($data->isActive == true){
                            $button = '<form action="'.route('suppliers.update', $data->id).'" method="POST" ">';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }else{
                            $button = '<form action="'.route('suppliers.update', $data->id).'" method="POST" >';
                            $button .= @csrf_field();
                            $button .= @method_field('PUT');
                            $button .= '<label class="switch"><input name="isActive" type="checkbox" checked><span class="slider"></span></label>';
                            return $button;
                        }
                    })
                ->addColumn('companyType', function($data) {
                    return $data->company_type->Name ?? "No Data";
                })
                ->rawColumns([
                    'action',
                    'isActive',
                    'paymentType',
                    'TRNNumber',
                ])
                ->make(true);
        }
        return view('admin.supplier.index');
    }

    public function create()
    {
        $regions = Region::with('city')->get();
        $payment_types = PaymentType::orderBy('id', 'asc')->skip(0)->take(2)->get();
        $company_types = CompanyType::all();
        $payment_terms = PaymentTerm::all();
        return view('admin.supplier.create',compact('regions','payment_types','company_types','payment_terms'));
    }

    public function store(SupplierRequest $supplierRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');

        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($supplierRequest->hasFile('fileUpload'))
            $filename = $supplierRequest->file('fileUpload')->storeAs('suppliers', $filename,'public');

        else
            $filename = null;

        $supplier = [
            'Name' =>$supplierRequest->Name,
            'Mobile' =>$supplierRequest->Mobile,
            'Representative' =>$supplierRequest->Representative,
            'Phone' =>$supplierRequest->Phone,
            'Address' =>$supplierRequest->Address,
            'postCode' =>$supplierRequest->postCode,
            'region_id' =>$supplierRequest->region_id ?? 0,
            'Email' =>$supplierRequest->Email,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$supplierRequest->Description,
            'registrationDate' =>$supplierRequest->registrationDate,
            'TRNNumber' =>$supplierRequest->TRNNumber,
            'openingBalance' =>$supplierRequest->openingBalance,
            'openingBalanceAsOfDate' =>$supplierRequest->openingBalanceAsOfDate,
            'payment_term_id' =>$supplierRequest->paymentTerm ?? 0,
            'company_type_id' =>$supplierRequest->companyType ?? 0,
            'payment_type_id' =>$supplierRequest->paymentType ?? 0,
        ];
        $supplier = Supplier::create($supplier);
        if ($supplier)
        {
            //account entry
            $account = new AccountTransaction([
                'supplier_id' => $supplier->id,
                'user_id' => $user_id,
                'createdDate' => $supplierRequest->openingBalanceAsOfDate,
                'company_id' =>$company_id,
                'Description' =>'initial',
                'referenceNumber' =>'initial',
                'Credit' =>0.00,
                'Debit' =>0.00,
                'Differentiate' =>$supplierRequest->openingBalance,
            ]);
            $supplier->account_transaction()->save($account);

            if($supplierRequest->companyType==2 && $supplierRequest->openingBalance!=0)
            {
                //purchase entry
                $purchase = new Purchase();
                $purchase->PurchaseNumber = 'initial';
                $purchase->referenceNumber = 'initial';
                $purchase->PurchaseDate = $supplierRequest->openingBalanceAsOfDate;
                $purchase->DueDate =  $supplierRequest->openingBalanceAsOfDate;
                $purchase->Total = $supplierRequest->openingBalance;
                $purchase->subTotal = $supplierRequest->openingBalance;
                $purchase->totalVat = 0.00;
                $purchase->grandTotal = $supplierRequest->openingBalance;
                $purchase->paidBalance = 0.00;
                $purchase->remainingBalance = $supplierRequest->openingBalance;
                $purchase->supplier_id = $supplier->id;
                $purchase->Description = '';
                $purchase->supplierNote = '';
                $purchase->IsPaid = 0;
                $purchase->isActive = 0;
                $purchase->IsPartialPaid = 0;
                $purchase->IsNeedStampOrSignature = false;
                $purchase->user_id = $user_id;
                $purchase->company_id = $company_id;
                $purchase->save();
                $purchase = $purchase->id;

                $product=Product::select('id')->get()->first();
                $unit=Unit::select('id')->get()->first();

                $data =  PurchaseDetail::create([
                    "product_id" => $product->id,
                    "unit_id" => $unit->id,
                    "Quantity" => 0.00,
                    "Price" => 0.00,
                    "rowTotal" => $supplierRequest->openingBalance,
                    "VAT" => 0.00,
                    "rowVatAmount" => 0.00,
                    "rowSubTotal" => $supplierRequest->openingBalance,
                    "PadNumber" => '',
                    "Description" => 'initial',
                    "company_id" => $company_id,
                    "user_id" => $user_id,
                    "purchase_id" => $purchase,
                    "createdDate" => $supplierRequest->openingBalanceAsOfDate,
                    "supplier_id" => $supplier->id,
                ]);
            }
            elseif($supplierRequest->companyType==2 && $supplierRequest->openingBalance!=0)
            {
                $employee=Employee::select('id')->get()->first();
                $expense_category=ExpenseCategory::select('id')->get()->first();

                //expense entry
                $expense = new Expense();
                $expense->expenseNumber = 'initial';
                $expense->referenceNumber = 'initial';
                $expense->expenseDate = $supplierRequest->openingBalanceAsOfDate;
                $expense->Total = $supplierRequest->openingBalance;
                $expense->subTotal = $supplierRequest->openingBalance;
                $expense->totalVat = 0.00;
                $expense->grandTotal = $supplierRequest->openingBalance;
                $expense->paidBalance = 0.00;
                $expense->remainingBalance = $supplierRequest->openingBalance;
                $expense->supplier_id = $supplier->id;
                $expense->employee_id = $employee->id;
                $expense->Description = '';
                $expense->user_id = $user_id;
                $expense->company_id = $company_id;
                $expense->save();
                $expense = $expense->id;

                $data =  ExpenseDetail::create([
                    "Total" => $supplierRequest->openingBalance,
                    "expenseDate" => $supplierRequest->openingBalanceAsOfDate,
                    "expense_category_id" => $expense_category->id,
                    "Description" => 'initial',
                    "Vat" => 0.00,
                    "rowVatAmount" => 0.00,
                    "rowSubTotal" => $supplierRequest->openingBalance,
                    "company_id" => $company_id,
                    "user_id" => $user_id,
                    "expense_id" => $expense,
                    "PadNumber" => '',
                ]);
            }
        }
        return redirect()->route('suppliers.index');
    }

    public function update(Request $request, $Id)
    {
        $supplier = Supplier::find($Id);
        $filename = sprintf('thumbnail_%s.jpg',random_int(1,1000));
        if ($request->hasFile('fileUpload'))
            $filename = $request->file('fileUpload')->storeAs('suppliers', $filename,'public');

        else
            $filename = $supplier->fileUpload;

        $user_id = session('user_id');
        $supplier->update([
            'Name' =>$request->Name,
            'Mobile' =>$request->Mobile,
            'Representative' =>$request->Representative,
            'Phone' =>$request->Phone,
            'Address' =>$request->Address,
            'postCode' =>$request->postCode,
            'region_id' =>$request->region_id ?? 0,
            'user_id' =>$user_id,
            'Email' =>$request->Email,
//            'company_id' =>$company_id,
            'fileUpload' =>$filename,
            'Description' =>$request->Description,
            'registrationDate' =>$request->registrationDate,
            'TRNNumber' =>$request->TRNNumber,
            'openingBalance' =>$request->openingBalance,
            'openingBalanceAsOfDate' =>$request->openingBalanceAsOfDate,
            'payment_term_id' =>$request->paymentTerm ?? 0,
            'company_type_id' =>$request->companyType ?? 0,
            'payment_type_id' =>$request->paymentType ?? 0,

        ]);
        return redirect()->route('suppliers.index');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $regions = Region::with('city')->get();
        $payment_types = PaymentType::orderBy('id', 'asc')->skip(0)->take(2)->get();
        $company_types = CompanyType::all();
        $payment_terms = PaymentTerm::all();
        $supplier = Supplier::with('region','payment_type','company_type','payment_term')->find($Id);
        return view('admin.supplier.edit',compact('supplier','regions','payment_types','company_types','payment_terms'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Supplier::findOrFail($Id);
        $data->delete();
        return redirect()->route('suppliers.index');
    }

    public function supplier_delete_post(Request $request)
    {
        $data = Supplier::findOrFail($request->row_id);
        $data->delete();

        $update_note = new UpdateNote();
        $update_note->RelationTable = 'suppliers';
        $update_note->RelationId = $request->row_id;
        $update_note->UpdateDescription = $request->deleteDescription;
        $update_note->user_id = session('user_id');
        $update_note->company_id = session('company_id');
        $update_note->save();

        return Response()->json(true);
        //return redirect()->route('suppliers.index');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function supplierDetails($Id)
    {
        //$suppliers = Supplier::find($Id);
        $suppliers = Supplier::select('id','Name','Address','Mobile','postCode','TRNNumber','Email')->where('id',$Id)->get();

        // getting latest closing for supplier from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.supplier_id')
            ->where('ac.supplier_id','=',$Id)
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.supplier_id','ac.Differentiate')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->get();
        $row=json_decode(json_encode($row), true);
        $row=$row[0]['Differentiate'];

        //get open lpo list
        $parent_ids=lpo::select('id')->where('customer_id',$Id)->get();
        $parent_ids=json_decode(json_encode($parent_ids), true);
        $parent_ids=array_column($parent_ids,'id');
        $lpo_ids_from_detail=lpo_detail::select('lpo_id')->whereIn('lpo_id',$parent_ids)->where('RemainingQty','!=',0)->get();
        $lpo_ids_from_detail=json_decode(json_encode($lpo_ids_from_detail), true);
        $lpo_ids_from_detail=array_column($lpo_ids_from_detail,'lpo_id');
        $lpo_ids=lpo::select('id','LPONumber')->whereIn('id',$lpo_ids_from_detail)->get();
        return response()->json(array('supplier'=>$suppliers,'closing'=>$row,'open_lpo'=>$lpo_ids));
    }

    public function getRemainingQtyOfOpenLpo($Id)
    {
        $rem_qty=lpo_detail::select('RemainingQty','Price','VAT')->where('lpo_id',$Id)->first();
        return $rem_qty;
    }

    public function getLedgerSuppliers()
    {
        $suppliers = Supplier::select('id','Name')->where('company_id',session('company_id'))->where('company_type_id',2)->get();
        return response()->json(array('parties'=>$suppliers));
    }
}
