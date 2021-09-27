<?php


namespace App\WebRepositories;


use App\Http\Requests\PurchaseRequest;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Models\AccountTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\lpo_detail;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SupplierAdvance;
use App\Models\SupplierAdvanceDetail;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentDetail;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IPurchaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDF;

class PurchaseRepository implements IPurchaseRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Purchase::with(['purchase_details_without_trash.product','supplier'=>function($q){$q->select('id','Name');}])->select('id','company_id','supplier_id','PurchaseDate','DueDate','grandTotal','paidBalance','remainingBalance','referenceNumber','Total','totalVat')->where('company_id',session('company_id'))->where('isActive',1)->latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<a href="'.route('purchases.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    if($data->paidBalance==0.00)
                    {
                        //$button .='<a href="'.url('purchase_delete', $data->id).'" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                        $button.='<a href="#" data-id="'.$data->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                    }
                    else
                    {
                        $button .='<a href="javascript:void(0);" class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-ban"></i></i></a>';
                    }
                    $button.='&nbsp;&nbsp;';
                    $button.='<button class="btn btn-primary" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-money"><i class="fa fa-question" ></i></i></button>';
//                    $button .='<a href="javascript:void(0)"  onclick="return'. get_pdf($data->id).'"  class=" btn btn-secondary btn-sm"><i style="font-size: 20px" class="fa fa-file-pdf-o"></i></a>';
                    return $button;
                })
                ->addColumn('createdDate', function($data) {
                    return date('d-m-Y', strtotime($data->purchase_details_without_trash[0]->createdDate)) ?? "No date";
                })
                ->addColumn('DueDate', function($data) {
                    return date('d-m-Y', strtotime($data->DueDate)) ?? "No date";
                })
                ->addColumn('PadNumber', function($data) {
                    return $data->purchase_details_without_trash[0]->PadNumber ?? "No Pad";
                })
                ->addColumn('supplier', function($data) {
                    return $data->supplier->Name ?? "No Name";
                })
                ->addColumn('Product', function($data) {
                    return $data->purchase_details_without_trash[0]->product->Name ?? "No product";
                })
                ->addColumn('Quantity', function($data) {
                    return $data->purchase_details_without_trash[0]->Quantity ?? "No Quantity";
                })
                ->addColumn('Price', function($data) {
                    return $data->purchase_details_without_trash[0]->Price ?? "No Quantity";
                })
                ->rawColumns(
                    [
                        'action',
                        //'isActive',
                        'createdDate',
                        'referenceNumber',
                        'PadNumber',
                        'supplier',
                        'Quantity',
                        'Price',
                        'DueDate',
                    ])
                ->make(true);
        }
        return view('admin.purchase.index');
    }

    public function all_purchase(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = Purchase::where('company_id',session('company_id'))->where('isActive',1)->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select p.id,p.company_id,p.supplier_id,p.PurchaseDate,p.totalVat,p.grandTotal,p.paidBalance,p.remainingBalance,p.TermsAndCondition,p.supplierNote,p.referenceNumber,p.DueDate,p.Total,s.Name, pd.PadNumber,pd.Quantity,pd.Price from purchases as p left join suppliers as s on s.id = p.supplier_id join (SELECT purchase_details.* FROM purchase_details WHERE purchase_details.deleted_at is null) as pd on p.id = pd.purchase_id where p.company_id = '.session('company_id').' and p.isActive = 1 and p.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $purchase = DB::select( DB::raw($sql));
        }
        else {
            $search = $request->input('search.value');

            $sql = 'select p.id,p.company_id,p.supplier_id,p.PurchaseDate,p.totalVat,p.grandTotal,p.paidBalance,p.remainingBalance,p.TermsAndCondition,p.supplierNote,p.referenceNumber,p.DueDate,p.Total,s.Name,pd.PadNumber,pd.Quantity,pd.Price from purchases as p left join suppliers as s on s.id = p.supplier_id join (SELECT purchase_details.* FROM purchase_details WHERE purchase_details.deleted_at is null) as pd on p.id = pd.purchase_id where p.company_id = '.session('company_id').' and p.isActive = 1 and p.deleted_at is null and p.referenceNumber LIKE "%'.$search.'%" or pd.PadNumber LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $purchase = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,p.id,p.company_id,p.supplier_id,p.PurchaseDate,p.totalVat,p.grandTotal,p.paidBalance,p.remainingBalance,p.TermsAndCondition,p.supplierNote,p.referenceNumber,p.DueDate,p.Total,s.Name,pd.PadNumber,pd.Quantity,pd.Price from purchases as p left join suppliers as s on s.id = p.supplier_id join (SELECT purchase_details.* FROM purchase_details WHERE purchase_details.deleted_at is null) as pd on p.id = pd.purchase_id where p.company_id = '.session('company_id').' and p.isActive = 1 and p.deleted_at is null and p.referenceNumber LIKE "%'.$search.'%" or pd.PadNumber LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start;
            $count = DB::select(DB::raw($sql_count));
            if(!empty($count))
            {
                $totalFiltered = $count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($purchase))
        {
            foreach ($purchase as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['PurchaseDate'] = date('d-m-Y', strtotime($single->PurchaseDate));
                $nestedData['referenceNumber'] = $single->referenceNumber ?? "N.A.";
                $nestedData['PadNumber'] = $single->PadNumber ?? "N.A.";
                $nestedData['supplier'] = $single->Name ?? "N.A.";
                $nestedData['DueDate'] = $single->DueDate ?? 0.00;
                $nestedData['Quantity'] = $single->Quantity ?? 0.00;
                $nestedData['Total'] = $single->Total ?? 0.00;
                $nestedData['totalVat'] = $single->totalVat ?? 0.00;
                $nestedData['grandTotal'] = $single->grandTotal ?? 0.00;
                $nestedData['paidBalance'] = $single->paidBalance ?? 0.00;
                $nestedData['remainingBalance'] = $single->remainingBalance ?? 0.00;
                $button='';
                if($single->TermsAndCondition==1)
                {
                    $button.='<a href="'.route('purchases.edit', $single->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                }
                $button.='&nbsp;';
                if($single->supplierNote==1)
                {
                    $button.='<a href="#" data-id="'.$single->id.'" data-toggle="modal" class="deleteRow btn btn-danger btn-sm" data-target="#deleteRow"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                }
                $button.='&nbsp;<button class="btn btn-primary"  onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i style="font-size: 20px" class="fa fa-money"><i class="fa fa-question" ></i></i></button>';
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

    public function create()
    {
        $purchaseNo = $this->invoiceNumber();
        $PadNumber = $this->PadNumber();
        $suppliers = Supplier::where('company_type_id',2)->where('company_id',session('company_id'))->get();
        $products = Product::select('id','Name')->get();
        $purchaseRecords = Purchase::with(['purchase_details_without_trash','supplier'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->orderBy('id', 'desc')->skip(0)->take(3)->get();
        return view('admin.purchase.create',compact('suppliers','purchaseNo','products','PadNumber','purchaseRecords'));
    }

    public function store(PurchaseRequest $purchaseRequest)
    {
        $pad_number=$purchaseRequest->Data['orders'][0]['PadNumber'];
        if($pad_number!=0)
        {
            $already_exist = PurchaseDetail::where('company_id',session('company_id'))->where('PadNumber',$pad_number)->get();
            if(!$already_exist->isEmpty())
            {
                $data=array('result'=>false,'message'=>'PAD NUMBER ALREADY EXIST');
                echo json_encode($data);exit();
            }
        }

        DB::transaction(function () use($purchaseRequest)
        {
            $user_id = session('user_id');
            $company_id = session('company_id');

            $AllRequestCount = collect($purchaseRequest->Data)->count();
            if ($AllRequestCount > 0)
            {   if (isset($purchaseRequest->Data['orders'][0]['PadNumber']))
                {
                    $this_pad_no = $purchaseRequest->Data['orders'][0]['PadNumber'];
                }
                else
                {
                    $this_pad_no = 0;
                }
                $purchase = new Purchase();
                $purchase->PurchaseNumber = $purchaseRequest->Data['PurchaseNumber'];
                $purchase->referenceNumber = $purchaseRequest->Data['referenceNumber'];
                $purchase->PurchaseDate = $purchaseRequest->Data['PurchaseDate'];
                $purchase->DueDate = $purchaseRequest->Data['DueDate'];
                $purchase->Total = $purchaseRequest->Data['Total'];
                $purchase->subTotal = $purchaseRequest->Data['subTotal'];
                $purchase->totalVat = $purchaseRequest->Data['totalVat'];
                $purchase->grandTotal = $purchaseRequest->Data['grandTotal'];
                $purchase->paidBalance = $purchaseRequest->Data['paidBalance'];
                $purchase->remainingBalance = $purchaseRequest->Data['grandTotal'] - $purchaseRequest->Data['paidBalance'];
                $purchase->supplier_id = $purchaseRequest->Data['supplier_id'];
                $purchase->supplierNote = 1;
                $purchase->TermsAndCondition = 1;
                $purchase->IsPaid = false;
                $purchase->IsPartialPaid = false;
                $purchase->IsNeedStampOrSignature = false;
                $purchase->user_id = $user_id;
                $purchase->company_id = $company_id;
                $purchase->save();
                $purchase = $purchase->id;

                foreach ($purchaseRequest->Data['orders'] as $detail)
                {
                    PurchaseDetail::create([
                        "product_id" => $detail['product_id'],
                        "unit_id" => $detail['unit_id'],
                        "Quantity" => $detail['Quantity'],
                        "Price" => $detail['Price'],
                        "rowTotal" => $detail['rowTotal'],
                        "VAT" => $detail['Vat'],
                        "rowVatAmount" => $detail['rowVatAmount'],
                        "rowSubTotal" => $detail['rowSubTotal'],
                        "PadNumber" => $detail['PadNumber'],
                        "Description" => $detail['description'],
                        "company_id" => $company_id,
                        "user_id" => $user_id,
                        "purchase_id" => $purchase,
                        "createdDate" => $purchaseRequest->Data['PurchaseDate'],
                        "supplier_id" => $purchaseRequest->Data['supplier_id'],
                    ]);
                }

                if($purchaseRequest->Data['open_lpo_list']!='' AND $purchaseRequest->Data['open_lpo_list']!=0)
                {
                    //purchased from open lpo need to update qty in lpo detail table
                    $lpo_detail = lpo_detail::where('lpo_id',$purchaseRequest->Data['open_lpo_list'])->first();
                    $qty_to_update=$lpo_detail->RemainingQty-$purchaseRequest->Data['orders'][0]['Quantity'];
                    $lpo_detail->update([
                        'RemainingQty' => $qty_to_update,
                    ]);
                }

                if ($purchaseRequest->Data['paidBalance'] != 0.00 || $purchaseRequest->Data['paidBalance'] != 0)
                {
                    $cashTransaction = CashTransaction::where(['company_id' => $company_id])->get();
                    $difference = $cashTransaction->last()->Differentiate;
                    $cash_transaction = new CashTransaction();
                    $cash_transaction->Reference = $purchase;
                    $cash_transaction->createdDate = $purchaseRequest->Data['PurchaseDate'];
                    $cash_transaction->Type = 'purchases';
                    $cash_transaction->Details = 'CashPurchase|' . $purchase;
                    $cash_transaction->Credit = $purchaseRequest->Data['paidBalance'];
                    $cash_transaction->Debit = 0.00;
                    $cash_transaction->Differentiate = $difference - $purchaseRequest->Data['paidBalance'];
                    $cash_transaction->user_id = $user_id;
                    $cash_transaction->company_id = $company_id;
                    $cash_transaction->save();
                }

                ////////////////// start account section gautam ////////////////
                if ($purchase)
                {
                    $accountTransaction = AccountTransaction::where(['supplier_id' => $purchaseRequest->Data['supplier_id'],])->get();
                    // totally credit
                    if ($purchaseRequest->Data['paidBalance'] == 0 || $purchaseRequest->Data['paidBalance'] == 0.00)
                    {
                        $totalCredit = $purchaseRequest->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $purchaseRequest->Data['grandTotal'];
                        $AccData = [
                            'supplier_id' => $purchaseRequest->Data['supplier_id'],
                            'Credit' => $totalCredit,
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'Purchase|' . $purchase,
                            'referenceNumber' => $this_pad_no,
                        ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    } // partial payment some cash some credit
                    elseif ($purchaseRequest->Data['paidBalance'] > 0 and $purchaseRequest->Data['paidBalance'] < $purchaseRequest->Data['grandTotal'])
                    {
                        $differenceValue = $accountTransaction->last()->Differentiate - $purchaseRequest->Data['paidBalance'];
                        $totalCredit = $accountTransaction->last()->Differentiate + $purchaseRequest->Data['grandTotal'];
                        $difference = $differenceValue + $purchaseRequest->Data['grandTotal'];

                        //make credit entry for the purchase
                        $AccData =
                            [
                                'supplier_id' => $purchaseRequest->Data['supplier_id'],
                                'Credit' => $purchaseRequest->Data['grandTotal'],
                                'Debit' => 0.00,
                                'Differentiate' => $totalCredit,
                                'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'Purchase|' . $purchase,
                                'referenceNumber' => $this_pad_no,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);

                        //make debit entry for the whatever cash is paid
                        $difference = $totalCredit - $purchaseRequest->Data['paidBalance'];
                        $AccData =
                            [
                                'supplier_id' => $purchaseRequest->Data['supplier_id'],
                                'Credit' => 0.00,
                                'Debit' => $purchaseRequest->Data['paidBalance'],
                                'Differentiate' => $difference,
                                'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                                'user_id' => $user_id,
                                'company_id' => $company_id,
                                'Description' => 'PartialCashPurchase|' . $purchase,
                                'referenceNumber' => $this_pad_no,
                            ];
                        $AccountTransactions = AccountTransaction::Create($AccData);
                    } // fully paid with cash
                    else
                    {
                        $totalCredit = $purchaseRequest->Data['grandTotal'];
                        $difference = $accountTransaction->last()->Differentiate + $purchaseRequest->Data['grandTotal'];
                        //make credit entry for the purchase
                        $AccountTransactions = AccountTransaction::Create([
                            'supplier_id' => $purchaseRequest->Data['supplier_id'],
                            'Credit' => $totalCredit,
                            'Debit' => 0.00,
                            'Differentiate' => $difference,
                            'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'Purchase|' . $purchase,
                            'referenceNumber' => $this_pad_no,
                        ]);

                        //make debit entry for the whatever cash is paid
                        $difference = $difference - $purchaseRequest->Data['paidBalance'];
                        $AccountTransactions = AccountTransaction::Create([
                            'supplier_id' => $purchaseRequest->Data['supplier_id'],
                            'Credit' => 0.00,
                            'Debit' => $purchaseRequest->Data['paidBalance'],
                            'Differentiate' => $difference,
                            'createdDate' => $purchaseRequest->Data['PurchaseDate'],
                            'user_id' => $user_id,
                            'company_id' => $company_id,
                            'Description' => 'FullCashPurchase|' . $purchase,
                            'referenceNumber' => $this_pad_no,
                        ]);
                    }
                    $data=array('result'=>true,'message'=>'Record Inserted Successfully.');
                    echo json_encode($data);
                }
                ////////////////// end account section gautam ////////////////
            }
        });
    }

    public function update(Request $request, $Id)
    {
        DB::transaction(function () use($request,$Id) {
            $AllRequestCount = collect($request->Data)->count();
            if ($AllRequestCount > 0) {
                $purchased = Purchase::find($Id);
                $user_id = session('user_id');
                $company_id = session('company_id');

                ////////////////// account section gautam ////////////////
                $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                if (!is_null($accountTransaction))
                {
                    // payment is done (in any way - advance or payment)
                    if ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0)
                    {
                        // here will come 3 cases
                        // 1. only supplier is updating - quantity and price remains same
                        // 2. only quantity or price updating - supplier is the same
                        // 3. both supplier and quantity or price updating

                        //check if only supplier is changed and not quantity or price = grand total is same as previous
                        if ($request->Data['supplier_id'] != $purchased->supplier_id and $purchased->grandTotal == $request->Data['grandTotal']) {
                            //supplier is changed need to reverse all previously made account entries for the previous supplier

                            //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                            if ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0)
                            {
                                // start reverse entry
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'Purchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing - $previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                            }

                            /*new entry*/
                            // start new entry for updated supplier with checking all three cases
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $request->Data['supplier_id'],])->get();
                            // totally credit
                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $totalCredit,
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            } // partial payment some cash some credit
                            elseif ($request->Data['paidBalance'] > 0 and $request->Data['paidBalance'] < $request->Data['grandTotal']) {
                                $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $difference = $differenceValue + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $request->Data['grandTotal'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $totalCredit,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);

                                //make debit entry for the whatever cash is paid
                                $difference = $totalCredit - $request->Data['paidBalance'];
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $request->Data['paidBalance'],
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'PartialCashPurchase|' . $Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            } // fully paid with cash
                            else {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccountTransactions = AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ]);

                                //make debit entry for the whatever cash is paid
                                $difference = $difference - $request->Data['paidBalance'];
                                $AccountTransactions = AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $request->Data['paidBalance'],
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'FullCashPurchase|' . $Id,
                                ]);
                            }
                            /*new entry*/
                        } // check if only grand total is changed and not the supplier
                        elseif ($request->Data['supplier_id'] == $purchased->supplier_id and $purchased->grandTotal != $request->Data['grandTotal']) {
                            //supplier is not changed then need to find what is the differance in total and for payment changes
                            // here in two way we can proceed
                            // option 1 : reverse previous account entries and make new entry
                            // option 2 : find out plus minus differance and make one another entry with differences
                            // option 2 is not preferable because of while displaying we need to add or subtract similar purchase id entry so that is little tricky in query
                            // also need to manage isPaid and isPartialPaid flag according

                            // implementation of option 2
                            //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                            if ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0) {
                                // start reverse entry
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'Purchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing - $previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                            } //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                            elseif ($purchased->paidBalance > 0 and $purchased->paidBalance < $purchased->grandTotal and $purchased->IsPartialPaid == 1) {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'Purchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing - $previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'PartialCashPurchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing + $previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'PartialCashPurchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            } //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                            else {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'Purchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing - $previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'FullCashPurchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing + $previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'FullCashPurchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            }

                            /* new entry */
                            // start new entry for updated supplier with checking all three cases
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $request->Data['supplier_id'],])->get();
                            // totally credit
                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $totalCredit,
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            } // partial payment some cash some credit
                            elseif ($request->Data['paidBalance'] > 0 and $request->Data['paidBalance'] < $request->Data['grandTotal']) {
                                $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $difference = $differenceValue + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $request->Data['grandTotal'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $totalCredit,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);

                                //make debit entry for the whatever cash is paid
                                $difference = $totalCredit - $request->Data['paidBalance'];
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $request->Data['paidBalance'],
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'PartialCashPurchase|' . $Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            } // fully paid with cash
                            else {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccountTransactions = AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ]);

                                //make debit entry for the whatever cash is paid
                                $difference = $difference - $request->Data['paidBalance'];
                                $AccountTransactions = AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $request->Data['paidBalance'],
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'FullCashPurchase|' . $Id,
                                ]);
                            }
                            /* new entry */
                        } // check both supplier and grandTotal is changed meaning case 3
                        elseif ($request->Data['supplier_id'] != $purchased->supplier_id and $purchased->grandTotal != $request->Data['grandTotal']) {
                            //case : 1 full credit entry + payment is not done yet like isPaid=0 and IsPartialPaid=0
                            if ($purchased->IsPaid == 0 && $purchased->IsPartialPaid == 0) {
                                // start reverse entry
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'Purchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing - $previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                            } //case : 2 partial cash is paid and some amount is credit + payment is not fully done yet like isPaid=0 and IsPartialPaid=1
                            elseif ($purchased->paidBalance > 0 and $purchased->paidBalance < $purchased->grandTotal and $purchased->IsPartialPaid == 1) {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'Purchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing - $previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done
                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'PartialCashPurchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing + $previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'PartialCashPurchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            } //case : 3 fully cash paid + isPaid=1 and IsPartialPaid=1
                            else {
                                // entry 1 : debit entry for purchase
                                // start reverse entry
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'Purchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_credited = $previous_entry->Credit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => $previously_credited,
                                        'Credit' => 0.00,
                                        'Differentiate' => $last_closing - $previously_credited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // entry 2 : credit whatever cash is debited
                                // start reverse entry
                                $accountTransaction = AccountTransaction::where(['supplier_id' => $purchased->supplier_id,])->get();
                                $last_closing = $accountTransaction->last()->Differentiate;
                                $description_string = 'FullCashPurchase|' . $Id;
                                $previous_entry = AccountTransaction::get()->where('supplier_id', '=', $purchased->supplier_id)->where('Description', 'like', $description_string)->last();
                                //echo "<pre>";print_r($previous_entry->Credit);die;
                                $previously_debited = $previous_entry->Debit;
                                $AccData =
                                    [
                                        'supplier_id' => $purchased->supplier_id,
                                        'Debit' => 0.00,
                                        'Credit' => $previously_debited,
                                        'Differentiate' => $last_closing + $previously_debited,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'FullCashPurchase|' . $Id,
                                        'updateDescription' => 'hide',
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                                // also hide previous entry start
                                AccountTransaction::where('id', $previous_entry->id)->update(array('updateDescription' => 'hide'));
                                // also hide previous entry end
                                // reverse entry done

                                // reverse cash entry start
                                // reverse cash entry end
                                // no need to make cash entries because amount is same only supplier is changing
                                // make new cash entry for correct supplier start
                                // make new cash entry for correct supplier end
                            }

                            /*new entry*/
                            // start new entry for updated supplier with checking all three cases
                            $accountTransaction = AccountTransaction::where(['supplier_id' => $request->Data['supplier_id'],])->get();
                            // totally credit
                            if ($request->Data['paidBalance'] == 0 || $request->Data['paidBalance'] == 0.00) {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $totalCredit,
                                        'Debit' => 0.00,
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            } // partial payment some cash some credit
                            elseif ($request->Data['paidBalance'] > 0 and $request->Data['paidBalance'] < $request->Data['grandTotal']) {
                                $differenceValue = $accountTransaction->last()->Differentiate - $request->Data['paidBalance'];
                                $totalCredit = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];
                                $difference = $differenceValue + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => $request->Data['grandTotal'],
                                        'Debit' => 0.00,
                                        'Differentiate' => $totalCredit,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'Purchase|' . $Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);

                                //make debit entry for the whatever cash is paid
                                $difference = $totalCredit - $request->Data['paidBalance'];
                                $AccData =
                                    [
                                        'supplier_id' => $request->Data['supplier_id'],
                                        'Credit' => 0.00,
                                        'Debit' => $request->Data['paidBalance'],
                                        'Differentiate' => $difference,
                                        'createdDate' => $request->Data['PurchaseDate'],
                                        'user_id' => $user_id,
                                        'company_id' => $company_id,
                                        'Description' => 'PartialCashPurchase|' . $Id,
                                    ];
                                $AccountTransactions = AccountTransaction::Create($AccData);
                            } // fully paid with cash
                            else {
                                $totalCredit = $request->Data['grandTotal'];
                                $difference = $accountTransaction->last()->Differentiate + $request->Data['grandTotal'];

                                //make credit entry for the purchase
                                $AccountTransactions = AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => $totalCredit,
                                    'Debit' => 0.00,
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'Purchase|' . $Id,
                                ]);

                                //make debit entry for the whatever cash is paid
                                $difference = $difference - $request->Data['paidBalance'];
                                $AccountTransactions = AccountTransaction::Create([
                                    'supplier_id' => $request->Data['supplier_id'],
                                    'Credit' => 0.00,
                                    'Debit' => $request->Data['paidBalance'],
                                    'Differentiate' => $difference,
                                    'createdDate' => $request->Data['PurchaseDate'],
                                    'user_id' => $user_id,
                                    'company_id' => $company_id,
                                    'Description' => 'FullCashPurchase|' . $Id,
                                ]);
                            }
                            /*new entry*/
                        }
                        //return Response()->json($accountTransaction);
                    }
                }
                ////////////////// end of account section gautam ////////////////

                if ($request->Data['paidBalance'] == 0.00 || $request->Data['paidBalance'] == 0)
                {
                    $isPaid = false;
                    $partialPaid = false;
                }
                elseif ($request->Data['paidBalance'] >= $request->Data['grandTotal'])
                {
                    $isPaid = true;
                    $partialPaid = false;
                }
                else
                {
                    $isPaid = false;
                    $partialPaid = true;
                }
                $purchased->update([
                    'PurchaseNumber' => $request->Data['PurchaseNumber'],
                    'referenceNumber' => $request->Data['referenceNumber'],
                    'PurchaseDate' => $request->Data['PurchaseDate'],
                    'DueDate' => $request->Data['DueDate'],
                    'Total' => $request->Data['Total'],
                    'subTotal' => $request->Data['subTotal'],
                    'totalVat' => $request->Data['totalVat'],
                    'grandTotal' => $request->Data['grandTotal'],
                    'paidBalance' => $request->Data['paidBalance'],
                    'remainingBalance' => $request->Data['grandTotal'],
                    'supplier_id' => $request->Data['supplier_id'],
                    'supplierNote' => $request->Data['supplierNote'],
                    'IsPaid' => $isPaid,
                    'IsPartialPaid' => $partialPaid,
                    'IsNeedStampOrSignature' => false,
                    'user_id' => $user_id,
                    'company_id' => $company_id,
                ]);

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'purchases';
                $update_note->RelationId = $Id;
                $update_note->Description = $request->Data['UpdateDescription'];
                $update_note->user_id = $user_id;
                $update_note->company_id = $company_id;
                $update_note->save();

                PurchaseDetail::where('purchase_id', array($Id))->delete();
                PurchaseDetail::where('purchase_id', $Id)->get();
                foreach ($request->Data['orders'] as $detail) {
                    PurchaseDetail::create([
                        "product_id" => $detail['product_id'],
                        "unit_id" => $detail['unit_id'],
                        "Quantity" => $detail['Quantity'],
                        "Price" => $detail['Price'],
                        "rowTotal" => $detail['rowTotal'],
                        "VAT" => $detail['Vat'],
                        "rowVatAmount" => $detail['rowVatAmount'],
                        "rowSubTotal" => $detail['rowSubTotal'],
                        "PadNumber" => $detail['PadNumber'],
                        "Description" => $detail['description'],
                        "user_id" => $user_id,
                        "company_id" => $company_id,
                        "purchase_id" => $Id,
                        "createdDate" => $request->Data['PurchaseDate'],
                        "supplier_id" => $request->Data['supplier_id'],
                    ]);
                }
            }
        });
        $ss = PurchaseDetail::where('purchase_id', $Id)->get();
        return Response()->json($ss);
    }

    public function getById($Id)
    {
        $Response = PurchaseResource::collection(Purchase::where('id',$Id)->with('user','supplier','purchase_details','update_notes','documents')->get());
        $data = json_decode(json_encode($Response), true);
        return $data[0];
    }

    public function edit($Id)
    {
        $update_notes = UpdateNote::with('company','user')->where(['RelationId' => $Id, 'RelationTable' => 'purchases'])->get();
        $suppliers = Supplier::where('company_type_id',2)->get();
        $products = Product::all();
        $units = Unit::all();
        $purchase_details = PurchaseDetail::withTrashed()->with('purchase.supplier','user','product','unit')->where('purchase_id', $Id)->get();
        return view('admin.purchase.edit',compact('purchase_details','suppliers','products','update_notes','units'));
    }

    public function delete($Id)
    {
        $purchase = Purchase::find($Id);
        if($purchase && $purchase->paidBalance==0.00)
        {
            DB::transaction(function () use($purchase)
            {
                $user_id = session('user_id');
                $company_id = session('company_id');

                PurchaseDetail::where('purchase_id', '=', $purchase->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id]);
                PurchaseDetail::where('purchase_id', '=', $purchase->id)->where('company_id', '=', $company_id)->delete();

                AccountTransaction::where('Description', 'like', 'Purchase|' . $purchase->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,'updateDescription'=>'hide']);
                AccountTransaction::where('Description', 'like', 'Purchase|' . $purchase->id)->where('company_id', '=', $company_id)->delete();

                $purchase->update(['user_id' => $user_id,]);
                $purchase->delete();
            });
            return redirect()->route('purchases.index');
        }
        else
        {
            echo "Not allowed";die;
        }
    }

    public function purchase_delete_post(Request $request)
    {
        $purchase = Purchase::find($request->row_id);
        if($purchase && $purchase->paidBalance==0.00)
        {
            DB::transaction(function () use($purchase,$request)
            {
                $user_id = session('user_id');
                $company_id = session('company_id');

                PurchaseDetail::where('purchase_id', '=', $purchase->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id]);
                PurchaseDetail::where('purchase_id', '=', $purchase->id)->where('company_id', '=', $company_id)->delete();

                AccountTransaction::where('Description', 'like', 'Purchase|' . $purchase->id)->where('company_id', '=', $company_id)->update(['user_id' => $user_id,'updateDescription'=>'hide']);
                AccountTransaction::where('Description', 'like', 'Purchase|' . $purchase->id)->where('company_id', '=', $company_id)->delete();

                $purchase->update(['user_id' => $user_id,]);
                $purchase->delete();

                $update_note = new UpdateNote();
                $update_note->RelationTable = 'purchases';
                $update_note->RelationId = $request->row_id;
                $update_note->UpdateDescription = $request->deleteDescription;
                $update_note->user_id = session('user_id');
                $update_note->company_id = $company_id;
                $update_note->save();
            });
            //return redirect()->route('purchases.index');
            return Response()->json(true);
        }
        else
        {
            echo "Not allowed";die;
        }
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
        $invoice = new Purchase();
        $lastInvoiceID = $invoice->orderByDesc('id')->pluck('id')->first();
        $newInvoiceID = 'PUR-00'.($lastInvoiceID + 1);
        return $newInvoiceID;
    }

    public function PadNumber()
    {
        $max_purchase_id = PurchaseDetail::where('company_id',session('company_id'))->max('id');
        $max_purchase_id = PurchaseDetail::where('id',$max_purchase_id)->first();
        if($max_purchase_id)
        {
            $lastPad = $max_purchase_id->PadNumber;
            if(!is_numeric($lastPad))
            {
                $newPad=1;
            }
            else
            {
                $newPad = ($lastPad + 1);
            }
        }
        else
        {
            $newPad=1;
        }
        return $newPad;
    }

    public function CheckPurchasePadExist($request)
    {
        $data = PurchaseDetail::where('PadNumber','=',$request->PadNumber)->where('company_id','=',session('company_id'))->get();
        if($data->first())
        {
            return Response()->json(true);
        }
        else
        {
            return Response()->json(false);
        }
    }

    public function print($id)
    {
        $data=$this->getById($id);
        //echo "<pre>";print_r($data);die;
        if(!empty($data['purchase_details']))
        {
            $company_title='WATAN PHARMA LLP.';
            $company_address='MUSSAFAH M13,PLOT 100, ABU DHABI,UAE';
            $company_email='Email : info@alhamood.ae';
            $company_mobile='Mobile : +971-25550870  +971-557383866  +971-569777861';
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            $pdf::AddPage();$pdf::SetFont('times', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetXY(25,7);
            $pdf::SetFont('times', '', 12);
            $pdf::MultiCell(83, 5, $company_title, 0, 'R', 0, 2, '', '', true, 0);
            $pdf::SetFont('times', '', 8);

            $pdf::SetXY(25,12);
            $pdf::MultiCell(134, 5, $company_address, 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(44, 5, $data['PurchaseNumber'], 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,16);
            $pdf::MultiCell(147, 5, $company_mobile, 0, 'C', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,20);
            $pdf::MultiCell(107, 5, $company_email, 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(71, 5, 'Date : '.date('d-m-Y', strtotime($data['PurchaseDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(25,24);
            $pdf::MultiCell(106, 5, 'TRN : 100330389600003', 0, 'C', 0, 2, '', '', true, 0);
            $pdf::MultiCell(72, 5, 'Due Date : '.date('d-m-Y', strtotime($data['DueDate'])), 0, 'R', 0, 2, '', '', true, 0);

            $pdf::SetXY(28,28);
            $pdf::Image('https://watanpharma.com/images/logo-1.png', 15, 5, 40, 18, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf::SetXY(15,37);
            $pdf::Ln(6);

            $pdf::SetXY(25,35);
            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $row=$data['purchase_details'];
            $pdf::SetFont('times', '', 15);
            $html='<u><b>PURCHASE INVOICE</b></u>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $created_by=isset($data['user']['name'])?$data['user']['name']:'N.A.';
            $vendor=isset($data['supplier']['Name'])?$data['supplier']['Name']:'N.A.';
            $email=isset($data['vendor']['Name'])?$data['vendor']['Name']:'N.A.';
            $phone=isset($data['vendor']['Mobile'])?$data['vendor']['Mobile']:'N.A.';
            $address=isset($data['vendor']['Address'])?$data['vendor']['Address']:'N.A.';
            $pdf::SetFont('times', '', 10);
            $pdf::Cell(95, 5, 'SUPPLIER :','B',0,'L');
            $pdf::Cell(95, 5, 'Created By : '.$created_by,'',0,'R');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Name : '.$vendor,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Email : '.$email,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Phone : '.$phone,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, 'Address : '.$address,'',0,'L');
            $pdf::Ln(6);

            $pdf::Cell(95, 5, '','',0,'');
            $pdf::Ln(6);

            $pdf::SetFont('times', 'B', 14);
            $html = '<table border="0.5" cellpadding="5">
                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                    <th align="center" width="30">S/N</th>
                    <th align="center" width="190">Product</th>
                    <th align="center" width="70">PadNO</th>
                    <th align="center" width="50">Unit</th>
                    <th align="center" width="55">Price</th>
                    <th align="center" width="50">Quantity</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="80">Subtotal</th>
                </tr>';
            $pdf::SetFont('times', '', 10);
            $subtotal=0.0;
            $vat_total=0.0;
            $grand_total=0.0;
            $sn=0;
            for($i=0;$i<count($row);$i++)
            {
//                if($row[$i]['deleted_at']=='1970-01-01T08:00:00.000000Z')
//                {

                $html .='<tr>
                    <td align="center" width="30">'.($sn+1).'</td>
                    <td align="left" width="190">'.$row[$i]['api_product']['Name'].'</td>
                    <td align="left" width="70">'.$row[$i]['PadNumber'].'</td>
                    <td align="center" width="50">'.'N.A.'.'</td>
                    <td align="center" width="55">'.number_format($row[$i]['Price'],2,'.',',').'</td>
                    <td align="center" width="50">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    <td align="center" width="35">'.number_format($row[$i]['VAT'],2,'.',',').'</td>
                    <td align="right" width="80">'.number_format($row[$i]['rowSubTotal'],2,'.',',').'</td>
                    </tr>';
                $sn++;
                //}
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table><table border="0" cellpadding="5">';
            $html.= '
                <tr color="black">
                    <td width="220" colspan="2" style="border: 1px solid black;">Terms & Conditions :</td>
                    <td width="175" colspan="4" style="border: 1px solid black;">Vendor Note :</td>
                    <td width="85" colspan="2" align="right" style="border: 1px solid black;">Total(AED)</td>
                    <td width="80" align="right" style="border: 1px solid black;">'.number_format($subtotal,2,'.',',').'</td>
                </tr>';
            $terms_condition=isset($data['TermsAndCondition'])?$data['TermsAndCondition']:'N.A.';
            $vendor_note=isset($data['supplierNote'])?$data['supplierNote']:'N.A.';
            $html.= '
                <tr color="black">
                    <td width="220" colspan="2" rowspan="2" style="border: 1px solid black;">'.$data['TermsAndCondition'].'</td>
                    <td width="175" colspan="4" rowspan="2" style="border: 1px solid black;">'.$data['supplierNote'].'</td>
                    <td width="85" colspan="2" align="right" style="border: 1px solid black;">VAT (5%)</td>
                    <td width="80" align="right" style="border: 1px solid black;">'.number_format($data['totalVat'],2,'.',',').'</td>
                </tr>';
            $html.= '
                <tr color="black">
                    <td width="85" colspan="2" align="right" style="border: 1px solid black;">Grand Total(AED)</td>
                    <td width="80" align="right" style="border: 1px solid black;">'.number_format($data['grandTotal'],2,'.',',').'</td>
                </tr>';
            $html.='</table>';
            $pdf::writeHTML($html, true, false, true, false, '');

            $amount_in_words=Str::getUAECurrency($data['grandTotal']);
            $pdf::Cell(95, 5, 'Amount in Words : '.$amount_in_words,'',0,'L');
            $pdf::Ln(6);
            $pdf::Ln(6);
            $pdf::Ln(6);
            $pdf::Ln(6);

            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $pdf::Cell(95, 5, 'Accepted By (Name & Signature) :','',0,'C');
            $pdf::Cell(95, 5, 'Issued By (Name & Signature): ','',0,'C');

            $pdf::lastPage();
            $time=time();
            if (!file_exists('/app/public/purchase_order_files/')) {
                mkdir('/app/public/purchase_order_files/', 0777, true);
            }
            $fileLocation = storage_path().'/app/public/purchase_order_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/purchase_order_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            //$url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function supplierSaleDetails($Id)
    {
        $sales = Purchase::with('supplier','purchase_details')->where([
                'supplier_id'=>$Id,
                'IsPaid'=> false,
            ])->get();
        return response()->json($sales);
    }

    public function getPurchasePaymentDetail($Id)
    {
        $rows_string='';
        $i=0;
        $payment_detail=SupplierPaymentDetail::where('purchase_id',$Id)->get();
        if($payment_detail->first())
        {
            foreach ($payment_detail as $single)
            {
                $parent=SupplierPayment::where('id',$single->supplier_payment_id)->first();
                $rows_string.='<tr>';
                $rows_string.='<td>'.++$i.'</td>';
                $rows_string.='<td>'.$single->amountPaid??"NA".'</td>';
                $rows_string.='<td>'.date('d-M-Y',strtotime($parent->transferDate))??"NA".'</td>';
                $rows_string.='<td>'.$parent->referenceNumber??"NA".'</td>';
                $rows_string.='<td>'.$parent->payment_type??"NA".'</td>';
                $rows_string.='<td>Payment</td>';
                $rows_string.='</tr>';
            }
        }
        $payment_detail=SupplierAdvanceDetail::where('purchase_id',$Id)->get();
        if($payment_detail->first())
        {
            foreach ($payment_detail as $single)
            {
                $parent=SupplierAdvance::where('id',$single->supplier_advances_id)->first();
                $rows_string.='<tr>';
                $rows_string.='<td>'.++$i.'</td>';
                $rows_string.='<td>'.$single->amountPaid??"NA".'</td>';
                $rows_string.='<td>'.date('d-M-Y',strtotime($parent->TransferDate))??"NA".'</td>';
                $rows_string.='<td>'.$parent->receiptNumber??"NA".'</td>';
                $rows_string.='<td>'.$parent->paymentType??"NA".'</td>';
                $rows_string.='<td>Advance</td>';
                $rows_string.='</tr>';
            }
        }

        $html='';
        $purchase=Purchase::where('id',$Id)->with('supplier','purchase_details')->first();
        if($purchase)
        {
            $html='<div class="row"><div class="col-md-12"><label>Supplier Name : '.$purchase->supplier->Name.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>Purchase Date : '.date('d-M-Y',strtotime($purchase->PurchaseDate)).'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>PAD# : '.$purchase->purchase_details[0]->PadNumber.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>LPO# : '.$purchase->referenceNumber.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>Amount : '.$purchase->grandTotal.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>Paid : '.$purchase->paidBalance.'</label></div></div>';
            $html.='<div class="row"><div class="col-md-12"><label>Remaining : '.$purchase->remainingBalance.'</label></div></div>';
            $html.='<table class="table table-sm"><thead><th>SR</th><th>Paid Amount</th><th>Date</th><th>REF#</th><th>PaymentMode</th><th>Type</th></thead><tbody>';
            $html.=$rows_string;
            $html.='</tbody>';
        }
        else
        {
            $html.='<h1>Purchse Record Not Fount.</h1>';
        }
        return Response()->json($html);
    }

    public function getAveragePurchasePrice($Id)
    {
        if(trim($Id)!='')
        {
            $dt = $Id.'-01';
            $start_date = date("Y-m-01", strtotime($dt));
            $end_date = date("Y-m-t", strtotime($dt));
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_purchase_amount=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowSubTotal');
            if($total_purchase_qty!=0)
            {
                $average_price=$total_purchase_amount/$total_purchase_qty;
                return Response()->json(round($average_price,2));
            }
            else
            {
                return Response()->json(round(0,2));
            }
        }
        else
            return Response()->json(0);
    }
}
