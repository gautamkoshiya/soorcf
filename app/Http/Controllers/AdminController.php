<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\Financer;
use App\Models\LoanMaster;
use App\Models\OtherStock;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Supplier;
use App\Models\Task;
use App\Models\User;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;

class AdminController extends Controller
{
    public function index()
    {
        /*$dashboard['total_users']=User::all()->count();
        $dashboard['total_sales_today']=Sale::all()->where('createdDate','=',date('Y-m-d'))->count();
        $dashboard['total_purchase_today']=Purchase::all()->where('createdDate','=',date('Y-m-d'))->count();
        $in_sum=OtherStock::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('in');
        $out_sum=OtherStock::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('out');
        $dashboard['other_stock']=$in_sum-$out_sum;
        $admin=array();
        if(session('role_name')=='admin' || session('role_name')=='superadmin')
        {
            // getting latest closing for all customer from account transaction table
            $result_array=array();
            $customers=Customer::where('company_id',session('company_id'))->where('company_type_id','!=',4)->where('isActive',1)->get();
            foreach ($customers as $customer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                if($diff!=0)
                {
                    $temp=array('Differentiate'=>$diff);
                    $result_array[]=$temp;
                    unset($temp);
                }
            }
            $row=array_column($result_array,'Differentiate');
            $total_receivable=array_sum($row);

            // getting latest closing for all suppliers from account transaction table
            $result_array=array();
            $suppliers=Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
            foreach ($suppliers as $supplier)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=array_column($result_array,'Differentiate');
            $total_payable=array_sum($row);

            //total purchase quantity
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            //total sales quantity
            $total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $stock_qty=$total_purchase_qty-$total_sales_qty;

            //today's data
            $today_total_sale=Sale::where('company_id','=',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->sum('grandTotal');
            $today_credit_sale=Sale::where('company_id','=',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->sum('remainingBalance');
            $today_cash_sale=Sale::where('company_id','=',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->sum('paidBalance');

            $total_sales_qty=SaleDetail::where('createdDate','>=',date('Y-m-d').' 00:00:00')->where('createdDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $total_purchase_qty=PurchaseDetail::where('createdDate','>=',date('Y-m-d').' 00:00:00')->where('createdDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $total_purchase_amount=PurchaseDetail::where('createdDate','>=',date('Y-m-d').' 00:00:00')->where('createdDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('rowSubTotal');
            $total_expense_amount=Expense::where('expenseDate','>=',date('Y-m-d').' 00:00:00')->where('expenseDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('grandTotal');

            $admin['today_total_sale']=$today_total_sale;
            $admin['today_credit_sale']=$today_credit_sale;
            $admin['today_cash_sale']=$today_cash_sale;

            $admin['today_sale_qty']=$total_sales_qty;
            $admin['today_purchase_qty']=$total_purchase_qty;
            $admin['today_purchase_amount']=$total_purchase_amount;
            $admin['today_expense_amount']=$total_expense_amount;

            $admin['total_receivable']=$total_receivable;
            $admin['total_payable']=$total_payable;
            $admin['stock_qty']=$stock_qty;
            $admin['cash_on_hand']=CashTransaction::select('Differentiate')->where('company_id',session('company_id'))->get()->last();
            //$admin['loan_payable']=LoanMaster::where('loanType',1)->where('company_id',session('company_id'))->sum('inward_RemainingBalance');
            $admin['loan_receivable']=LoanMaster::where('loanType',0)->where('company_id',session('company_id'))->sum('outward_RemainingBalance');

            // getting latest closing for all financer from account transaction table
            $result_array=array();
            $financers=Financer::where('company_id',session('company_id'))->get();
            foreach ($financers as $financer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$financer->id)->whereNull('deleted_at')->sum('Credit');
                $debit_sum=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$financer->id)->whereNull('deleted_at')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=array_column($result_array,'Differentiate');
            $total_payable=array_sum($row);
            $admin['loan_payable']=$total_payable;

            //tasks
            $admin['tasks']=Task::with(['master_task'=>function($q){$q->select('id','Name','user_id','StartDate','EndDate','CompletionTime');},'master_task.user'])->where('assigned_to',session('user_id'))->where('Date','<=',date('Y-m-d'))->get();
            //echo "<pre>";print_r($admin['tasks']);die;
        }*/
        //return view('admin.index',compact('dashboard','admin'));
        return view('admin.index');
    }

    public function login()
    {
        return view('admin.user.login');
    }

    public  function register()
    {
         return view('admin.user.register');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function GetDashboardData($id)
    {
        $admin=array();
        //echo "<pre>";print_r($admin['tasks']);die;
        $date = new DateTime(date('Y-m-d')); // Y-m-d
        $date_plus_one_month=$date->add(new DateInterval('P30D'));
        $date_plus_one_month=$date_plus_one_month->format('Y-m-d');

        $first_day_this_month = date('Y-m-01');
        $last_day_this_month  = date('Y-m-t');
        $start = strtotime($first_day_this_month);
        $end = strtotime(date('Y-m-d'));
        $days_between = ceil(abs($end - $start) / 86400);
        $days_between++;
        if($id==1)
        {
            $in_sum=OtherStock::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('in');
            $out_sum=OtherStock::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('out');
            $admin['other_stock']=$in_sum-$out_sum;

            // getting latest closing for all customer from account transaction table
            $result_array=array();
            $customers=Customer::where('company_id',session('company_id'))->where('company_type_id','!=',4)->where('isActive',1)->get();
            foreach ($customers as $customer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                if($diff!=0)
                {
                    $temp=array('Differentiate'=>$diff);
                    $result_array[]=$temp;
                    unset($temp);
                }
            }
            $row=array_column($result_array,'Differentiate');
            $total_receivable=array_sum($row);

            // getting latest closing for all suppliers from account transaction table
            $result_array=array();
            $suppliers=Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
            foreach ($suppliers as $supplier)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=array_column($result_array,'Differentiate');
            $total_payable=array_sum($row);

            //total purchase quantity
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            //total sales quantity
            $total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $stock_qty=$total_purchase_qty-$total_sales_qty;

            $admin['total_receivable']=$total_receivable;
            $admin['total_payable']=$total_payable;
            $admin['stock_qty']=$stock_qty;
            $admin['cash_on_hand']=CashTransaction::select('Differentiate')->where('company_id',session('company_id'))->get()->last();

            $admin['loan_receivable']=LoanMaster::where('loanType',0)->where('company_id',session('company_id'))->sum('outward_RemainingBalance');

            // getting latest closing for all financer from account transaction table
            $result_array=array();
            $financers=Financer::where('company_id',session('company_id'))->get();
            foreach ($financers as $financer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$financer->id)->whereNull('deleted_at')->sum('Credit');
                $debit_sum=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$financer->id)->whereNull('deleted_at')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=array_column($result_array,'Differentiate');
            $total_payable=array_sum($row);
            $admin['loan_payable']=$total_payable;
        }
        if($id==2)
        {
            //total purchase quantity
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            //total sales quantity
            $total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $stock_qty=$total_purchase_qty-$total_sales_qty;

            //today's data
            $today_total_sale=Sale::where('company_id','=',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->sum('grandTotal');
            $today_credit_sale=Sale::where('company_id','=',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->sum('remainingBalance');
            $today_cash_sale=Sale::where('company_id','=',session('company_id'))->where('isActive',1)->where('SaleDate',date('Y-m-d'))->sum('paidBalance');

            $total_sales_qty=SaleDetail::where('createdDate','>=',date('Y-m-d').' 00:00:00')->where('createdDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $total_purchase_qty=PurchaseDetail::where('createdDate','>=',date('Y-m-d').' 00:00:00')->where('createdDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $total_purchase_amount=PurchaseDetail::where('createdDate','>=',date('Y-m-d').' 00:00:00')->where('createdDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('rowSubTotal');
            $total_expense_amount=Expense::where('expenseDate','>=',date('Y-m-d').' 00:00:00')->where('expenseDate','<=',date('Y-m-d').' 23:59:59')->where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('grandTotal');

            $admin['today_total_sale']=$today_total_sale;
            $admin['today_credit_sale']=$today_credit_sale;
            $admin['today_cash_sale']=$today_cash_sale;

            $admin['today_sale_qty']=$total_sales_qty;
            $admin['today_purchase_qty']=$total_purchase_qty;
            $admin['today_purchase_amount']=$total_purchase_amount;
            $admin['today_expense_amount']=$total_expense_amount;


            //tasks
            $admin['tasks']=Task::with(['master_task'=>function($q){$q->select('id','Name','user_id','StartDate','EndDate','CompletionTime');},'master_task.user'])->where('assigned_to',session('user_id'))->where('Date','<=',date('Y-m-d'))->get();
        }
        if($id==3)
        {
            //start of expense analysis
            $begin = new DateTime($first_day_this_month);
            $end   = new DateTime($last_day_this_month);
            $all_dates=array();
            $all_expenses=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $expense=Expense::where('company_id',session('company_id'))->where('expenseDate',$date)->sum('grandTotal');
                $all_expenses[]=$expense;
            }
            $sum_of_expenses=array_sum($all_expenses);
            $average_of_expenses=$sum_of_expenses/$days_between;

            $expense_analysis_html='<table border="1" class="table table-responsive"><thead>';
            foreach($all_dates as $single)
            {
                $expense_analysis_html.='<th>'.date('d', strtotime($single)).'</th>';
            }
            $expense_analysis_html.='</thead><tbody><tr>';
            foreach($all_expenses as $single)
            {
                $expense_analysis_html.='<td>'.$single.'</td>';
            }
            $expense_analysis_html.='</tr></tbody></table>';
            $admin['expense_analysis']=$expense_analysis_html;
            $admin['sum_of_expense']=' SUM : '.round($sum_of_expenses);
            $admin['average_of_expense']=' AVE :'.round($average_of_expenses);
            //end of expense analysis

            //start of expense analysis by category
            $begin = new DateTime($first_day_this_month);
            $end   = new DateTime($last_day_this_month);
            $expense_category=ExpenseCategory::all();
            $final_array=array();
            foreach($expense_category as $item)
            {
                $ids=ExpenseDetail::select('expense_id')->where('company_id',session('company_id'))->where('expense_category_id',$item->id)->whereBetween('expenseDate', [$begin, $end])->get();
                $ids = json_decode(json_encode($ids), true);
                $ids = array_column($ids,'expense_id');
                $temp=Expense::where('company_id',session('company_id'))->whereIn('id',$ids)->whereBetween('expenseDate', [$begin, $end])->sum('grandTotal');
                if($temp!=0)
                {
                    $tmp_array=[
                        'category_name'=>$item->Name,
                        'total_expense'=>$temp,
                    ];
                    $final_array[]=$tmp_array;
                }
            }

            $expense_analysis_html='<table border="1" class="table table-responsive"><tbody><tr><td>Expense Category</td>';
            foreach($final_array as $single)
            {
                $expense_analysis_html.='<td><b>'.($single['category_name']).'</b></td>';
            }
            $expense_analysis_html.='</tr><tr><td>Expense Amount</td>';
            foreach($final_array as $single)
            {
                $expense_analysis_html.='<td>'.($single['total_expense']).'</td>';
            }
            $expense_analysis_html.='</tr></tbody></table>';
            $admin['expense_analysis_by_category']=$expense_analysis_html;
            //end of expense analysis by category
        }
        if($id==4)
        {
            //start of sales analysis
            $begin = new DateTime($first_day_this_month);
            $end   = new DateTime($last_day_this_month);
            $all_dates=array();
            $all_sales=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $qty=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('Quantity');
                $all_sales[]=$qty;
            }
            $sum_of_sales=array_sum($all_sales);
            $average_of_sales=$sum_of_sales/$days_between;

            $sales_analysis_html='<table border="1" class="table table-responsive"><thead>';
            foreach($all_dates as $single)
            {
                $sales_analysis_html.='<th>'.date('d', strtotime($single)).'</th>';
            }
            $sales_analysis_html.='</thead><tbody><tr>';
            foreach($all_sales as $single)
            {
                $sales_analysis_html.='<td>'.$single.'</td>';
            }
            $sales_analysis_html.='</tr></tbody></table>';
            $admin['sales_analysis']=$sales_analysis_html;
            $admin['sum_of_sales']=' SUM : '.round($sum_of_sales);
            $admin['average_of_sales']=' AVE :'.round($average_of_sales);
            //end of sales analysis

            //start of purchase analysis
            $begin = new DateTime($first_day_this_month);
            $end   = new DateTime($last_day_this_month);
            $all_dates=array();
            $all_purchase=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $qty=PurchaseDetail::where('company_id',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('Quantity');
                $all_purchase[]=$qty;
            }
            $sum_of_purchase=array_sum($all_purchase);
            $average_of_purchase=$sum_of_purchase/$days_between;

            $purchase_analysis_html='<table border="1" class="table table-responsive"><thead>';
            foreach($all_dates as $single)
            {
                $purchase_analysis_html.='<th>'.date('d', strtotime($single)).'</th>';
            }
            $purchase_analysis_html.='</thead><tbody><tr>';
            foreach($all_purchase as $single)
            {
                $purchase_analysis_html.='<td>'.$single.'</td>';
            }
            $purchase_analysis_html.='</tr></tbody></table>';
            $admin['purchase_analysis']=$purchase_analysis_html;
            $admin['sum_of_purchase']=' SUM : '.round($sum_of_purchase);
            $admin['average_of_purchase']=' AVE :'.round($average_of_purchase);
            //end of purchase analysis
        }
        if($id==5)
        {
            //start of receivable analysis
            $begin = new DateTime($first_day_this_month);
            $end   = new DateTime($last_day_this_month);
            $all_dates=array();
            $all_receivable=array();
            $all_customers=Customer::select('id')->where('company_id',session('company_id'))->where('isActive',1)->where('company_type_id','!=',4)->get();

            $row=json_decode(json_encode($all_customers), true);
            $all_ids=array_column($row,'id');
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $credit_sum=AccountTransaction::whereIn('customer_id',$all_ids)->where('createdDate','<=',$date)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Credit');
                $debit_sum=AccountTransaction::whereIn('customer_id',$all_ids)->where('createdDate','<=',$date)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $all_receivable[]=$diff;
            }
            $receivable_analysis_html='<table border="1" class="table table-responsive"><thead>';
            foreach($all_dates as $single)
            {
                $receivable_analysis_html.='<th>'.date('d', strtotime($single)).'</th>';
            }
            $receivable_analysis_html.='</thead><tbody><tr>';
            foreach($all_receivable as $single)
            {
                $receivable_analysis_html.='<td>'.round($single).'</td>';
            }
            $receivable_analysis_html.='</tr></tbody></table>';
            $admin['receivable_analysis']=$receivable_analysis_html;
            //end of receivable analysis
        }
        if($id==6)
        {
            //start of payable analysis
            $begin = new DateTime($first_day_this_month);
            $end   = new DateTime($last_day_this_month);
            $all_dates=array();
            $all_payable=array();
            $all_suppliers=Supplier::select('id')->where('company_id',session('company_id'))->where('isActive',1)->where('company_type_id',2)->get();

            $row=json_decode(json_encode($all_suppliers), true);
            $all_ids=array_column($row,'id');
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $credit_sum=AccountTransaction::whereIn('supplier_id',$all_ids)->where('createdDate','<=',$date)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Credit');
                $debit_sum=AccountTransaction::whereIn('supplier_id',$all_ids)->where('createdDate','<=',$date)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $all_payable[]=$diff;
            }
            $payable_analysis_html='<table border="1" class="table table-responsive"><thead>';
            foreach($all_dates as $single)
            {
                $payable_analysis_html.='<th>'.date('d', strtotime($single)).'</th>';
            }
            $payable_analysis_html.='</thead><tbody><tr>';
            foreach($all_payable as $single)
            {
                $payable_analysis_html.='<td>'.round($single).'</td>';
            }
            $payable_analysis_html.='</tr></tbody></table>';
            $admin['payable_analysis']=$payable_analysis_html;
            //end of payable analysis
        }
        if($id==7)
        {
            //visa_about_to_expire
            $upcoming_visa_exp=Employee::select('id','Name','visa_expire_date')->where('company_id',session('company_id'))->where('visa_expire_date','<',$date_plus_one_month)->get();
            $visa_about_to_expire_html='<table border="1" class="table table-responsive"><thead><th>Employee Name</th><th>Expiry Date</th></thead><tbody>';
            foreach($upcoming_visa_exp as $single)
            {
                $visa_about_to_expire_html.='<tr><td>'.($single->Name).'</td>';
                $visa_about_to_expire_html.='<td>'.date('d-M-Y', strtotime($single->visa_expire_date)).'</td></tr>';
            }
            $visa_about_to_expire_html.='</tbody></table>';
            $admin['visa_about_to_expire']=$visa_about_to_expire_html;
            //end of visa_about_to_expire
        }
        if($id==8)
        {
            //driving_licence_about_to_expire
            $upcoming_driving_licence_exp=Employee::select('id','Name','driving_licence_expire_date')->where('company_id',session('company_id'))->where('driving_licence_expire_date','<',$date_plus_one_month)->get();
            $driving_licence_about_to_expire_html='<table border="1" class="table table-responsive"><thead><th>Employee Name</th><th>Expiry Date</th></thead><tbody>';
            foreach($upcoming_driving_licence_exp as $single)
            {
                $driving_licence_about_to_expire_html.='<tr><td>'.($single->Name).'</td>';
                $driving_licence_about_to_expire_html.='<td>'.date('d-M-Y', strtotime($single->driving_licence_expire_date)).'</td></tr>';
            }
            $driving_licence_about_to_expire_html.='</tbody></table>';
            $admin['driving_licence_about_to_expire']=$driving_licence_about_to_expire_html;
            //end of driving_licence_about_to_expire
        }
        return Response()->json($admin);
    }
}
