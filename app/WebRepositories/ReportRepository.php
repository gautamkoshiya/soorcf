<?php


namespace App\WebRepositories;


use App\Http\Resources\Expense\ExpenseResource;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Http\Resources\Sales\SalesResource;
use App\MISC\CustomeFooter;
use App\MISC\MYPDF;
use App\Models\AccountTransaction;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\CashTransaction;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\CustomerAdvanceBooking;
use App\Models\CustomerPrice;
use App\Models\Deposit;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDetail;
use App\Models\FileManager;
use App\Models\Financer;
use App\Models\Investor;
use App\Models\LoanMaster;
use App\Models\LoginLog;
use App\Models\MeterReading;
use App\Models\OtherStock;
use App\Models\PaymentReceive;
use App\Models\Project;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Receivable_summary_log;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Supplier;
use App\Models\SupplierAdvance;
use App\Models\SupplierPayment;
use App\Models\Task;
use App\Models\TaskMaster;
use App\Models\UpdateNote;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Withdrawal;
use App\WebRepositories\Interfaces\IReportRepositoryInterface;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use PDF;

class ReportRepository implements IReportRepositoryInterface
{
    public function GetCustomerStatement()
    {
        return view('admin.report.customer_statement');
    }

    public function GetDetailCustomerStatement()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        return view('admin.report.customer_detailed_statement',compact('customers'));
    }

    public function GetSupplierStatement()
    {
        return view('admin.report.supplier_statement');
    }

    public function GetPaidAdvancesSummary()
    {
        return view('admin.report.paid_advance_summary');
    }

    public function GetReceivedAdvancesSummary()
    {
        return view('admin.report.received_advance_summary');
    }

    public function GetDetailSupplierStatement()
    {
        $suppliers = Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
        return view('admin.report.supplier_detailed_statement',compact('suppliers'));
    }

    public function SalesReport()
    {
        return view('admin.report.sales_report');
    }

    public function PurchaseReport()
    {
        $suppliers = Supplier::where('company_type_id',2)->where('company_id',session('company_id'))->get();
        return view('admin.report.purchase_report',compact('suppliers'));
    }

    public function ExpenseReport()
    {
        $expense_category= ExpenseCategory::get();
        return view('admin.report.expense_report',compact('expense_category'));
    }

    public function ExpenseVatReport()
    {
        $companies= Company::get();
        return view('admin.report.expense_vat_report',compact('companies'));
    }

    public function CashReport()
    {
        return view('admin.report.cash_report');
    }

    public function BankReport()
    {
        $banks= Bank::get();
        return view('admin.report.bank_report',compact('banks'));
    }

    public function GeneralLedger()
    {
        return view('admin.report.general_ledger_report');
    }

    public function Profit_loss()
    {
        return view('admin.report.profit_loss_report');
    }

    public function Garage_value()
    {
        return view('admin.report.garage_value_report');
    }

    public function SalesReportByVehicle()
    {
        $vehicles = Vehicle::where('company_id',session('company_id'))->whereNull('deleted_at')->get();
        return view('admin.report.sales_report_by_vehicle',compact('vehicles'));
    }

    public function SalesReportByCustomer()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        return view('admin.report.sales_report_by_customer',compact('customers'));
    }

    public function PrintSalesReportByCustomer(Request $request)
    {
        //echo "<pre>";print_r($request->all());die;
        if ($request->fromDate!='' && $request->toDate!='' &&  $request->customer_id!='all')
        {
            if($request->filter=='with')
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('totalVat', '!=', 0.00)->where('isActive', '!=', 0));
            }
            elseif($request->filter=='without')
            {
                $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('totalVat', '==', 0.00)->where('isActive', '!=', 0));
            }
            else
            {
                //$sales=SalesResource::collection(Sale::with(['sale_details'=>function($query){$query->whereIn(['138']);}])->get()->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('customer_id','==',$request->customer_id)->where('isActive', '!=', 0));
                $sales=Sale::where('company_id',session('company_id'))->where('customer_id',$request->customer_id)->where('isActive', '=', 1)->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->first();
                //echo "<pre>";print_r($sales);die;
                //$all_bank_transactions=BankTransaction::where('company_id',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('deleted_at','=',NULL)->where('bank_id','=',$request->bank_id)->orderBy('createdDate')->get();
            }
        }
        else
        {
            if($request->filter=='with')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '!=', 0.00)->where('isActive', '!=', 0));
            }
            elseif($request->filter=='without')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '==', 0.00)->where('isActive', '!=', 0));
            }
            else
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('isActive', '!=', 0));

            }
        }
//        echo "<pre>";print_r($sales);die;

        if($sales->first())
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $row=json_decode(json_encode($sales), true);
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);

            $date='';
            $html='<b>Customer Name : </b>'.$request->customer_name;

            $pdf::writeHTML($html, false, false, false, false, '');
            $pdf::Ln(8);

//            $pdf::Cell(95,5,$html,'',0,'L');
//            $pdf::Cell(95,5,$date,'',0,'R');
//            $pdf::Ln(8);

            $booking_status_string='';
            $booking_rem_quantity=CustomerAdvanceBooking::where('customer_id',$request->customer_id)->sum('remainingQuantity');
            if($booking_rem_quantity>0)
            {
                $booking_status_string=' Advance booked Quantity : '.$booking_rem_quantity;
            }
            else
            {
                $sum_of_overfilled_qty=SaleDetail::where('customer_id',$request->customer_id)->whereNull('deleted_at')->where('isActive',1)->whereNotNull('booking_shortage')->sum('Quantity');
                if($sum_of_overfilled_qty>0)
                {
                    $booking_status_string='Overfilled Quantity is : '.$sum_of_overfilled_qty;
                }
            }
            if($booking_status_string!='')
            {
                //$pdf::writeHTML('<hr style="height:1px;border-width:0;color:gray;background-color:gray">', false, false, true, false, '');
                $pdf::SetFont('helvetica', 'B', 10);
                $html='ADVANCE BOOKING REPORT';
                $date=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));

                $pdf::Cell(95,5,$html,'',0,'L');
                $pdf::Cell(95,5,$date,'',0,'R');
                $pdf::Ln(6);

                $booking=CustomerAdvanceBooking::with(['booking_details'])->where('company_id',session('company_id'))->where('customer_id',$request->customer_id)->whereBetween('BookingDate',[$request->fromDate,$request->toDate])->get();
                if($booking->first())
                {
                    $pdf::SetFont('helvetica', '', 8);
                    $row=json_decode(json_encode($booking), true);
                    if(!empty($row))
                    {
                        //echo "<pre>";print_r($row);die;
                        //booking heading
                        $html = '<table border="0.5" cellpadding="1">
                        <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); font-weight: bold;font-size: 10px;">
                            <th align="center" width="60">Code#</th>
                            <th align="center" width="70">Date</th>
                            <th align="center" width="70">Booked Qty</th>
                            <th align="center" width="70">Rem. Qty</th>
                            <th align="center" width="70">Used</th>
                            <th align="center" width="40">Rate</th>
                            <th align="center" width="155">Description</th>
                        </tr>';
                        $sum_of_booked_qty=0;
                        $sum_of_rem_qty=0;
                        $sum_of_used_qty=0;
                        for($i=0;$i<count($row);$i++)
                        {
                            $html .= '<tr>
                            <td align="center" width="60">'.($row[$i]['code']).'</td>
                            <td align="center" width="70">'.(date('d-m-Y', strtotime($row[$i]['BookingDate']))).'</td>
                            <td align="right" width="70">'.(number_format($row[$i]['totalQuantity'], 2, '.', ',')).'</td>
                            <td align="right" width="70">'.(number_format($row[$i]['remainingQuantity'], 2, '.', ',')).'</td>
                            <td align="right" width="70">'.(number_format($row[$i]['consumedQuantity'], 2, '.', ',')).'</td>
                            <td align="right" width="40">'.(number_format($row[$i]['Rate'], 2, '.', ',')).'</td>
                            <td align="left" width="155">'.($row[$i]['Description']).'</td>
                            </tr>';
                            $sum_of_booked_qty+=$row[$i]['totalQuantity'];
                            $sum_of_rem_qty+=$row[$i]['remainingQuantity'];
                            $sum_of_used_qty+=$row[$i]['consumedQuantity'];

                            /*$html = '<table border="0.5" cellpadding="1">
                            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); font-weight: bold;font-size: 8px;">
                                <th align="center" width="60"></th>
                                <th align="center" width="70">Inv#</th>
                                <th align="center" width="70"></th>
                                <th align="center" width="40">Qty</th>
                                <th align="center" width="130">#</th>
                            </tr>';
                            for($j=0;$j<count($row[$i]['booking_details']);$j++)
                            {
                                $html .= '<tr>
                            <td align="center" width="60"></td>
                            <td align="center" width="70">'.($row[$i]['booking_details'][$j]['PadNumber']).'</td>
                            <td align="center" width="70"></td>
                            <td align="right" width="40">'.(number_format($row[$i]['booking_details'][$j]['Quantity'], 2, '.', ',')).'</td>
                            <td align="center" width="130">'.($j+1).'</td>

                            </tr>';
                            }
                            $html .= '</table>';
                            $pdf::writeHTML($html, true, false, false, false, '');*/
                        }
                        $html .= '<tr  style="font-weight: bold;font-size: 9px;">
                             <td width="130" align="right" colspan="2">Total : </td>
                             <td width="70" align="right" color="green">'.number_format($sum_of_booked_qty, 2, '.', ',').'</td>
                             <td width="70" align="right" color="#ff7300">'.number_format($sum_of_rem_qty, 2, '.', ',').'</td>
                             <td width="70" align="right" color="red">'.number_format($sum_of_used_qty, 2, '.', ',').'</td>
                             <td width="195" align="right" colspan="2"></td>
                         </tr>';
                        $html .= '</table>';
                        $pdf::writeHTML($html, false, false, false, false, '');
                    }
                }
                //$pdf::writeHTML('<hr style="border-bottom: dotted 1px #000;height:1px;border-width:0;color:gray;background-color:gray">', false, false, true, false, '');
            }
            $pdf::Ln(6);



            $pdf::SetFont('helvetica', 'B', 10);
            $html='SALES REPORT BY VEHICLE';
            $date=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));

            $pdf::Cell(95,5,$html,'',0,'L');
            $pdf::Cell(95,5,$date,'',0,'R');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', 'B', 8);
            if($request->customer_id==='all')
            {
                //for customer selection
                $customer_ids=array();
                $customer_name=array();
                foreach ($row as $item)
                {
                    $customer_ids[]=$item['api_customer']['id'];
                    $customer_name[]=$item['api_customer']['Name'];
                }
                $customer_ids=array_unique($customer_ids);
                $customer_name=array_unique($customer_name);
                $customer_ids=array_values($customer_ids);
                $customer_name=array_values($customer_name);

                for($i=0;$i<count($customer_ids);$i++)
                {
                    $sub_total_sum=0.0;
                    $paid_total_sum=0.0;
                    $balance_total_sum=0.0;
                    $vat_sum=0.0;
                    $qty_sum=0.0;

                    $customer_title='<u><b>'.'Customer :- '.$customer_name[$i].'</b></u>';
                    $pdf::SetFont('helvetica', 'B', 10);
                    $pdf::writeHTMLCell(0, 0, '', '', $customer_title,0, 1, 0, true, 'L', true);

                    $pdf::SetFont('helvetica', '', 8);
                    //code will come here
                    $html = '<table border="0.5" cellpadding="3">
                    <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                        <th align="center" width="60">Date</th>
                        <th align="center" width="60">S.No.</th>
                        <th align="center" width="50">Vehicle</th>
                        <th align="center" width="40">Qty</th>
                        <th align="center" width="40">Rate</th>
                        <th align="center" width="45">Total</th>
                        <th align="center" width="40">VAT</th>
                        <th align="center" width="50">SubTotal</th>
                        <th align="center" width="50">Paid</th>
                        <th align="center" width="50">Balance</th>
                    </tr>';
                    for ($j=0;$j<count($row);$j++)
                    {
                        if ($customer_ids[$i]==$row[$j]['api_customer']['id'])
                        {
                            $sub_total_sum += $row[$j]['sale_details'][0]['rowSubTotal'];
                            $paid_total_sum += $row[$j]['paidBalance'];
                            $balance_total_sum += $row[$j]['remainingBalance'];
                            $current_vat_amount = $row[$j]['sale_details'][0]['rowTotal'] * $row[$j]['sale_details'][0]['VAT'] / 100;
                            $vat_sum += $current_vat_amount;
                            $qty_sum += $row[$j]['sale_details'][0]['Quantity'];
                            $html .= '<tr>
                                <td align="center" width="60">' . (date('d-M-Y', strtotime($row[$j]['SaleDate']))) . '</td>
                                <td align="center" width="60">' . ($row[$j]['sale_details'][0]['PadNumber']) . '</td>
                                <td align="center" width="50">' . ($row[$j]['sale_details'][0]['api_vehicle']['registrationNumber']) . '</td>
                                <td align="right" width="40">' . ($row[$j]['sale_details'][0]['Quantity']) . '</td>
                                <td align="center" width="40">' . ($row[$j]['sale_details'][0]['Price']) . '</td>
                                <td align="center" width="45">' . ($row[$j]['sale_details'][0]['rowTotal']) . '</td>
                                <td align="right" width="40">' . (number_format($current_vat_amount, 2, '.', ',')) . '</td>
                                <td align="right" width="50">' . ($row[$j]['sale_details'][0]['rowSubTotal']) . '</td>
                                <td align="right" width="50">' . ($row[$j]['paidBalance']) . '</td>
                                <td align="right" width="50">' . ($row[$j]['remainingBalance']) . '</td>
                                </tr>';
                        }
                    }
                    $html .= '
                         <tr color="red">
                             <td width="110" align="right" colspan="2">Total : </td>
                             <td width="40" align="right">'. number_format($qty_sum, 2, '.', ',') .'</td>
                             <td width="40"></td>
                             <td width="45"></td>
                             <td width="40" align="right">'. number_format($vat_sum, 2, '.', ',') .'</td>
                             <td width="50" align="right">'. number_format($sub_total_sum, 2, '.', ',') .'</td>
                             <td width="50" align="right">'. number_format($paid_total_sum, 2, '.', ',') .'</td>
                             <td width="50" align="right">'. number_format($balance_total_sum, 2, '.', ',') .'</td>
                             <td width="60" align="right"></td>
                         </tr>';
                    $pdf::SetFillColor(255, 0, 0);
                    $html .= '</table>';
                    //code will come here

                    $pdf::writeHTML($html, true, false, false, false, '');
                }
            }
            else
            {
                $pdf::SetFont('helvetica', '', 8);
                //if($request->vehicle_id==='all')
                if(in_array('all',$request->vehicle_id))
                {
                    $customer_all_vehicle=Vehicle::where('customer_id',$request->customer_id)->get();
                    $customer_all_vehicle=$customer_all_vehicle->sortBy('registrationNumber');
                    foreach($customer_all_vehicle as $single)
                    {
                        $sales_ids=SaleDetail::select('sale_id')->where('vehicle_id',$single->id)->whereBetween('createdDate',[$request->fromDate,$request->toDate])->get();
                        $sales_ids_array[$single->registrationNumber]=json_decode(json_encode($sales_ids), true);
                    }
                    //echo "<pre>";print_r($sales_ids_array);die;
                    $level_zero_qty_sum=0;
                    $level_zero_total_sum=0;
                    $level_zero_sub_total_sum=0;
                    $level_zero_vat_sum=0;
                    $level_zero_paid_sum=0;
                    $level_zero_balance_sum=0;
                    foreach($sales_ids_array as $key=>$value)
                    {
                        //echo $key;print_r($value);die;
                        //<tr style="background-color: rgb(255, 255, 255); color: rgb(242, 10, 10);">
                        if(!empty($value))
                        {
                            $html = '<table border="0.5" cellpadding="1">
                            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); font-weight: bold;font-size: 10px;">
                                <th align="center" width="60">Vehicle</th>
                                <th align="center" width="70">Inv#</th>
                                <th align="center" width="70">Date</th>
                                <th align="center" width="40">Qty</th>
                                <th align="center" width="40">Rate</th>
                                <th align="center" width="50">Total</th>
                                <th align="center" width="40">VAT</th>
                                <th align="center" width="55">SubTotal</th>
                                <th align="center" width="55">Paid</th>
                                <th align="center" width="55">Balance</th>
                            </tr>';

                            $needed_sales=array_column($value,'sale_id');
                            //echo "<pre>";print_r($needed_sales);die;
                            $data=Sale::with('sale_details','sale_details.vehicle')->whereIn('id', $needed_sales)->where('isActive',1)->get();
                            $row=json_decode(json_encode($data), true);
                            $level_one_qty_sum=0;
                            $level_one_total_sum=0;
                            $level_one_sub_total_sum=0;
                            $level_one_vat_sum=0;
                            $level_one_paid_sum=0;
                            $level_one_balance_sum=0;
                            for ($j=0;$j<count($row);$j++)
                            {
                                $style='';
                                if($row[$j]['sale_details'][0]['booking_shortage']!='')
                                {
                                    $style='style="background-color: #e86507;"';
                                }
                                $current_vat_amount = $row[$j]['sale_details'][0]['rowTotal'] * $row[$j]['sale_details'][0]['VAT'] / 100;
                                $level_one_qty_sum+=$row[$j]['sale_details'][0]['Quantity'];
                                $level_one_total_sum+=$row[$j]['sale_details'][0]['rowTotal'];
                                $level_one_sub_total_sum+=$row[$j]['sale_details'][0]['rowSubTotal'];
                                $level_one_vat_sum+=$current_vat_amount;
                                $level_one_paid_sum+=$row[$j]['paidBalance'];
                                $level_one_balance_sum+=$row[$j]['remainingBalance'];
                                $html .= '<tr '.$style.'>
                                <td align="center" width="60">'.($key).'</td>
                                <td align="center" width="70">'.($row[$j]['sale_details'][0]['PadNumber']).'</td>
                                <td align="center" width="70">'.(date('d-M-Y', strtotime($row[$j]['SaleDate']))).'</td>
                                <td align="right" width="40">'.($row[$j]['sale_details'][0]['Quantity']).'</td>
                                <td align="center" width="40">'.($row[$j]['sale_details'][0]['Price']).'</td>
                                <td align="right" width="50">'.($row[$j]['sale_details'][0]['rowTotal']).'</td>
                                <td align="right" width="40">'.(number_format($current_vat_amount, 2, '.', ',')).'</td>
                                <td align="right" width="55">'.($row[$j]['sale_details'][0]['rowSubTotal']).'</td>
                                <td align="right" width="55">'.($row[$j]['paidBalance']).'</td>
                                <td align="right" width="55">'.($row[$j]['remainingBalance']).'</td>
                                </tr>';

                                $level_zero_qty_sum+=$row[$j]['sale_details'][0]['Quantity'];
                                $level_zero_total_sum+=$row[$j]['sale_details'][0]['rowTotal'];
                                $level_zero_sub_total_sum+=$row[$j]['sale_details'][0]['rowSubTotal'];
                                //$level_zero_sub_total_sum+=$row[$j]['sale_details'][0]['rowSubTotal'];
                                $level_zero_vat_sum+=$current_vat_amount;
                                $level_zero_paid_sum+=$row[$j]['paidBalance'];
                                $level_zero_balance_sum+=$row[$j]['remainingBalance'];
                            }
                            $html .= '<tr color="red" style="font-weight: bold;font-size: 9px;">
                                 <td width="200" align="right" colspan="2">Total : </td>
                                 <td width="40" align="right">'. number_format($level_one_qty_sum, 2, '.', ',') .'</td>
                                 <td width="40"></td>
                                 <td width="50" align="right">'. number_format($level_one_total_sum, 2, '.', ',') .'</td>
                                 <td width="40" align="right">'. number_format($level_one_vat_sum, 2, '.', ',') .'</td>
                                 <td width="55" align="right">'. number_format($level_one_sub_total_sum, 2, '.', ',') .'</td>
                                 <td width="55" align="right">'. number_format($level_one_paid_sum, 2, '.', ',') .'</td>
                                 <td width="55" align="right">'. number_format($level_one_balance_sum, 2, '.', ',') .'</td>
                             </tr>';

                            $html .= '</table>';
                            $pdf::writeHTML($html, true, false, false, false, '');
                        }
                    }
                    $html = '<table border="0" cellpadding="1"><tr color="red" style="font-weight: bold;font-size: 8px;">
                         <td width="200" align="right" colspan="2">Grand Total : </td>
                         <td width="40" align="right">'. number_format($level_zero_qty_sum, 2, '.', ',') .'</td>
                         <td width="40"></td>
                         <td width="50" align="right">'. number_format($level_zero_total_sum, 2, '.', ',') .'</td>
                         <td width="40" align="right">'. number_format($level_zero_vat_sum, 2, '.', ',') .'</td>
                         <td width="55" align="right">'. number_format($level_zero_sub_total_sum, 2, '.', ',') .'</td>
                         <td width="55" align="right">'. number_format($level_zero_paid_sum, 2, '.', ',') .'</td>
                         <td width="55" align="right">'. number_format($level_zero_balance_sum, 2, '.', ',') .'</td>
                     </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, true, false, false, false, '');

                    //advance section
                    $advance_amount_sum=0;
                    $advance_disbursed_sum=0;
                    $advance_remaining_sum=0;
                    $advances=CustomerAdvance::where('customer_id',$request->customer_id)->where('isPushed',1)->where('remainingBalance','>',0)->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->get();
                    if($advances->first())
                    {
                        $html='Advances';
                        $pdf::SetFont('helvetica', 'B', 11);
                        $pdf::Cell(95,5,$html,'',0,'L');
                        $pdf::Ln(6);


                        $html = '<table border="0.5" cellpadding="1" style="font-weight: bold;">
                            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);">
                                <th align="center" width="95">Date</th>
                                <th align="center" width="100">REF#</th>
                                <th align="center" width="100">Payment Mode</th>
                                <th align="center" width="80">Amount</th>
                                <th align="center" width="80">Disbursed</th>
                                <th align="center" width="80">Remaining</th>
                            </tr>';
                        $row=json_decode(json_encode($advances), true);

                        for ($j=0;$j<count($row);$j++)
                        {
                            $advance_amount_sum+=$row[$j]['Amount'];
                            $advance_disbursed_sum+=$row[$j]['spentBalance'];
                            $advance_remaining_sum+=$row[$j]['remainingBalance'];
                            $html .= '<tr>
                                <td align="center" width="95">'.(date('d-M-Y', strtotime($row[$j]['TransferDate']))).'</td>
                                <td align="center" width="100">'.($row[$j]['receiptNumber']).'</td>
                                <td align="center" width="100">'.($row[$j]['paymentType']).'</td>
                                <td align="right" width="80">'.(number_format($row[$j]['Amount'], 2, '.', ',')).'</td>
                                <td align="right" width="80">'.(number_format($row[$j]['spentBalance'], 2, '.', ',')).'</td>
                                <td align="right" width="80">'.(number_format($row[$j]['remainingBalance'], 2, '.', ',')).'</td>
                                </tr>';
                        }
                        $html .= '<tr color="red" style="font-weight: bold">
                                 <td width="295" align="right" colspan="2">Total : </td>
                                 <td width="80" align="right">'. number_format($advance_amount_sum, 2, '.', ',') .'</td>
                                 <td width="80" align="right">'. number_format($advance_disbursed_sum, 2, '.', ',') .'</td>
                                 <td width="80" align="right">'. number_format($advance_remaining_sum, 2, '.', ',') .'</td>
                             </tr>';
                        $html .= '</table>';
                        $pdf::SetFont('helvetica', '', 8);
                        $pdf::writeHTML($html, true, false, false, false, '');
                    }

                    //loan section
                    $loan_amount_sum=0;
                    $loan_recovered_sum=0;
                    $loan_remaining_sum=0;
                    //$loans=LoanMaster::where('customer_id',$request->customer_id)->where('isPushed',1)->where('outward_isPaid',0)->where('loanType',0)->whereBetween('loanDate',[$request->fromDate,$request->toDate])->get();
                    $loans=LoanMaster::where('customer_id',$request->customer_id)->where('isPushed',1)->where('outward_isPaid',0)->where('loanType',0)->get();
                    if($loans->first())
                    {
                        $html='Loans';
                        $pdf::Cell(95,5,$html,'',0,'L');
                        $pdf::Ln(6);

                        $html = '<table border="0.5" cellpadding="1">
                            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                                <th align="center" width="50">Date</th>
                                <th align="center" width="50">REF#</th>
                                <th align="center" width="60">PaymentMode</th>
                                <th align="center" width="50">Amount</th>
                                <th align="center" width="50">Recovered</th>
                                <th align="center" width="50">Remaining</th>
                                <th align="center" width="225">Description</th>
                            </tr>';
                        $row=json_decode(json_encode($loans), true);

                        for ($j=0;$j<count($row);$j++)
                        {
                            $loan_amount_sum+=$row[$j]['totalAmount'];
                            $loan_recovered_sum+=$row[$j]['outward_PaidBalance'];
                            $loan_remaining_sum+=$row[$j]['outward_RemainingBalance'];
                            $html .= '<tr>
                                <td align="center" width="50">'.(date('d-M-Y', strtotime($row[$j]['loanDate']))).'</td>
                                <td align="center" width="50">'.($row[$j]['referenceNumber']).'</td>
                                <td align="center" width="60">'.($row[$j]['payment_type']).'</td>
                                <td align="right" width="50">'.(number_format($row[$j]['totalAmount'], 2, '.', ',')).'</td>
                                <td align="right" width="50">'.(number_format($row[$j]['outward_PaidBalance'], 2, '.', ',')).'</td>
                                <td align="right" width="50">'.(number_format($row[$j]['outward_RemainingBalance'], 2, '.', ',')).'</td>
                                <td align="center" width="225">'.($row[$j]['Description']).'</td>
                                </tr>';
                        }
                        $html .= '<tr color="red" style="font-weight: bold">
                                 <td width="160" align="right" colspan="2">Total : </td>
                                 <td width="50" align="right">'. number_format($loan_amount_sum, 2, '.', ',') .'</td>
                                 <td width="50" align="right">'. number_format($loan_recovered_sum, 2, '.', ',') .'</td>
                                 <td width="50" align="right">'. number_format($loan_remaining_sum, 2, '.', ',') .'</td>
                                 <td width="225" align="right"></td>
                             </tr>';
                        $html .= '</table>';
                        $pdf::writeHTML($html, true, false, false, false, '');
                    }

                    //summary section
                    $sum_of_debit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
                    $sum_of_credit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
                    $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;

                    //find sum of payments and advances between to and from date
                    $total_paid_amount_prev=PaymentReceive::where('customer_id',$request->customer_id)->where('isPushed',1)->whereNull('deleted_at')->whereBetween('transferDate',[$request->fromDate,$request->toDate])->sum('paidAmount');
                    $total_advance_amount_prev=CustomerAdvance::where('customer_id',$request->customer_id)->where('isPushed',1)->whereNull('deleted_at')->whereBetween('transferDate',[$request->fromDate,$request->toDate])->sum('Amount');
                    $sum_of_receive=$total_paid_amount_prev+$total_advance_amount_prev;

                    //echo "<pre>";print_r($sum_of_receive);die;
                    //echo $sum_of_receive;die;
                    if($sum_of_receive>$closing_amount)
                    {
                        $prev=0;
                    }
                    else
                    {
                        $prev=$closing_amount-$sum_of_receive;
                    }
                    //$prev=$closing_amount;

                    //$total_advance_amount=CustomerAdvance::where('customer_id',$request->customer_id)->where('isPushed',1)->whereNull('deleted_at')->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->sum('Amount');
                    if($request->customer_id==157)
                    {
                        // spark
                        $total_advance_amount=CustomerAdvance::where('customer_id',$request->customer_id)->where('isPushed',1)->whereNull('deleted_at')->sum('remainingBalance');
                    }
                    else
                    {
                        $total_advance_amount=CustomerAdvance::where('customer_id',$request->customer_id)->where('isPushed',1)->whereNull('deleted_at')->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->sum('remainingBalance');
                    }


                    $credit_sum=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->sum('Credit');
                    $debit_sum=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->sum('Debit');
                    $diff=$debit_sum-$credit_sum;

                    $prev_from_sale=Sale::where('customer_id',$request->customer_id)->whereNull('deleted_at')->where('SaleDate','<',$request->fromDate)->sum('remainingBalance');
                    //$prev_from_sale=Sale::where('customer_id',$request->customer_id)->whereNull('deleted_at')->where('SaleDate','<',$request->fromDate)->where('isActive',1)->sum('remainingBalance');

                    /*$html = '<table border="0" cellpadding="0">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>PREVIOUS</u></td>
                         <td width="80" align="right"><u>'. number_format($closing_amount-$total_paid_amount, 2, '.', ',') .'</u></td>
                    </tr>';*/

                    $prev=$prev_from_sale;
                    $html = '<table border="0" cellpadding="0">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="240" align="right"><u><b>'.$booking_status_string.'</b></u></td>
                         <td width="215" align="right"><u>PREVIOUS</u></td>
                         <td width="80" align="right"><u>'. number_format($prev, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>TOTAL</u></td>
                         <td width="80" align="right"><u>'. number_format(($prev+$level_zero_total_sum), 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>VAT</u></td>
                         <td width="80" align="right"><u>'. number_format($level_zero_vat_sum, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>SUB TOTAL</u></td>
                         <td width="80" align="right"><u>'. number_format(($prev+$level_zero_total_sum+$level_zero_vat_sum), 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>LOAN</u></td>
                         <td width="80" align="right"><u>'. number_format($loan_remaining_sum, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>NET TOTAL</u></td>
                         <td width="80" align="right"><u>'. number_format(($prev+$level_zero_total_sum+$level_zero_vat_sum+$loan_remaining_sum), 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr color="blue" style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>PAID</u></td>
                         <td width="80" align="right"><u>'. number_format($level_zero_paid_sum, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr color="green" style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>ADVANCE</u></td>
                         <td width="80" align="right"><u>'. number_format($total_advance_amount, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    /*$html = '<table border="0" cellpadding="1">
                    <tr color="red" style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>BALANCE</u></td>
                         <td width="80" align="right"><u>'. number_format($prev+$level_zero_total_sum+$level_zero_vat_sum+$loan_remaining_sum-$level_zero_paid_sum, 2, '.', ',') .'</u></td>
                    </tr>';
                    $pdf::writeHTML($html, false, false, false, false, '');*/

                    $html = '<table border="0" cellpadding="1">
                    <tr color="#eb7328" style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>OUTSTANDING AMOUNT</u></td>
                         <td width="80" align="right"><u>'. number_format(($prev+$level_zero_total_sum+$level_zero_vat_sum+$loan_remaining_sum-$level_zero_paid_sum)-$total_advance_amount, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');


                    /*$pdf::Ln(8);
                    $pdf::SetFont('helvetica', '', 10);
                    $credit_sum=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Credit');
                    $debit_sum=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Debit');
                    $diff=$debit_sum-$credit_sum;
                    $html='<table border="0.5" cellpadding="1">';
                    $html.= '
                 <tr>
                     <td width="310" align="right" colspan="3">Total Outstanding Balance (Credit-Debit) : </td>
                     <td width="80" align="right">'.number_format($credit_sum,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($debit_sum,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($diff,2,'.',',').'</td>
                 </tr>';
                    $pdf::SetFillColor(255, 0, 0);
                    $html.='</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');*/
                }
                else
                {
                    $customer_all_vehicle=Vehicle::where('customer_id',$request->customer_id)->whereIn('id',$request->vehicle_id)->get();
                    $customer_all_vehicle=$customer_all_vehicle->sortBy('registrationNumber');
                    foreach($customer_all_vehicle as $single)
                    {
                        $sales_ids=SaleDetail::select('sale_id')->where('vehicle_id',$single->id)->whereBetween('createdDate',[$request->fromDate,$request->toDate])->get();
                        $sales_ids_array[$single->registrationNumber]=json_decode(json_encode($sales_ids), true);
                    }
                    //echo "<pre>";print_r($sales_ids_array);die;
                    $level_zero_qty_sum=0;
                    $level_zero_total_sum=0;
                    $level_zero_sub_total_sum=0;
                    $level_zero_vat_sum=0;
                    $level_zero_paid_sum=0;
                    $level_zero_balance_sum=0;
                    foreach($sales_ids_array as $key=>$value)
                    {
                        //echo $key;print_r($value);die;
                        //<tr style="background-color: rgb(255, 255, 255); color: rgb(242, 10, 10);">
                        if(!empty($value))
                        {
                            $html = '<table border="0.5" cellpadding="1">
                            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); font-weight: bold;font-size: 10px;">
                                <th align="center" width="60">Vehicle</th>
                                <th align="center" width="70">Inv#</th>
                                <th align="center" width="70">Date</th>
                                <th align="center" width="40">Qty</th>
                                <th align="center" width="40">Rate</th>
                                <th align="center" width="50">Total</th>
                                <th align="center" width="40">VAT</th>
                                <th align="center" width="55">SubTotal</th>
                                <th align="center" width="55">Paid</th>
                                <th align="center" width="55">Balance</th>
                            </tr>';

                            $needed_sales=array_column($value,'sale_id');
                            //echo "<pre>";print_r($needed_sales);die;
                            $data=Sale::with('sale_details','sale_details.vehicle')->whereIn('id', $needed_sales)->where('isActive',1)->get();
                            $row=json_decode(json_encode($data), true);
                            $level_one_qty_sum=0;
                            $level_one_total_sum=0;
                            $level_one_sub_total_sum=0;
                            $level_one_vat_sum=0;
                            $level_one_paid_sum=0;
                            $level_one_balance_sum=0;
                            for ($j=0;$j<count($row);$j++)
                            {
                                $current_vat_amount = $row[$j]['sale_details'][0]['rowTotal'] * $row[$j]['sale_details'][0]['VAT'] / 100;
                                $level_one_qty_sum+=$row[$j]['sale_details'][0]['Quantity'];
                                $level_one_total_sum+=$row[$j]['sale_details'][0]['rowTotal'];
                                $level_one_sub_total_sum+=$row[$j]['sale_details'][0]['rowSubTotal'];
                                $level_one_vat_sum+=$current_vat_amount;
                                $level_one_paid_sum+=$row[$j]['paidBalance'];
                                $level_one_balance_sum+=$row[$j]['remainingBalance'];
                                $html .= '<tr>
                                <td align="center" width="60">'.($key).'</td>
                                <td align="center" width="70">'.($row[$j]['sale_details'][0]['PadNumber']).'</td>
                                <td align="center" width="70">'.(date('d-M-Y', strtotime($row[$j]['SaleDate']))).'</td>
                                <td align="right" width="40">'.($row[$j]['sale_details'][0]['Quantity']).'</td>
                                <td align="center" width="40">'.($row[$j]['sale_details'][0]['Price']).'</td>
                                <td align="right" width="50">'.($row[$j]['sale_details'][0]['rowTotal']).'</td>
                                <td align="right" width="40">'.(number_format($current_vat_amount, 2, '.', ',')).'</td>
                                <td align="right" width="55">'.($row[$j]['sale_details'][0]['rowSubTotal']).'</td>
                                <td align="right" width="55">'.($row[$j]['paidBalance']).'</td>
                                <td align="right" width="55">'.($row[$j]['remainingBalance']).'</td>
                                </tr>';

                                $level_zero_qty_sum+=$row[$j]['sale_details'][0]['Quantity'];
                                $level_zero_total_sum+=$row[$j]['sale_details'][0]['rowTotal'];
                                $level_zero_sub_total_sum+=$row[$j]['sale_details'][0]['rowSubTotal'];
                                //$level_zero_sub_total_sum+=$row[$j]['sale_details'][0]['rowSubTotal'];
                                $level_zero_vat_sum+=$current_vat_amount;
                                $level_zero_paid_sum+=$row[$j]['paidBalance'];
                                $level_zero_balance_sum+=$row[$j]['remainingBalance'];
                            }
                            $html .= '<tr color="red" style="font-weight: bold;font-size: 9px;">
                                 <td width="200" align="right" colspan="2">Total : </td>
                                 <td width="40" align="right">'. number_format($level_one_qty_sum, 2, '.', ',') .'</td>
                                 <td width="40"></td>
                                 <td width="50" align="right">'. number_format($level_one_total_sum, 2, '.', ',') .'</td>
                                 <td width="40" align="right">'. number_format($level_one_vat_sum, 2, '.', ',') .'</td>
                                 <td width="55" align="right">'. number_format($level_one_sub_total_sum, 2, '.', ',') .'</td>
                                 <td width="55" align="right">'. number_format($level_one_paid_sum, 2, '.', ',') .'</td>
                                 <td width="55" align="right">'. number_format($level_one_balance_sum, 2, '.', ',') .'</td>
                             </tr>';

                            $html .= '</table>';
                            $pdf::writeHTML($html, true, false, false, false, '');
                        }
                    }
                    $html = '<table border="0" cellpadding="1"><tr color="red" style="font-weight: bold;font-size: 8px;">
                         <td width="200" align="right" colspan="2">Grand Total : </td>
                         <td width="40" align="right">'. number_format($level_zero_qty_sum, 2, '.', ',') .'</td>
                         <td width="40"></td>
                         <td width="50" align="right">'. number_format($level_zero_total_sum, 2, '.', ',') .'</td>
                         <td width="40" align="right">'. number_format($level_zero_vat_sum, 2, '.', ',') .'</td>
                         <td width="55" align="right">'. number_format($level_zero_sub_total_sum, 2, '.', ',') .'</td>
                         <td width="55" align="right">'. number_format($level_zero_paid_sum, 2, '.', ',') .'</td>
                         <td width="55" align="right">'. number_format($level_zero_balance_sum, 2, '.', ',') .'</td>
                     </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, true, false, false, false, '');

                    //advance section
                    $advance_amount_sum=0;
                    $advance_disbursed_sum=0;
                    $advance_remaining_sum=0;
                    $advances=CustomerAdvance::where('customer_id',$request->customer_id)->where('isPushed',1)->where('remainingBalance','>',0)->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->get();
                    if($advances->first())
                    {
                        $html='Advances';
                        $pdf::Cell(95,5,$html,'',0,'L');
                        $pdf::Ln(6);

                        $html = '<table border="0.5" cellpadding="1" style="font-weight: bold;">
                            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);">
                                <th align="center" width="95">Date</th>
                                <th align="center" width="100">REF#</th>
                                <th align="center" width="100">Payment Mode</th>
                                <th align="center" width="80">Amount</th>
                                <th align="center" width="80">Disbursed</th>
                                <th align="center" width="80">Remaining</th>
                            </tr>';
                        $row=json_decode(json_encode($advances), true);

                        for ($j=0;$j<count($row);$j++)
                        {
                            $advance_amount_sum+=$row[$j]['Amount'];
                            $advance_disbursed_sum+=$row[$j]['spentBalance'];
                            $advance_remaining_sum+=$row[$j]['remainingBalance'];
                            $html .= '<tr>
                                <td align="center" width="95">'.(date('d-M-Y', strtotime($row[$j]['TransferDate']))).'</td>
                                <td align="center" width="100">'.($row[$j]['receiptNumber']).'</td>
                                <td align="center" width="100">'.($row[$j]['paymentType']).'</td>
                                <td align="right" width="80">'.(number_format($row[$j]['Amount'], 2, '.', ',')).'</td>
                                <td align="right" width="80">'.(number_format($row[$j]['spentBalance'], 2, '.', ',')).'</td>
                                <td align="right" width="80">'.(number_format($row[$j]['remainingBalance'], 2, '.', ',')).'</td>
                                </tr>';
                        }
                        $html .= '<tr color="red" style="font-weight: bold">
                                 <td width="295" align="right" colspan="2">Total : </td>
                                 <td width="80" align="right">'. number_format($advance_amount_sum, 2, '.', ',') .'</td>
                                 <td width="80" align="right">'. number_format($advance_disbursed_sum, 2, '.', ',') .'</td>
                                 <td width="80" align="right">'. number_format($advance_remaining_sum, 2, '.', ',') .'</td>
                             </tr>';
                        $html .= '</table>';
                        $pdf::writeHTML($html, true, false, false, false, '');
                    }

                    //loan section
                    $loan_amount_sum=0;
                    $loan_recovered_sum=0;
                    $loan_remaining_sum=0;
                    //$loans=LoanMaster::where('customer_id',$request->customer_id)->where('isPushed',1)->where('outward_isPaid',0)->where('loanType',0)->whereBetween('loanDate',[$request->fromDate,$request->toDate])->get();
                    $loans=LoanMaster::where('customer_id',$request->customer_id)->where('isPushed',1)->where('outward_isPaid',0)->where('loanType',0)->get();
                    if($loans->first())
                    {
                        $html='Loans';
                        $pdf::Cell(95,5,$html,'',0,'L');
                        $pdf::Ln(6);

                        $html = '<table border="0.5" cellpadding="1">
                            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                                <th align="center" width="50">Date</th>
                                <th align="center" width="50">REF#</th>
                                <th align="center" width="60">PaymentMode</th>
                                <th align="center" width="50">Amount</th>
                                <th align="center" width="50">Recovered</th>
                                <th align="center" width="50">Remaining</th>
                                <th align="center" width="225">Description</th>
                            </tr>';
                        $row=json_decode(json_encode($loans), true);

                        for ($j=0;$j<count($row);$j++)
                        {
                            $loan_amount_sum+=$row[$j]['totalAmount'];
                            $loan_recovered_sum+=$row[$j]['outward_PaidBalance'];
                            $loan_remaining_sum+=$row[$j]['outward_RemainingBalance'];
                            $html .= '<tr>
                                <td align="center" width="50">'.(date('d-M-Y', strtotime($row[$j]['loanDate']))).'</td>
                                <td align="center" width="50">'.($row[$j]['referenceNumber']).'</td>
                                <td align="center" width="60">'.($row[$j]['payment_type']).'</td>
                                <td align="right" width="50">'.(number_format($row[$j]['totalAmount'], 2, '.', ',')).'</td>
                                <td align="right" width="50">'.(number_format($row[$j]['outward_PaidBalance'], 2, '.', ',')).'</td>
                                <td align="right" width="50">'.(number_format($row[$j]['outward_RemainingBalance'], 2, '.', ',')).'</td>
                                <td align="center" width="225">'.($row[$j]['Description']).'</td>
                                </tr>';
                        }
                        $html .= '<tr color="red" style="font-weight: bold">
                                 <td width="160" align="right" colspan="2">Total : </td>
                                 <td width="50" align="right">'. number_format($loan_amount_sum, 2, '.', ',') .'</td>
                                 <td width="50" align="right">'. number_format($loan_recovered_sum, 2, '.', ',') .'</td>
                                 <td width="50" align="right">'. number_format($loan_remaining_sum, 2, '.', ',') .'</td>
                                 <td width="225" align="right"></td>
                             </tr>';
                        $html .= '</table>';
                        $pdf::writeHTML($html, true, false, false, false, '');
                    }

                    //summary section
                    $sum_of_debit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
                    $sum_of_credit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
                    $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;

                    //find sum of payments and advances between to and from date
                    $total_paid_amount_prev=PaymentReceive::where('customer_id',$request->customer_id)->where('isPushed',1)->whereNull('deleted_at')->whereBetween('transferDate',[$request->fromDate,$request->toDate])->sum('paidAmount');
                    $total_advance_amount_prev=CustomerAdvance::where('customer_id',$request->customer_id)->where('isPushed',1)->whereNull('deleted_at')->whereBetween('transferDate',[$request->fromDate,$request->toDate])->sum('Amount');
                    $sum_of_receive=$total_paid_amount_prev+$total_advance_amount_prev;

                    //echo "<pre>";print_r($sum_of_receive);die;
                    //echo $sum_of_receive;die;
                    if($sum_of_receive>$closing_amount)
                    {
                        $prev=0;
                    }
                    else
                    {
                        $prev=$closing_amount-$sum_of_receive;
                    }
                    //$prev=$closing_amount;

                    //$total_advance_amount=CustomerAdvance::where('customer_id',$request->customer_id)->where('isPushed',1)->whereNull('deleted_at')->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->sum('Amount');
                    $total_advance_amount=CustomerAdvance::where('customer_id',$request->customer_id)->where('isPushed',1)->whereNull('deleted_at')->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->sum('remainingBalance');

                    $credit_sum=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->sum('Credit');
                    $debit_sum=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->sum('Debit');
                    $diff=$debit_sum-$credit_sum;

                    $prev_from_sale=Sale::where('customer_id',$request->customer_id)->whereNull('deleted_at')->where('SaleDate','<',$request->fromDate)->sum('remainingBalance');
                    //$prev_from_sale=Sale::where('customer_id',$request->customer_id)->whereNull('deleted_at')->where('SaleDate','<',$request->fromDate)->where('isActive',1)->sum('remainingBalance');

                    /*$html = '<table border="0" cellpadding="0">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>PREVIOUS</u></td>
                         <td width="80" align="right"><u>'. number_format($closing_amount-$total_paid_amount, 2, '.', ',') .'</u></td>
                    </tr>';*/
                    $prev=$prev_from_sale;
                    $html = '<table border="0" cellpadding="0">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>PREVIOUS</u></td>
                         <td width="80" align="right"><u>'. number_format($prev, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>TOTAL</u></td>
                         <td width="80" align="right"><u>'. number_format(($prev+$level_zero_total_sum), 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>VAT</u></td>
                         <td width="80" align="right"><u>'. number_format($level_zero_vat_sum, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>SUB TOTAL</u></td>
                         <td width="80" align="right"><u>'. number_format(($prev+$level_zero_total_sum+$level_zero_vat_sum), 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>LOAN</u></td>
                         <td width="80" align="right"><u>'. number_format($loan_remaining_sum, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>NET TOTAL</u></td>
                         <td width="80" align="right"><u>'. number_format(($prev+$level_zero_total_sum+$level_zero_vat_sum+$loan_remaining_sum), 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr color="blue" style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>PAID</u></td>
                         <td width="80" align="right"><u>'. number_format($level_zero_paid_sum, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr color="green" style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>ADVANCE</u></td>
                         <td width="80" align="right"><u>'. number_format($total_advance_amount, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');

                    $html = '<table border="0" cellpadding="1">
                    <tr color="red" style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>BALANCE</u></td>
                         <td width="80" align="right"><u>'. number_format($prev+$level_zero_total_sum+$level_zero_vat_sum+$loan_remaining_sum-$level_zero_paid_sum, 2, '.', ',') .'</u></td>
                    </tr>';
                    $html .= '</table>';
                    $pdf::writeHTML($html, false, false, false, false, '');
                }
                /*else
                {
                    //for vehicle selection
                    $veh_ids=array();
                    $veh_name=array();
                    foreach ($row as $item)
                    {
                        if($item['sale_details'][0]['api_vehicle']['id']==$request->vehicle_id)
                        {
                            $veh_ids[]=$item['sale_details'][0]['api_vehicle']['id'];
                            $veh_name[]=$item['sale_details'][0]['api_vehicle']['registrationNumber'];
                        }
                    }
                    $veh_ids=array_unique($veh_ids);
                    $veh_name=array_unique($veh_name);

                    for($i=0;$i<count($veh_ids);$i++)
                    {
                        $sub_total_sum=0.0;
                        $paid_total_sum=0.0;
                        $balance_total_sum=0.0;
                        $vat_sum=0.0;
                        $qty_sum=0.0;

                        $vehicle_name=$veh_name[$i];
                        $veh_title='<u><b>'.'Vehicle :- '.$vehicle_name.'</b></u>';
                        $pdf::SetFont('helvetica', 'B', 10);
                        $pdf::writeHTMLCell(0, 0, '', '', $veh_title,0, 1, 0, true, 'L', true);
                        $pdf::SetFont('helvetica', '', 8);

                        $html = '<table border="0.5" cellpadding="3">
                        <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                            <th align="center" width="50">Date</th>
                            <th align="center" width="50">S.No.</th>
                            <th align="center" width="140">Customer</th>
                            <th align="center" width="40">Qty</th>
                            <th align="center" width="40">Rate</th>
                            <th align="center" width="45">Total</th>
                            <th align="center" width="40">VAT</th>
                            <th align="center" width="50">SubTotal</th>
                            <th align="center" width="50">Paid</th>
                            <th align="center" width="50">Balance</th>
                        </tr>';

                        for($j=0;$j<count($row);$j++)
                        {
                            if($veh_ids[$i]==$row[$j]['sale_details'][0]['api_vehicle']['id'])
                            {
                                $sub_total_sum += $row[$j]['sale_details'][0]['rowSubTotal'];
                                $paid_total_sum += $row[$j]['paidBalance'];
                                $balance_total_sum += $row[$j]['remainingBalance'];
                                $current_vat_amount=$row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100;
                                $vat_sum+=$current_vat_amount;
                                $qty_sum+=$row[$j]['sale_details'][0]['Quantity'];
                                $html .= '<tr>
                                <td align="center" width="50">' . (date('d-M-Y', strtotime($row[$j]['SaleDate']))) . '</td>
                                <td align="center" width="50">' . ($row[$j]['sale_details'][0]['PadNumber']) . '</td>
                                <td align="left" width="140">' . ($row[$j]['api_customer']['Name']) . '</td>
                                <td align="right" width="40">' . ($row[$j]['sale_details'][0]['Quantity']) . '</td>
                                <td align="center" width="40">' . ($row[$j]['sale_details'][0]['Price']) . '</td>
                                <td align="center" width="45">' . ($row[$j]['sale_details'][0]['rowTotal']) . '</td>
                                <td align="center" width="40">' . ($current_vat_amount) . '</td>
                                <td align="center" width="50">' . ($row[$j]['sale_details'][0]['rowSubTotal']) . '</td>
                                <td align="center" width="50">' . ($row[$j]['paidBalance']) . '</td>
                                <td align="center" width="50">' . ($row[$j]['remainingBalance']) . '</td>
                                </tr>';
                            }
                        }
                        $html .= '<tr color="red">
                             <td width="190" colspan="2">Total : </td>
                             <td width="40" align="right">'.number_format($qty_sum, 2, '.', ',').'</td>
                             <td width="40"></td>
                             <td width="45"></td>
                             <td width="40" align="left">' . number_format($vat_sum, 2, '.', ',') . '</td>
                             <td width="50" align="right">' . number_format($sub_total_sum, 2, '.', ',') . '</td>
                             <td width="50" align="right">' . number_format($paid_total_sum, 2, '.', ',') . '</td>
                             <td width="50" align="right">' . number_format($balance_total_sum, 2, '.', ',') . '</td>
                             <td width="50" align="right"></td>
                        </tr>';
                        $pdf::SetFillColor(255, 0, 0);
                        $html .= '</table>';
                        $pdf::writeHTML($html, true, false, false, false, '');
                    }
                    //for vehicle selection
                }*/
            }

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function SalesReportByShift()
    {
        return view('admin.report.sales_report_by_shift');
    }

    public function PrintSalesReportByShift(Request $request)
    {
        if($request->start_pad!='' && $request->middle_pad!='' && $request->end_pad!='')
        {
            $start_pad_date = SaleDetail::select('createdDate')->where('company_id',session('company_id'))->where('PadNumber','like',$request->start_pad)->first();
            $middle_pad_date = SaleDetail::select('createdDate')->where('company_id',session('company_id'))->where('PadNumber','like',$request->middle_pad)->first();
            $end_pad_date = SaleDetail::select('createdDate')->where('company_id',session('company_id'))->where('PadNumber','like',$request->end_pad)->first();

            if(($start_pad_date->createdDate == $middle_pad_date->createdDate) && ($middle_pad_date->createdDate == $end_pad_date->createdDate))
            {
                $day_shift=SaleDetail::select('sale_id','PadNumber')->where('company_id',session('company_id'))->whereBetween('PadNumber',[$request->start_pad,$request->middle_pad])->where('isActive','=','1')->where('deleted_at','=',NULL)->get();
                $day_row=json_decode(json_encode($day_shift), true);
                $day_row=$this->array_sort($day_row, 'PadNumber', SORT_ASC);
                $day_ids=array_column($day_row,'sale_id');

                $night_shift=SaleDetail::select('sale_id','PadNumber')->where('company_id',session('company_id'))->whereBetween('PadNumber',[$request->middle_pad,$request->end_pad])->where('isActive','=','1')->where('deleted_at','=',NULL)->get();
                $night_row=json_decode(json_encode($night_shift), true);
                $night_row=$this->array_sort($night_row, 'PadNumber', SORT_ASC);
                $first=array_shift($night_row);
                $night_ids=array_column($night_row,'sale_id');

                $day_sales=SalesResource::collection(Sale::with('sale_details')->whereIn('id',$day_ids)->get());
                $night_sales=SalesResource::collection(Sale::with('sale_details')->whereIn('id',$night_ids)->get());

                $footer=new CustomeFooter;
                $footer->footer();
                $pdf = new PDF();
                $pdf::setPrintHeader(false);

                $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf::SetAutoPageBreak(TRUE, 14);

                $pdf::AddPage('', 'A4');
                $pdf::SetFont('helvetica', '', 6);
                $pdf::SetFillColor(255,255,0);

                $grand_qty=0;
                $grand_vat=0;
                $grand_subtotal=0;
                $grand_credit=0;
                $grand_cash=0;
                $grand_credit_per=0;
                $grand_cash_per=0;


                $day_row=json_decode(json_encode($day_sales), true);
                $row=array_values($day_row);
                // copy all data to new array and sort it according to pad number and then print
                $new_master_array=array();
                for($i=0;$i<count($row);$i++)
                {
                    if($row[$i]['sale_details'][0]['PadNumber']!='0')
                    {
                        $master_row=array();
                        $master_row['PadNumber']=$row[$i]['sale_details'][0]['PadNumber'] ?? 'NA';
                        $master_row['Name']=$row[$i]['api_customer']['Name'];
                        $master_row['registrationNumber']=$row[$i]['sale_details'][0]['api_vehicle']['registrationNumber'] ?? '';
                        $master_row['Quantity']=$row[$i]['sale_details'][0]['Quantity'] ?? 0;
                        $master_row['Price']=$row[$i]['sale_details'][0]['Price'] ?? 0;
                        $master_row['rowTotal']=$row[$i]['sale_details'][0]['rowTotal'] ?? 0;
                        $master_row['VAT']=($row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100);
                        $master_row['rowSubTotal']=$row[$i]['sale_details'][0]['rowSubTotal'];
                        $master_row['paidBalance']=$row[$i]['paidBalance'];
                        $master_row['remainingBalance']=$row[$i]['remainingBalance'];
                        $master_row['SaleDate']=$row[$i]['SaleDate'];
                        $master_row['IsPaid']=$row[$i]['IsPaid'];
                        $new_master_array[]=$master_row;
                    }
                }
                $keys = array_column($new_master_array, 'PadNumber');
                array_multisort($keys, SORT_ASC, $new_master_array);
                $row=$new_master_array;

                $pdf::SetFont('helvetica', '', 12);
                $html='SALES REPORT';
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

                $pdf::SetFont('helvetica', '', 12);
                $day_pad_title=$request->start_pad.' To '.$request->middle_pad;
                $day_title='Day Shift';
                $pdf::Cell(95,5,$day_pad_title,'',0,'L');
                $pdf::Cell(95,5,$day_title,'',0,'R');
                $pdf::Ln(6);

                //<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                $pdf::SetFont('helvetica', 'B', 8);
                $html = '<table border="0.5" cellpadding="1">
                    <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFont('helvetica', '', 8);

                $VAT_sum=0.0;
                $grand_rowTotal_sum=0.0;
                $qty_sum=0.0;
                $sub_total_sum=0.0;
                $paid_total_sum=0.0;
                $balance_total_sum=0.0;
                $rowSubTotal=0.0;
                $rowTotal_sum=0.0;
                for($i=0;$i<count($row);$i++)
                {
                    $sub_total_sum+=$row[$i]['rowSubTotal'];
                    $paid_total_sum+=$row[$i]['paidBalance'];
                    $balance_total_sum+=$row[$i]['remainingBalance'];
                    $qty_sum+=$row[$i]['Quantity'];
                    $rowTotal_sum+=$row[$i]['rowTotal'];
                    $VAT_sum+=$row[$i]['VAT'];
                    $rowSubTotal+=$row[$i]['rowSubTotal'];
                    if($row[$i]['remainingBalance']!=0)
                    {
                        //$html .='<tr style="background-color: #aba9a9">
                        $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50" style="background-color: #aba9a9">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                }

                $html.= '
                 <tr color="red">
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="50"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="20"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
                $pdf::SetFont('helvetica', '', 12);

                $cash_percentage=$paid_total_sum/$rowSubTotal*100;
                $grand_credit_per+=$cash_percentage;
                $cash_percentage=number_format($cash_percentage,'2','.',',');
                $day_shift_cash='Cash Sales : '.number_format($paid_total_sum,2,'.',',').' | '.$cash_percentage.'%';
                $credit_percentage=$balance_total_sum/$rowSubTotal*100;

                $grand_cash_per+=$credit_percentage;
                $credit_percentage=number_format($credit_percentage,'2','.',',');
                $day_shift_credit='Credit Sales : '.number_format($balance_total_sum,2,'.',',').' | '.$credit_percentage.'%';
                $pdf::Cell(95,5,$day_shift_cash,'',0,'L');
                $pdf::Cell(95,5,$day_shift_credit,'',0,'R');
                $pdf::Ln(6);

                $grand_qty+=$qty_sum;
                $grand_vat+=$VAT_sum;
                $grand_subtotal+=$rowSubTotal;
                $grand_credit+=$balance_total_sum;
                $grand_cash+=$paid_total_sum;
                $grand_rowTotal_sum+=$rowTotal_sum;



                ////////////////////////////shift change//////////////////////////////////////////

                $pdf::writeHTML("<hr>", true, false, false, false, '');
                $night_row=json_decode(json_encode($night_sales), true);
                $row=array_values($night_row);
                // copy all data to new array and sort it according to pad number and then print
                $new_master_array=array();
                for($i=0;$i<count($row);$i++)
                {
                    if($row[$i]['sale_details'][0]['PadNumber']!='0')
                    {
                        $master_row=array();
                        $master_row['PadNumber']=$row[$i]['sale_details'][0]['PadNumber'] ?? 'NA';
                        $master_row['Name']=$row[$i]['api_customer']['Name'];
                        $master_row['registrationNumber']=$row[$i]['sale_details'][0]['api_vehicle']['registrationNumber'] ?? '';
                        $master_row['Quantity']=$row[$i]['sale_details'][0]['Quantity'] ?? 0;
                        $master_row['Price']=$row[$i]['sale_details'][0]['Price'] ?? 0;
                        $master_row['rowTotal']=$row[$i]['sale_details'][0]['rowTotal'] ?? 0;
                        $master_row['VAT']=($row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100);
                        $master_row['rowSubTotal']=$row[$i]['sale_details'][0]['rowSubTotal'];
                        $master_row['paidBalance']=$row[$i]['paidBalance'];
                        $master_row['remainingBalance']=$row[$i]['remainingBalance'];
                        $master_row['SaleDate']=$row[$i]['SaleDate'];
                        $master_row['IsPaid']=$row[$i]['IsPaid'];
                        $new_master_array[]=$master_row;
                    }
                }
                $keys = array_column($new_master_array, 'PadNumber');
                array_multisort($keys, SORT_ASC, $new_master_array);
                $row=$new_master_array;

                $pdf::SetFont('helvetica', '', 12);
                $day_pad_title=($request->middle_pad+1).' To '.$request->end_pad;
                $day_title='Night Shift';
                $pdf::Cell(95,5,$day_pad_title,'',0,'L');
                $pdf::Cell(95,5,$day_title,'',0,'R');
                $pdf::Ln(6);

                //<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                $pdf::SetFont('helvetica', 'B', 8);
                $html = '<table border="0.5" cellpadding="1">
                    <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFont('helvetica', '', 8);

                $VAT_sum=0.0;
                $rowTotal_sum=0.0;
                $qty_sum=0.0;
                $sub_total_sum=0.0;
                $paid_total_sum=0.0;
                $balance_total_sum=0.0;
                $rowSubTotal=0.0;
                for($i=0;$i<count($row);$i++)
                {
                    $sub_total_sum+=$row[$i]['rowSubTotal'];
                    $paid_total_sum+=$row[$i]['paidBalance'];
                    $balance_total_sum+=$row[$i]['remainingBalance'];
                    $qty_sum+=$row[$i]['Quantity'];
                    $rowTotal_sum+=$row[$i]['rowTotal'];
                    $VAT_sum+=$row[$i]['VAT'];
                    $rowSubTotal+=$row[$i]['rowSubTotal'];
                    if($row[$i]['remainingBalance']!=0)
                    {
                        //$html .='<tr style="background-color: #aba9a9">
                        $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50" style="background-color: #aba9a9">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                }

                $html.= '
                 <tr color="red">
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="50"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="20"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
                $pdf::SetFont('helvetica', '', 12);

                $cash_percentage=$paid_total_sum/$rowSubTotal*100;
                $grand_cash_per+=$cash_percentage;
                $cash_percentage=number_format($cash_percentage,'2','.',',');
                $day_shift_cash='Cash Sales : '.number_format($paid_total_sum,2,'.',',').' | '.$cash_percentage.'%';
                $credit_percentage=$balance_total_sum/$rowSubTotal*100;
                $grand_credit_per+=$credit_percentage;
                $credit_percentage=number_format($credit_percentage,'2','.',',');
                $day_shift_credit='Credit Sales : '.number_format($balance_total_sum,2,'.',',').' | '.$credit_percentage.'%';
                $pdf::Cell(95,5,$day_shift_cash,'',0,'L');
                $pdf::Cell(95,5,$day_shift_credit,'',0,'R');
                $pdf::Ln(6);

                $grand_qty+=$qty_sum;
                $grand_vat+=$VAT_sum;
                $grand_subtotal+=$rowSubTotal;
                $grand_credit+=$balance_total_sum;
                $grand_cash+=$paid_total_sum;
                $grand_rowTotal_sum+=$rowTotal_sum;

                /////////////////////summary section//////////////////////
                $pdf::writeHTML("<hr>", true, false, false, false, '');

                $html='<table border="0.5" style="font-weight: bold;color:red;">';
                $html .='<tr>
                    <td align="right" width="260" colspan="4"> Total </td>
                    <td align="left" width="60" colspan="2">'.(number_format($grand_qty,2,'.',',')).'</td>
                    <td align="right" width="45">'.(number_format($grand_rowTotal_sum,2,'.',',')).'</td>
                    <td align="right" width="40">'.(number_format($grand_vat,2,'.',',')).'</td>
                    <td align="right" width="50">'.(number_format($grand_subtotal,2,'.',',')).'</td>
                    <td align="right" width="50">'.(number_format($grand_cash,2,'.',',')).'</td>
                    <td align="right" width="50">'.(number_format($grand_credit,2,'.',',')).'</td>
                    </tr>';
                $html.='</table>';
                $pdf::SetFont('helvetica', '', 8);
                $pdf::writeHTML($html, true, false, false, false, '');

                $pdf::SetFont('helvetica', '', 14);
                $cash_percentage=$grand_cash/$grand_subtotal*100;
                if($cash_percentage>50)
                {
                    $cash_percentage='<span style="color: darkgreen">('.(number_format($cash_percentage,'2','.',',')).'%)</span>';
                }
                else
                {
                    $cash_percentage='<span style="color: red">('.(number_format($cash_percentage,2,'.',',')).'%)</span>';
                }
                $html='<b>Cash Sales : '.number_format($grand_cash,2,'.',',').' '.$cash_percentage.'</b>';
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
                $pdf::Ln();
                $credit_percentage=$grand_credit/$grand_subtotal*100;
                if($credit_percentage<50)
                {
                    $credit_percentage='<span style="color: darkgreen">('.(number_format($credit_percentage,'2','.',',')).'%)</span>';
                }
                else
                {
                    $credit_percentage='<span style="color: red">('.(number_format($credit_percentage,2,'.',',')).'%)</span>';
                }
                $html='<b>Credit Sales : '.number_format($grand_credit,2,'.',',').' '.$credit_percentage.'</b>';
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

                $pdf::lastPage();

                $time=time();
                $name='SHIFT_SALES_REPORT_'.$request->start_pad.'_To_'.$request->end_pad.'_'.$time;
                $fileLocation = storage_path().'/app/public/report_files/';
                $fileNL = $fileLocation.'//'.$name.'.pdf';
                $pdf::Output($fileNL, 'F');
                $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
                $url=array('url'=>$url);
                return $url;
            }
            else
            {
                $data=array('result'=>false,'message'=>'DATE IS NOT SAME FOR ALL THREE PADS');
                return Response()->json($data);
            }
        }
        else
        {
            $data=array('result'=>false,'message'=>'INVALID INPUT DATA');
            return Response()->json($data);
        }
    }

    public function PrintGeneralLedger(Request $request)
    {
//        if ($request->fromDate!='' && $request->toDate!='')
//        {
//            $all_account_transactions=AccountTransactionResource::collection(AccountTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('Credit','!=',0.00)->where('Debit','!=',0.00)->where('Differentiate','!=',0.00));
//        }
//        else
//        {
//            return FALSE;
//        }

        //this is suppose to be generated with account_transaction table but something is not right in there so
        // generating this report manually

        //1.bring all cash sales -> debit
        //2.bring all cash purchase -> credit
        //3.all expenses (by cash and bank) -> credit
        //4.all customer advance (cash and bank) -> debit
        //5.all supplier advance (cash and bank) -> credit
        //6.all customer receive (cash and bank) -> debit
        //7.all supplier payment (cash and bank) -> credit

        //1.bring all cash sales -> debit
//        $row = Sale::select('PurchaseDate as Date', DB::raw('SUM(grandTotal) as PurchaseAmount'))
//            ->where('supplier_id','=',$supplier_id)
//            ->whereBetween('PurchaseDate',[$fromDate,$toDate])
//            ->groupBy('PurchaseDate')
//            ->get();
//        $row=json_decode(json_encode($row), true);

        //supplier payment entries
//        $row1 = SupplierPayment::select('transferDate as Date','paidAmount','referenceNumber','Description')
//            ->where('supplier_id','=',$supplier_id)
//            //->where('isPushed','=',1)
//            ->whereBetween('transferDate',[$fromDate,$toDate])
//            ->get();
//        $row1=json_decode(json_encode($row1), true);
//        $combined=array_merge($row,$row1);
//
//        $ord = array();
//        foreach ($combined as $key => $value){
//            $ord[] = strtotime($value['Date']);
//        }
//        array_multisort($ord, SORT_ASC, $combined);
//        //echo "<pre>123";print_r($combined);die;
//        $row=$combined;

        $all_account_transactions='';
        $footer=new CustomeFooter;
        $footer->footer();
        $pdf = new PDF();
        $pdf::setPrintHeader(false);

        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, 14);

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);

        //$row=$sales->sale_details;
        $row=json_decode(json_encode($all_account_transactions), true);
        //echo "<pre>123";print_r($row);die;
        if(empty($row))
        {
            return FALSE;
        }

        $pdf::SetFont('helvetica', '', 15);
        $html='General Ledger';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

        $balance=0.0;
        $debit_total=0.0;
        $credit_total=0.0;

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="50">#</th>
                <th align="center" width="80">Date</th>
                <th align="center" width="100">Type</th>
                <th align="center" width="100">Details</th>
                <th align="center" width="60">Credit</th>
                <th align="center" width="60">Debit</th>
                <th align="center" width="60">Closing</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        for($i=0;$i<count($row);$i++)
        {
            if($row[$i]['Debit']!=0)
            {
                $debit_total += $row[$i]['Debit'];
                $balance = $balance + $row[$i]['Debit'];
            }
            else
            {
                $credit_total += $row[$i]['Credit'];
                $balance = $balance - $row[$i]['Credit'];
            }
            $html .='<tr>
                <td align="center" width="50">'.($row[$i]['referenceNumber']).'</td>
                <td align="center" width="80">'.($row[$i]['createdDate']).'</td>
                <td align="center" width="100">'.'Type'.'</td>
                <td align="center" width="100">N.A.</td>
                <td align="right" width="60">'.($row[$i]['Credit']).'</td>
                <td align="right" width="60">'.($row[$i]['Debit']).'</td>
                <td align="right" width="60">'.number_format($row[$i]['Differentiate'],2,'.',',').'</td>
                </tr>';
        }
        $html.= '
             <tr color="red">
                 <td width="50"></td>
                 <td width="80"></td>
                 <td width="100"></td>
                 <td width="100" align="right">Total : </td>
                 <td width="60" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="60" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="60" align="right">'.number_format($balance,2,'.',',').'</td>
             </tr>';
        $pdf::SetFillColor(255, 0, 0);
        $html.='</table>';

        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::lastPage();
        $time=time();
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$time.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }

    public function PrintBankReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_bank_transactions=BankTransaction::where('company_id',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('deleted_at','=',NULL)->where('bank_id','=',$request->bank_id)->orderBy('createdDate')->get();
            $prev_date = date('Y-m-d', strtotime($request->fromDate .' -1 day'));
            $get_max_id=BankTransaction::where('company_id',session('company_id'))->where('bank_id','=',$request->bank_id)->where('createdDate','=',$prev_date)->max('id');
            //echo "<pre>";print_r($get_max_id);die;
            $sum_of_debit_before_from_date=BankTransaction::where('company_id',session('company_id'))->where('bank_id','=',$request->bank_id)->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=BankTransaction::where('company_id',session('company_id'))->where('bank_id','=',$request->bank_id)->where('createdDate','<',$request->fromDate)->sum('Credit');
            //$closing_amount=BankTransaction::where('company_id',session('company_id'))->where('bank_id','=',$request->bank_id)->where('id',$get_max_id)->first();
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
//            if(!$closing_amount)
//            {
//                $closing_amount=0;
//            }
//            else{
//                $closing_amount=$closing_amount->Differentiate;
//            }
        }
        else
        {
            return FALSE;
        }

        $footer=new CustomeFooter;
        $footer->footer();
        $pdf = new PDF();
        $pdf::setPrintHeader(false);

        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, 14);

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);

        //$row=$sales->sale_details;
        $row=json_decode(json_encode($all_bank_transactions), true);
        $row=array_values($row);
        //echo "<pre>123";print_r($row);die;

        $pdf::SetFont('helvetica', '', 15);
        $html='Bank Name :-'.$request->bank_name;
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=' Opening Balance : '.round($closing_amount,2);
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

        $balance=$closing_amount;
        $debit_total=0.0;
        $credit_total=0.0;

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="80">Date</th>
                <th align="center" width="100">Type</th>
                <th align="center" width="100">Ref#</th>
                <th align="center" width="80">Debit</th>
                <th align="center" width="80">Credit</th>
                <th align="center" width="90">Closing</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        $last_closing=0.0;
        for($i=0;$i<count($row);$i++)
        {
            if($row[$i]['Debit']!=0)
            {
                $debit_total += $row[$i]['Debit'];
                $balance = $balance + $row[$i]['Debit'];
            }
            elseif($row[$i]['Credit']!=0)
            {
                $credit_total += $row[$i]['Credit'];
                $balance = $balance - $row[$i]['Credit'];
            }
            else
            {
                $balance += $row[$i]['Differentiate'];
            }

            //$balance = $balance + $row[$i]['Differentiate'];
            $html .='<tr>
                <td align="center" width="80">'.(date('d-M-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left" width="100">'.($row[$i]['Type']).'</td>
                <td align="left" width="100">'.$row[$i]['updateDescription'].'</td>
                <td align="right" width="80">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                <td align="right" width="80">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                <td align="right" width="90">'.number_format($balance,2,'.',',').'</td>
                </tr>';
            $last_closing=$row[$i]['Differentiate'];
        }
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::SetFont('helvetica', 'B', 13);
        if($last_closing<0)
        {
            $html='<table border="0.5" cellpadding="2">';
            $html.= '
                 <tr>
                 <td width="280" align="right" colspan="3">Total : </td>
                 <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="90" align="right">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
        }
        else
        {
            $html='<table border="0.5" cellpadding="0">';
            $html.= '
                 <tr>
                 <td width="280" align="right" colspan="3">Total : </td>
                 <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="90" align="right">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
        }

        $pdf::lastPage();
        $time=time();
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$time.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }

    public function ViewBankReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_bank_transactions=BankTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('bank_id','=',$request->bank_id);
        }
        else
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        $row=json_decode(json_encode($all_bank_transactions), true);
        $row=array_values($row);
        if(empty($row))
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        $title='Bank Name :-'.$request->bank_name.' | FROM '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));

        $balance=0.0;
        $debit_total=0.0;
        $credit_total=0.0;

        $html = '<table class="display" id="report_table"><thead>
            <tr>
                <th align="center">Date</th>
                <th align="center">Type</th>
                <th align="center">Ref#</th>
                <th align="center">Debit</th>
                <th align="center">Credit</th>
                <th align="center">Closing</th>
            </tr></thead><tbody>';
        $last_closing=0.0;
        for($i=0;$i<count($row);$i++)
        {
            $debit_total += $row[$i]['Debit'];
            $credit_total += $row[$i]['Credit'];
            $html .='<tr>
                <td align="center">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left">'.($row[$i]['Type']).'</td>
                <td align="center">'.$row[$i]['updateDescription'].'</td>
                <td align="right">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                <td align="right">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                <td align="right">'.number_format($row[$i]['Differentiate'],2,'.',',').'</td>
                </tr>';
            $last_closing=$row[$i]['Differentiate'];
        }
        $html.='</tbody></table>';
        return view('admin.report.html_viewer',compact('html','title'))->render();
    }

    public function PrintCashReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_cash_transactions=CashTransaction::where('company_id',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('Details','not like','%hide%')->whereNull('deleted_at')->orderBy('createdDate')->orderBy('id')->get();

            //$prev_date = date('Y-m-d', strtotime($request->fromDate .' -1 day'));
            //$get_max_id=CashTransaction::where('company_id',session('company_id'))->where('createdDate','=',$prev_date)->max('id');
            //$closing_amount=CashTransaction::where('company_id',session('company_id'))->where('id',$get_max_id)->first();

            $sum_of_debit_before_from_date=CashTransaction::where('company_id',session('company_id'))->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=CashTransaction::where('company_id',session('company_id'))->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
            //$closing_amount=$closing_amount->Differentiate;
        }
        else
        {
            return FALSE;
        }
        $footer=new CustomeFooter;
        $footer->footer();
        $pdf = new PDF();
        $pdf::setPrintHeader(false);

        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, 14);

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);
        $row=json_decode(json_encode($all_cash_transactions), true);

        $pdf::SetFont('helvetica', '', 15);
        $html='Cash Transactions';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=' Opening Balance : '.round($closing_amount,2);
        $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

        $balance=$closing_amount;

        $debit_total=0.0;
        $credit_total=0.0;

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="60">Date</th>
                <th align="center" width="100">PAD/REF</th>
                <th align="center" width="140">Details</th>
                <th align="right" width="80">Debit</th>
                <th align="right" width="80">Credit</th>
                <th align="right" width="90">Closing</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        $last_closing=0.0;
        for($i=0;$i<count($row);$i++)
        {
            if($row[$i]['Debit']!=0)
            {
                $debit_total += $row[$i]['Debit'];
                $balance = $balance + $row[$i]['Debit'];
            }
            elseif($row[$i]['Credit']!=0)
            {
                $credit_total += $row[$i]['Credit'];
                $balance = $balance - $row[$i]['Credit'];
            }
            else
            {
                $balance += $row[$i]['Differentiate'];
            }

            if($i%2==0)
            {
                $html .='<tr style="background-color: #e3e3e3">
                <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left" width="100">'.($row[$i]['PadNumber']).'</td>
                <td align="left" width="140">'.($row[$i]['Details']).'</td>
                <td align="right" width="80">'.($row[$i]['Debit']).'</td>
                <td align="right" width="80">'.($row[$i]['Credit']).'</td>
                <td align="right" width="90">'.number_format($balance,2,'.',',').'</td>
                </tr>';
            }
            else
            {
                $html .='<tr>
                <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="left" width="100">'.($row[$i]['PadNumber']).'</td>
                <td align="left" width="140">'.($row[$i]['Details']).'</td>
                <td align="right" width="80">'.($row[$i]['Debit']).'</td>
                <td align="right" width="80">'.($row[$i]['Credit']).'</td>
                <td align="right" width="90">'.number_format($balance,2,'.',',').'</td>
                </tr>';
            }
            $last_closing=$balance;
        }
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::SetFont('helvetica', 'B', 13);
        if($last_closing<0)
        {
            $html='<table border="0.5" cellpadding="2">';
            $html.= '
                 <tr>
                 <td width="300" align="right" colspan="2">Total : </td>
                 <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="90" align="right">'.number_format($last_closing,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
        }
        else
        {
            $html='<table border="0.5" cellpadding="2">';
            $html.= '
                 <tr>
                 <td width="300" align="right" colspan="2">Total : </td>
                 <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="90" align="right">'.number_format($last_closing,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
        }

        $pdf::lastPage();
        $time=time();
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$time.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }

    public function PrintExpenseCashReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_cash_transactions=CashTransaction::where('company_id',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('Details','not like','%hide%')->where('Type','expenses')->whereNull('deleted_at')->orderBy('createdDate')->orderBy('id')->get();
        }
        else
        {
            return FALSE;
        }
        $footer=new CustomeFooter;
        $footer->footer();
        $pdf = new PDF();
        $pdf::setPrintHeader(false);

        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, 14);

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);
        $row=json_decode(json_encode($all_cash_transactions), true);

        $pdf::SetFont('helvetica', '', 15);
        $html='Cash Transactions (ONLY EXPENSES)';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $balance=0.0;
        $credit_total=0.0;

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="60">Date</th>
                <th align="center" width="120">PAD/REF</th>
                <th align="center" width="180">Details</th>
                <th align="right" width="80">Credit</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        $last_closing=0.0;
        for($i=0;$i<count($row);$i++)
        {
            if($row[$i]['Credit']!=0)
            {
                $credit_total += $row[$i]['Credit'];
                $balance = $balance - $row[$i]['Credit'];
            }
            $html .='<tr>
            <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
            <td align="left" width="120">'.($row[$i]['PadNumber']).'</td>
            <td align="left" width="180">'.($row[$i]['Details']).'</td>
            <td align="right" width="80">'.($row[$i]['Credit']).'</td>
            </tr>';
            $last_closing=$balance;
        }
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::SetFont('helvetica', 'B', 13);
        $html='<table border="0.5" cellpadding="2">';
        $html.= '<tr>
             <td width="360" align="right" colspan="2">Total : </td>
             <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
             </tr>';
        $pdf::SetFillColor(255, 0, 0);
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::lastPage();
        $time=time();
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$time.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }

    public function PrintCashLogReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_cash_transactions=CashTransaction::with('user')->where('company_id',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('Details','not like','%hide%')->whereNull('deleted_at')->orderBy('id')->get();
        }
        else
        {
            return FALSE;
        }
        $footer=new CustomeFooter;
        $footer->footer();
        $pdf = new PDF();
        $pdf::setPrintHeader(false);

        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, 14);

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);
        $row=json_decode(json_encode($all_cash_transactions), true);

        $pdf::SetFont('helvetica', '', 15);
        $html='Cash Transactions Log Report';
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $pdf::SetFont('helvetica', '', 12);
        $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

        $debit_total=0.0;
        $credit_total=0.0;

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="right" width="100">DateTime</th>
                <th align="center" width="60">User</th>
                <th align="center" width="100">PAD/REF</th>
                <th align="center" width="140">Details</th>
                <th align="right" width="80">Debit</th>
                <th align="right" width="80">Credit</th>

            </tr>';
        $pdf::SetFont('helvetica', '', 10);
        for($i=0;$i<count($row);$i++)
        {
            $html .='<tr>
            <td align="left" width="100">'.(date('d-m-Y h:i:s', strtotime($row[$i]['created_at']))).'</td>
            <td align="center" width="60">'.($row[$i]['user']['name']).'</td>
            <td align="left" width="100">'.($row[$i]['PadNumber']).'</td>
            <td align="left" width="140">'.($row[$i]['Details']).'</td>
            <td align="right" width="80">'.($row[$i]['Debit']).'</td>
            <td align="right" width="80">'.($row[$i]['Credit']).'</td>

            </tr>';
        }
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::lastPage();
        $time=time();
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$time.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }

    public function ViewCashReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $all_cash_transactions=CashTransaction::where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('company_id',session('company_id'))->where('Details','not like','%hide%')->orderBy('createdDate')->get();
        }
        else
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }

        $row=json_decode(json_encode($all_cash_transactions), true);

        if(empty($row))
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }

        $title='CASH TRANSACTIONS : FROM '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));

        $balance=0.0;
        $debit_total=0.0;
        $credit_total=0.0;
        $html = '<table class="display" id="report_table"><thead>
            <tr>
                <th align="center">Date</th>
                <th align="center">PAD/REF</th>
                <th align="center">Details</th>
                <th align="right">Debit</th>
                <th align="right">Credit</th>
                <th align="right">Closing</th>
            </tr></thead><tbody>';
        $last_closing=0.0;
        for($i=0;$i<count($row);$i++)
        {
            $debit_total += $row[$i]['Debit'];
            $credit_total += $row[$i]['Credit'];
            $balance = $balance + $row[$i]['Differentiate'];
                $html .='<tr>
                <td align="center">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                <td align="center">'.($row[$i]['PadNumber']).'</td>
                <td align="left">'.($row[$i]['Details']).'</td>
                <td align="right">'.($row[$i]['Debit']).'</td>
                <td align="right">'.($row[$i]['Credit']).'</td>
                <td align="right">'.number_format($row[$i]['Differentiate'],2,'.',',').'</td>
                </tr>';
            $last_closing=$row[$i]['Differentiate'];
        }
        $html.='</tbody></table>';
        return view('admin.report.html_viewer',compact('html','title'))->render();
    }

    public function PrintExpenseReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!=''  && $request->filter=='all' && $request->category=='all')
        {
            $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->whereNull('deleted_at')->orderBy('expenseDate')->get());
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->category=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->category!='all')
        {
            $ids=ExpenseDetail::where('expense_category_id','=',$request->category)->where('company_id',session('company_id'))->whereNull('deleted_at')->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'expense_id');
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        else
        {
            return FALSE;
        }

        if($expense->first())
        {
            $row=json_decode(json_encode($expense), true);
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Expenses';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $total_sum=0.0;
            $vat_sum=0.0;
            $sub_total_sum=0.0;

            $pdf::SetFont('helvetica', '', 8);

            // if category is selected as all go for this code
            if($request->category==='all')
            {
                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="60">Expense#</th>
                    <th align="center" width="60">Employee</th>
                    <th align="center" width="50">Category</th>
                    <th align="center" width="140">Vendor</th>
                    <th align="center" width="70">TRN</th>
                    <th align="center" width="40">Taxable</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="45">NetTotal</th>
                </tr>';
                for($i=0;$i<count($row);$i++)
                {
                    $total_sum+=$row[$i]['expense_details'][0]['Total'];

                    $sub_total_sum+=$row[$i]['expense_details'][0]['rowSubTotal'];
                    $this_row_vat_amount=$row[$i]['expense_details'][0]['Total']*$row[$i]['expense_details'][0]['VAT']/100;
                    $vat_sum+=$this_row_vat_amount;
                    if($i%2==0)
                    {
                        $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="60">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="50">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="60">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="50">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                }
                $html.= '
                 <tr color="red">
                     <td width="425" align="right" colspan="6">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
            }
            else
            {
                $category_name=ExpenseCategory::select('Name')->where('id','=',$request->category)->first();
                $pdf::SetFont('helvetica', '', 12);
                $html=' Category : '.$category_name->Name;
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);
                $pdf::SetFont('helvetica', '', 8);

                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="110">Expense#</th>
                    <th align="center" width="60">Employee</th>
                    <th align="center" width="140">Vendor</th>
                    <th align="center" width="70">TRN</th>
                    <th align="center" width="40">Taxable</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="45">NetTotal</th>
                </tr>';
                for($i=0;$i<count($row);$i++)
                {
                    $total_sum+=$row[$i]['expense_details'][0]['Total'];

                    $sub_total_sum+=$row[$i]['expense_details'][0]['rowSubTotal'];
                    $this_row_vat_amount=$row[$i]['expense_details'][0]['Total']*$row[$i]['expense_details'][0]['VAT']/100;
                    $vat_sum+=$this_row_vat_amount;
                    if($i%2==0)
                    {
                        $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="110">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="110">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                }
                $html.= '
                 <tr color="red">
                     <td width="425" align="right" colspan="6">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
            }


            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function PrintCashExpenseReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!=''  && $request->filter=='all' && $request->category=='all')
        {
            $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->where('payment_type','cash')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->category=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->where('payment_type','cash')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->where('payment_type','cash')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->where('payment_type','cash')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->category!='all')
        {
            $ids=ExpenseDetail::where('expense_category_id','=',$request->category)->where('company_id',session('company_id'))->whereNull('deleted_at')->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'expense_id');
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->where('payment_type','cash')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->where('payment_type','cash')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->where('payment_type','cash')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        else
        {
            return FALSE;
        }

        if($expense->first())
        {
            $row=json_decode(json_encode($expense), true);
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Expenses';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $total_sum=0.0;
            $vat_sum=0.0;
            $sub_total_sum=0.0;

            $pdf::SetFont('helvetica', '', 8);

            // if category is selected as all go for this code
            if($request->category==='all')
            {
                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="60">Expense#</th>
                    <th align="center" width="60">Employee</th>
                    <th align="center" width="50">Category</th>
                    <th align="center" width="140">Vendor</th>
                    <th align="center" width="70">TRN</th>
                    <th align="center" width="40">Taxable</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="45">NetTotal</th>
                </tr>';
                for($i=0;$i<count($row);$i++)
                {
                    $total_sum+=$row[$i]['expense_details'][0]['Total'];

                    $sub_total_sum+=$row[$i]['expense_details'][0]['rowSubTotal'];
                    $this_row_vat_amount=$row[$i]['expense_details'][0]['Total']*$row[$i]['expense_details'][0]['VAT']/100;
                    $vat_sum+=$this_row_vat_amount;
                    if($i%2==0)
                    {
                        $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="60">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="50">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="60">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="50">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                }
                $html.= '
                 <tr color="red">
                     <td width="425" align="right" colspan="6">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
            }
            else
            {
                $category_name=ExpenseCategory::select('Name')->where('id','=',$request->category)->first();
                $pdf::SetFont('helvetica', '', 12);
                $html=' Category : '.$category_name->Name;
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);
                $pdf::SetFont('helvetica', '', 8);

                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="110">Expense#</th>
                    <th align="center" width="60">Employee</th>
                    <th align="center" width="140">Vendor</th>
                    <th align="center" width="70">TRN</th>
                    <th align="center" width="40">Taxable</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="45">NetTotal</th>
                </tr>';
                for($i=0;$i<count($row);$i++)
                {
                    $total_sum+=$row[$i]['expense_details'][0]['Total'];

                    $sub_total_sum+=$row[$i]['expense_details'][0]['rowSubTotal'];
                    $this_row_vat_amount=$row[$i]['expense_details'][0]['Total']*$row[$i]['expense_details'][0]['VAT']/100;
                    $vat_sum+=$this_row_vat_amount;
                    if($i%2==0)
                    {
                        $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="110">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="110">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                }
                $html.= '
                 <tr color="red">
                     <td width="425" align="right" colspan="6">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
            }


            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function PrintVATExpenseReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!=''  && $request->filter=='all' && $request->company=='all')
        {
            $expense=ExpenseResource::collection(Expense::with('expense_details')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
        }
        else if ($request->fromDate!='' && $request->toDate!=''  && $request->filter!='all' && $request->company!='all')
        {
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',$request->company)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',$request->company)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->company=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->company!='all')
        {
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',$request->company)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',$request->company)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',$request->company)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        else
        {
            return FALSE;
        }

        if($expense->first())
        {
            $row=json_decode(json_encode($expense), true);
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $lpo=Project::where('id',1)->first();
            $company_title = $lpo->Name;
            $company_email = $lpo->Email;
            $company_address = $lpo->Address;
            $company_mobile = $lpo->Contact;
            $company_fax = $lpo->FAX;
            $company_trn = $lpo->TRN;

            $base = URL::to('/storage/app/public/project/');
            $logo_url = $base . '/' . $lpo->logo;

            $html = '<table border="0">';
            $html .= '<tr>
            <td width="150" rowspan="6"><img src="' . $logo_url . '" height="100px;" width="100px;"></td>
            <td width="300" style="font-weight: bold;font-size: xx-large;"> ' . $company_title . '</td>
            <td width="85"></td>
        </tr>';
            $html .= '<tr>
            <td width="300" style="font-size: large;"> Email : ' . $company_email . '</td>
            <td width="85"></td>
        </tr>';
            $html .= '<tr>
            <td width="300" style="font-size: large;"> Address : ' . $company_address . '</td>
            <td width="85"></td>
        </tr>';
            $html .= '<tr>
            <td width="270" style="font-size: large;"> Phone : ' . $company_mobile . '</td>
            <td width="115" align="right" style="font-size: large;"></td>
        </tr>';
            $html .= '<tr>
            <td width="270" style="font-size: large;"> FAX : ' . $company_fax . '</td>
            <td width="115" align="right" style="font-size: large;"></td>
        </tr>';
            $html .= '<tr>
            <td width="270" style="font-size: large;"> TRN : ' . $company_trn . '</td>
            <td width="115" align="right" style="font-size: large;"></td>
        </tr>';
            $html .= '</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::writeHTML("<hr>", true, false, false, false, '');

            $pdf::SetFont('helvetica', '', 12);
            $title='Expense Report - From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y', strtotime($request->toDate));
            //$time='Printed on : '.date('d-m-Y h:i:s');
            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 8);
            //$pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(6);

            $total_sum=0.0;
            $vat_sum=0.0;
            $sub_total_sum=0.0;

            $pdf::SetFont('helvetica', '', 8);

            $html = '<table border="0.5" cellpadding="1">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="45">Date</th>
                <th align="center" width="80">REF#</th>
                <th align="center" width="230">Vendor</th>
                <th align="center" width="70">TRN</th>
                <th align="center" width="40">Taxable</th>
                <th align="center" width="35">VAT</th>
                <th align="center" width="50">TotalAmount</th>
            </tr>';
            for($i=0;$i<count($row);$i++)
            {
                $total_sum+=$row[$i]['expense_details'][0]['Total'];
                $sub_total_sum+=$row[$i]['expense_details'][0]['rowSubTotal'];
                $this_row_vat_amount=$row[$i]['expense_details'][0]['Total']*$row[$i]['expense_details'][0]['VAT']/100;
                $vat_sum+=$this_row_vat_amount;
                $html .='<tr style="background-color: #e3e3e3;">
                <td align="center" width="45">'.(date('d-M-y', strtotime($row[$i]['expenseDate']))).'</td>
                <td align="left" width="80">'.($row[$i]['referenceNumber']).'</td>
                <td align="left" width="230">'.($row[$i]['api_supplier']['Name']).'</td>
                <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                <td align="right" width="50">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                </tr>';
            }
//            $html.= '
//             <tr color="red" style="font-weight: bold;">
//                 <td width="425" align="right" colspan="6">Total :</td>
//                 <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
//                 <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
//                 <td width="50" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
//             </tr>';
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $html = '<table cellpadding="1">';
            $html .= '<tr>
                <td align="right" width="370" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;"> Sub Total </td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($total_sum, 2, '.', ','))) . '</td>
                </tr>';
            $html .= '<tr>
                <td align="right" width="370" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;"> Total VAT </td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($vat_sum, 2, '.', ','))) . '</td>
                </tr>';
            $html .= '<tr>
                <td align="right" width="370" colspan="3"></td>
                <td align="right" width="100" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;"> Grand Total </td>
                <td align="right" width="80" colspan="2" style="border: 0.5px solid black;font-weight: bold;background-color: #dfe3e6;">' . ((number_format($sub_total_sum, 2, '.', ','))) . '</td>
                </tr>';
            $html .= '</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            $pdf::SetFillColor(255, 0, 0);


            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function PrintLandscapeExpenseReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!=''  && $request->filter=='all' && $request->category=='all')
        {
            $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->category=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->category!='all')
        {
            $ids=ExpenseDetail::where('expense_category_id','=',$request->category)->where('company_id',session('company_id'))->whereNull('deleted_at')->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'expense_id');
            if($request->filter=='with')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '!=', 0.00)->orderBy('expenseDate')->get());
            }
            elseif($request->filter=='without')
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->where('totalVat', '==', 0.00)->orderBy('expenseDate')->get());
            }
            else
            {
                $expense=ExpenseResource::collection(Expense::with('expense_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->orderBy('expenseDate')->get());
            }
        }
        else
        {
            return FALSE;
        }

        if($expense->first())
        {
            $row=json_decode(json_encode($expense), true);
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('L', 'A4');$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 12);
            $title='Expenses - From '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $time='Date : '.date('d-m-Y h:i:s');
            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(6);

            $total_sum=0.0;
            $vat_sum=0.0;
            $sub_total_sum=0.0;

            $pdf::SetFont('helvetica', '', 8);

            // if category is selected as all go for this code
            if($request->category==='all')
            {
                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="80">Expense#</th>
                    <th align="center" width="60">Employee</th>
                    <th align="center" width="60">Category</th>
                    <th align="center" width="190">Description</th>
                    <th align="center" width="170">Vendor</th>
                    <th align="center" width="70">TRN</th>
                    <th align="center" width="40">Taxable</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="45">NetTotal</th>
                </tr>';
                for($i=0;$i<count($row);$i++)
                {
                    $total_sum+=$row[$i]['expense_details'][0]['Total'];

                    $sub_total_sum+=$row[$i]['expense_details'][0]['rowSubTotal'];
                    $this_row_vat_amount=$row[$i]['expense_details'][0]['Total']*$row[$i]['expense_details'][0]['VAT']/100;
                    $vat_sum+=$this_row_vat_amount;
                    if($this_row_vat_amount==0)
                    {
                        $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="80">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="60">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                        <td align="left" width="190">'.($row[$i]['expense_details'][0]['Description']).'</td>
                        <td align="left" width="170">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="80">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="60">'.($row[$i]['expense_details'][0]['api_expense_category']['Name']).'</td>
                        <td align="left" width="190">'.($row[$i]['expense_details'][0]['Description']).'</td>
                        <td align="left" width="170">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                }
                $html.= '
                 <tr color="red" style="font-weight: bold;">
                     <td width="675" align="right" colspan="6">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
            }
            else
            {
                $category_name=ExpenseCategory::select('Name')->where('id','=',$request->category)->first();
                $pdf::SetFont('helvetica', '', 12);
                $html=' Category : '.$category_name->Name;
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);
                $pdf::SetFont('helvetica', '', 8);

                $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="110">Expense#</th>
                    <th align="center" width="60">Employee</th>
                    <th align="center" width="140">Vendor</th>
                    <th align="center" width="70">TRN</th>
                    <th align="center" width="40">Taxable</th>
                    <th align="center" width="35">VAT</th>
                    <th align="center" width="45">NetTotal</th>
                </tr>';
                for($i=0;$i<count($row);$i++)
                {
                    $total_sum+=$row[$i]['expense_details'][0]['Total'];

                    $sub_total_sum+=$row[$i]['expense_details'][0]['rowSubTotal'];
                    $this_row_vat_amount=$row[$i]['expense_details'][0]['Total']*$row[$i]['expense_details'][0]['VAT']/100;
                    $vat_sum+=$this_row_vat_amount;
                    if($i%2==0)
                    {
                        $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="110">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['expenseDate']))).'</td>
                        <td align="left" width="110">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="60">'.($row[$i]['api_employee']['Name']).'</td>
                        <td align="left" width="140">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="left" width="70">'.($row[$i]['api_supplier']['TRNNumber']).'</td>
                        <td align="right" width="40">'.(number_format($row[$i]['expense_details'][0]['Total'],2,'.',',')).'</td>
                        <td align="right" width="35">'.(number_format($this_row_vat_amount,2,'.',',')).'</td>
                        <td align="right" width="45">'.(number_format($row[$i]['expense_details'][0]['rowSubTotal'],2,'.',',')).'</td>
                        </tr>';
                    }
                }
                $html.= '
                 <tr color="red">
                     <td width="425" align="right" colspan="6">Total :</td>
                     <td width="40" align="right">'.number_format($total_sum,2,'.',',').'</td>
                     <td width="35" align="right">'.number_format($vat_sum,2,'.',',').'</td>
                     <td width="45" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
            }


            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function PrintPurchaseReport(Request $request)
    {
        if($request->supplier_id=='all' && $request->fromDate!='' && $request->toDate!='' && $request->filter=='all')
        {
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate));
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->supplier_id=='all' && $request->filter!='all')
        {
            if($request->filter=='with')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '!=', 0.00));
            }
            elseif($request->filter=='without')
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate)->where('totalVat', '==', 0.00));
            }
            else
            {
                $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('PurchaseDate','>=',$request->fromDate)->where('PurchaseDate','<=',$request->toDate));
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->supplier_id!='all')
        {
            $purchase=PurchaseResource::collection(Purchase::with('purchase_details_without_trash')->get()->where('company_id',session('company_id'))->where('isActive','=',1)->where('supplier_id','=',$request->supplier_id)->whereBetween('PurchaseDate', [$request->fromDate, $request->toDate]));
        }
        else
        {
            return FALSE;
        }

        if($purchase->first())
        {
            $company_title='WATAN PHARMA LLP.';
            $company_address='MUSSAFAH M13,PLOT 100, ABU DHABI,UAE';
            $company_email='Email : info@alhamood.ae';
            $company_mobile='Mobile : +971-25550870  +971-557383866  +971-569777861';
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);
            $row=json_decode(json_encode($purchase), true);
            //echo "<pre>123";print_r($row);die;

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 15);
            $html='PURCHASE REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $sub_total_sum=0.0;
            $paid_total_sum=0.0;
            $balance_total_sum=0.0;
            $qty_sum=0.0;
            $rowTotal_sum=0.0;
            $VAT_sum=0.0;

            $pdf::SetFont('helvetica', '', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="30">PAD#</th>
                    <th align="right" width="30">LPO#</th>
                    <th align="center" width="110">Vendor</th>
                    <th align="center" width="50">Qty</th>
                    <th align="center" width="30">Rate</th>
                    <th align="center" width="55">Total</th>
                    <th align="center" width="45">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="55">Paid</th>
                    <th align="center" width="55">Balance</th>
                </tr>';
            for($i=0;$i<count($row);$i++)
            {
                $sub_total_sum+=$row[$i]['purchase_details_without_trash'][0]['rowSubTotal'];
                $paid_total_sum+=$row[$i]['paidBalance'];
                $balance_total_sum+=$row[$i]['remainingBalance'];
                $qty_sum+=$row[$i]['purchase_details_without_trash'][0]['Quantity'];
                $rowTotal_sum+=$row[$i]['purchase_details_without_trash'][0]['rowTotal'];
                $VAT_sum+=$row[$i]['purchase_details_without_trash'][0]['rowTotal']*$row[$i]['purchase_details_without_trash'][0]['VAT']/100;
                if($i%2==0)
                {
                    $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['PurchaseDate']))).'</td>
                        <td align="center" width="30">'.($row[$i]['purchase_details_without_trash'][0]['PadNumber']).'</td>
                        <td align="center" width="30">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="110">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="right" width="50">'.($row[$i]['purchase_details_without_trash'][0]['Quantity']).'</td>
                        <td align="right" width="30">'.($row[$i]['purchase_details_without_trash'][0]['Price']).'</td>
                        <td align="right" width="55">'.($row[$i]['purchase_details_without_trash'][0]['rowTotal']).'</td>
                        <td align="right" width="45">'.(($row[$i]['purchase_details_without_trash'][0]['rowTotal']*$row[$i]['purchase_details_without_trash'][0]['VAT']/100)).'</td>
                        <td align="right" width="50">'.($row[$i]['purchase_details_without_trash'][0]['rowSubTotal']).'</td>
                        <td align="right" width="55">'.($row[$i]['paidBalance']).'</td>
                        <td align="right" width="55">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                        <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['PurchaseDate']))).'</td>
                        <td align="center" width="30">'.($row[$i]['purchase_details_without_trash'][0]['PadNumber']).'</td>
                        <td align="center" width="30">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="110">'.($row[$i]['api_supplier']['Name']).'</td>
                        <td align="right" width="50">'.($row[$i]['purchase_details_without_trash'][0]['Quantity']).'</td>
                        <td align="right" width="30">'.($row[$i]['purchase_details_without_trash'][0]['Price']).'</td>
                        <td align="right" width="55">'.($row[$i]['purchase_details_without_trash'][0]['rowTotal']).'</td>
                        <td align="right" width="45">'.(($row[$i]['purchase_details_without_trash'][0]['rowTotal']*$row[$i]['purchase_details_without_trash'][0]['VAT']/100)).'</td>
                        <td align="right" width="50">'.($row[$i]['purchase_details_without_trash'][0]['rowSubTotal']).'</td>
                        <td align="right" width="55">'.($row[$i]['paidBalance']).'</td>
                        <td align="right" width="55">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                }

            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', '', 8);
            $html='<table border="0.5" cellpadding="1">';
            $html.= '
             <tr color="red">
                 <td width="215" align="right" colspan="4">Total :- </td>
                 <td width="50" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                 <td width="30"></td>
                 <td width="55" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                 <td width="45" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                 <td width="50" align="right">'.number_format($sub_total_sum,2,'.',',').'</td>
                 <td width="55" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                 <td width="55" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
             </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';

            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return false;
        }
    }

    public function PrintSalesReportByVehicle(Request $request)
    {
        if($request->vehicle_id=='all' && $request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->where('company_id',session('company_id'))->whereBetween('SaleDate', [$request->fromDate, $request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get());

        }
        elseif ($request->fromDate!='' && $request->toDate!='' && $request->vehicle_id!='')
        {
            $ids=SaleDetail::where('vehicle_id','=',$request->vehicle_id)->where('company_id',session('company_id'))->whereNull('deleted_at')->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'sale_id');
            $sales=SalesResource::collection(Sale::with('sale_details')->whereIn('id', $ids)->where('company_id',session('company_id'))->whereBetween('SaleDate', [$request->fromDate, $request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get());
        }
        else
        {
            return FALSE;
        }

        if($sales->first())
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $row=json_decode(json_encode($sales), true);
            $row=array_values($row);
            //echo "<pre>";print_r($row);die;

            // copy all data to new array and sort it according to pad number and then print
            $new_master_array=array();
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['sale_details'][0]['PadNumber']!='0')
                {
                    $master_row=array();
                    $master_row['PadNumber']=$row[$i]['sale_details'][0]['PadNumber'] ?? 'NA';
                    $master_row['Name']=$row[$i]['api_customer']['Name'];
                    $master_row['registrationNumber']=$row[$i]['sale_details'][0]['api_vehicle']['registrationNumber'] ?? '';
                    $master_row['Quantity']=$row[$i]['sale_details'][0]['Quantity'] ?? 0;
                    $master_row['Price']=$row[$i]['sale_details'][0]['Price'] ?? 0;
                    $master_row['rowTotal']=$row[$i]['sale_details'][0]['rowTotal'] ?? 0;
                    $master_row['VAT']=($row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100);
                    $master_row['rowSubTotal']=$row[$i]['sale_details'][0]['rowSubTotal'];
                    $master_row['paidBalance']=$row[$i]['paidBalance'];
                    $master_row['remainingBalance']=$row[$i]['remainingBalance'];
                    $master_row['SaleDate']=$row[$i]['SaleDate'];
                    $master_row['IsPaid']=$row[$i]['IsPaid'];
                    $new_master_array[]=$master_row;
                }
            }
            $keys = array_column($new_master_array, 'PadNumber');
            array_multisort($keys, SORT_ASC, $new_master_array);
            $row=$new_master_array;

            $pdf::SetFont('helvetica', '', 8);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 8);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            if($request->vehicle_id==='all')
            {
                //<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                $pdf::SetFont('helvetica', 'B', 8);
                $html = '<table border="0.5" cellpadding="1">
                    <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFont('helvetica', '', 8);

                $VAT_sum=0.0;
                $rowTotal_sum=0.0;
                $qty_sum=0.0;
                $sub_total_sum=0.0;
                $paid_total_sum=0.0;
                $balance_total_sum=0.0;
                $rowSubTotal=0.0;
                for($i=0;$i<count($row);$i++)
                {
                    $sub_total_sum+=$row[$i]['rowSubTotal'];
                    $paid_total_sum+=$row[$i]['paidBalance'];
                    $balance_total_sum+=$row[$i]['remainingBalance'];
                    $qty_sum+=$row[$i]['Quantity'];
                    $rowTotal_sum+=$row[$i]['rowTotal'];
                    $VAT_sum+=$row[$i]['VAT'];
                    $rowSubTotal+=$row[$i]['rowSubTotal'];
                    if($row[$i]['IsPaid']==1)
                    {
                        $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                }

                $html.= '<tr color="red">
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="50"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="20"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';

                $html.='<tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $vehicle_registrationNumber=Vehicle::select('registrationNumber')->where('id','=',$request->vehicle_id)->first();
                $pdf::SetFont('helvetica', '', 12);
                $html=' Vehicle : '.$vehicle_registrationNumber->registrationNumber;
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

                //<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                $pdf::SetFont('helvetica', 'B', 8);
                $html = '<table border="0.5" cellpadding="1">
                    <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFont('helvetica', '', 8);

                $VAT_sum=0.0;
                $rowTotal_sum=0.0;
                $qty_sum=0.0;
                $sub_total_sum=0.0;
                $paid_total_sum=0.0;
                $balance_total_sum=0.0;
                $rowSubTotal=0.0;
                for($i=0;$i<count($row);$i++)
                {
                    $sub_total_sum+=$row[$i]['rowSubTotal'];
                    $paid_total_sum+=$row[$i]['paidBalance'];
                    $balance_total_sum+=$row[$i]['remainingBalance'];
                    $qty_sum+=$row[$i]['Quantity'];
                    $rowTotal_sum+=$row[$i]['rowTotal'];
                    $VAT_sum+=$row[$i]['VAT'];
                    $rowSubTotal+=$row[$i]['rowSubTotal'];
                    if($row[$i]['IsPaid']==1)
                    {
                        $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                    else
                    {
                        $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                    }
                }

                $html.= '<tr color="red">
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="50"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="20"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';

                $html.='<tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            $pdf::lastPage();

            $time=time();
            $name='SALES_REPORT_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return false;
        }
    }

    public function PrintSalesReport(Request $request)
    {

        if(session('company_id')==4 OR session('company_id')==5 OR session('company_id')==8)
        {
            return $this->PrintSalesReportService($request);
        }
        if($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->payment_filter=='all')
        {
            $sales=Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get();
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->payment_filter!='all')
        {
            if($request->payment_filter=='paid')
            {
                $sales = Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->where('paidBalance', '!=', 0.00)->orderBy('SaleDate')->get();
            }
            elseif($request->payment_filter=='unpaid')
            {
                $sales = Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->where('remainingBalance', '!=', 0.00)->orderBy('SaleDate')->get();
            }
            else
            {
                $sales=Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get();
            }
        }
        elseif ($request->fromDate!='' && $request->toDate!='' && $request->filter!='all' && $request->payment_filter=='all')
        {
            if($request->filter=='with')
            {
                $sales = Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->where('totalVat', '!=', 0.00)->orderBy('SaleDate')->get();
            }
            elseif($request->filter=='without')
            {
                $sales = Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->where('totalVat', '==', 0.00)->orderBy('SaleDate')->get();
            }
            else
            {
                //$sales=SalesResource::collection(Sale::with('sale_details')->where('company_id',session('company_id'))->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('isActive','=','1')->where('deleted_at','=',NULL)->orderBy('SaleDate')->get());

                $sales=Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get();
            }
        }
        elseif ($request->fromDate!='' && $request->toDate!='' && $request->filter!='all' && $request->payment_filter!='all')
        {
            if($request->filter=='with' && $request->payment_filter=='paid')
            {
                $sales = Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->where('totalVat', '!=', 0.00)->where('paidBalance', '!=', 0.00)->orderBy('SaleDate')->get();
            }
            elseif($request->filter=='without' && $request->payment_filter=='paid')
            {
                $sales = Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->where('totalVat', '==', 0.00)->where('paidBalance', '!=', 0.00)->orderBy('SaleDate')->get();
            }
            elseif($request->filter=='with' && $request->payment_filter=='unpaid')
            {
                $sales = Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->where('totalVat', '==', 0.00)->where('remainingBalance', '!=', 0.00)->orderBy('SaleDate')->get();
            }
            elseif($request->filter=='without' && $request->payment_filter=='unpaid')
            {
                $sales = Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->where('totalVat', '==', 0.00)->where('remainingBalance', '!=', 0.00)->orderBy('SaleDate')->get();
            }
            else
            {
                //$sales=SalesResource::collection(Sale::with('sale_details')->where('company_id',session('company_id'))->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('isActive','=','1')->where('deleted_at','=',NULL)->orderBy('SaleDate')->get());

                $sales=Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get();
            }
        }
        else
        {
            return FALSE;
        }

        if($sales->first())
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::setPrintFooter(true);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $row=json_decode(json_encode($sales), true);
            $row=array_values($row);
            //echo "<pre>";print_r($row);die;

            // copy all data to new array and sort it according to pad number and then print
            $new_master_array=array();
            for($i=0;$i<count($row);$i++)
            {
//                if($row[$i]['sale_details'][0]['PadNumber']!='0')
//                {
                    $master_row=array();
                    $master_row['PadNumber']=$row[$i]['sale_details'][0]['PadNumber'] ?? 'NA';
                    $master_row['Name']=$row[$i]['customer']['Name'];
                    $master_row['registrationNumber']=$row[$i]['sale_details'][0]['vehicle']['registrationNumber'] ?? '';
                    $master_row['Quantity']=$row[$i]['sale_details'][0]['Quantity'] ?? 0;
                    $master_row['Price']=$row[$i]['sale_details'][0]['Price'] ?? 0;
                    $master_row['rowTotal']=$row[$i]['sale_details'][0]['rowTotal'] ?? 0;
                    $master_row['VAT']=($row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100);
                    $master_row['rowSubTotal']=$row[$i]['sale_details'][0]['rowSubTotal'];
                    $master_row['paidBalance']=$row[$i]['paidBalance'];
                    $master_row['remainingBalance']=$row[$i]['remainingBalance'];
                    $master_row['SaleDate']=$row[$i]['SaleDate'];
                    $master_row['IsPaid']=$row[$i]['IsPaid'];
                    $new_master_array[]=$master_row;
//                }
            }
            $keys = array_column($new_master_array, 'PadNumber');
            array_multisort($keys, SORT_ASC, $new_master_array);
            $row=$new_master_array;

            $pdf::SetFont('helvetica', '', 8);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 8);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);



            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 8);

            $VAT_sum=0.0;
            $rowTotal_sum=0.0;
            $qty_sum=0.0;
            $sub_total_sum=0.0;
            $paid_total_sum=0.0;
            $balance_total_sum=0.0;
            $rowSubTotal=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $sub_total_sum+=$row[$i]['rowSubTotal'];
                $paid_total_sum+=$row[$i]['paidBalance'];
                $balance_total_sum+=$row[$i]['remainingBalance'];
                $qty_sum+=$row[$i]['Quantity'];
                $rowTotal_sum+=$row[$i]['rowTotal'];
                $VAT_sum+=$row[$i]['VAT'];
                $rowSubTotal+=$row[$i]['rowSubTotal'];
                if($row[$i]['IsPaid']==1)
                {
                    $html .='<tr style="background-color: #e3e3e3">
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                    <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                    <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                    <td align="left" width="130">'.($row[$i]['Name']).'</td>
                    <td align="center" width="50">'.($row[$i]['registrationNumber']).'</td>
                    <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                    <td align="right" width="20">'.($row[$i]['Price']).'</td>
                    <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                    <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                    <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                    <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                    <td align="right" width="50">'.($row[$i]['remainingBalance']).'</td>
                    </tr>';
                }
            }
            $html.= '
                 <tr color="red">
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="50"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="20"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';

            //<tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
            $html.='<tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="50">Vehicle</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="20">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', '', 14);

            $cash_percentage=$paid_total_sum/$rowSubTotal*100;
            if($cash_percentage>50)
            {
                $cash_percentage='<span style="color: darkgreen">('.(number_format($cash_percentage,'2','.',',')).'%)</span>';
            }
            else
            {
                $cash_percentage='<span style="color: red">('.(number_format($cash_percentage,2,'.',',')).'%)</span>';
            }
            $html='<b>Cash Sales : '.number_format($paid_total_sum,2,'.',',').' '.$cash_percentage.'</b>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
            $pdf::Ln();
            $credit_percentage=$balance_total_sum/$rowSubTotal*100;
            if($credit_percentage<50)
            {
                $credit_percentage='<span style="color: darkgreen">('.(number_format($credit_percentage,'2','.',',')).'%)</span>';
            }
            else
            {
                $credit_percentage='<span style="color: red">('.(number_format($credit_percentage,2,'.',',')).'%)</span>';
            }
            $html='<b>Credit Sales : '.number_format($balance_total_sum,2,'.',',').' '.$credit_percentage.'</b>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::lastPage();

            $time=time();
            $name='SALES_REPORT_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function PrintSalesReportService($request)
    {
        if($request->customer_id!='' && $request->fromDate!='' && $request->toDate!='')
        {
            $sales=SalesResource::collection(Sale::with('sale_details')->get()->where('SaleDate','>=',date("y/m/d", strtotime($request->fromDate.' 23:59:59')))->where('SaleDate','<=',$request->toDate.' 23:59:59')->where('customer_id',' =',$request->customer_id));

        }
        elseif ($request->fromDate!='' && $request->toDate!='')
        {
            if($request->filter=='with')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',session('company_id'))->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '!=', 0.00)->where('isActive','=',1)->sortBy('sale_details.'));
            }
            elseif($request->filter=='without')
            {
                $sales = SalesResource::collection(Sale::with('sale_details')->get()->where('company_id',session('company_id'))->where('SaleDate', '>=', $request->fromDate)->where('SaleDate', '<=', $request->toDate)->where('totalVat', '==', 0.00)->where('isActive','=',1)->sortBy('sale_details.'));
            }
            else
            {
                $sales=Sale::select('id','customer_id','SaleDate','IsPaid','paidBalance','remainingBalance',)->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','VAT','rowSubTotal');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');},'customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('SaleDate',[$request->fromDate,$request->toDate])->where('isActive','=','1')->orderBy('SaleDate')->get();
            }
        }
        else
        {
            return FALSE;
        }

        if($sales->first())
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $row=json_decode(json_encode($sales), true);
            $row=array_values($row);
            //echo "<pre>";print_r($row);die;

            // copy all data to new array and sort it according to pad number and then print
            $new_master_array=array();
            for($i=0;$i<count($row);$i++)
            {
//                if($row[$i]['sale_details'][0]['PadNumber']!='0')
//                {
                $master_row=array();
                $master_row['PadNumber']=$row[$i]['sale_details'][0]['PadNumber'] ?? 'NA';
                $master_row['Name']=$row[$i]['customer']['Name'];
                $master_row['registrationNumber']=$row[$i]['sale_details'][0]['vehicle']['registrationNumber'] ?? '';
                $master_row['Quantity']=$row[$i]['sale_details'][0]['Quantity'] ?? 0;
                $master_row['Price']=$row[$i]['sale_details'][0]['Price'] ?? 0;
                $master_row['rowTotal']=$row[$i]['sale_details'][0]['rowTotal'] ?? 0;
                $master_row['VAT']=($row[$i]['sale_details'][0]['rowTotal']*$row[$i]['sale_details'][0]['VAT']/100);
                $master_row['rowSubTotal']=$row[$i]['sale_details'][0]['rowSubTotal'];
                $master_row['paidBalance']=$row[$i]['paidBalance'];
                $master_row['remainingBalance']=$row[$i]['remainingBalance'];
                $master_row['SaleDate']=$row[$i]['SaleDate'];
                $master_row['IsPaid']=$row[$i]['IsPaid'];
                $new_master_array[]=$master_row;
//                }
            }
            $keys = array_column($new_master_array, 'PadNumber');
            array_multisort($keys, SORT_ASC, $new_master_array);
            $row=$new_master_array;

            $pdf::SetFont('helvetica', '', 8);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 8);
            $html='SALES REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);



            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="70">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 8);

            $VAT_sum=0.0;
            $rowTotal_sum=0.0;
            $qty_sum=0.0;
            $sub_total_sum=0.0;
            $paid_total_sum=0.0;
            $balance_total_sum=0.0;
            $rowSubTotal=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $sub_total_sum+=$row[$i]['rowSubTotal'];
                $paid_total_sum+=$row[$i]['paidBalance'];
                $balance_total_sum+=$row[$i]['remainingBalance'];
                $qty_sum+=$row[$i]['Quantity'];
                $rowTotal_sum+=$row[$i]['rowTotal'];
                $VAT_sum+=$row[$i]['VAT'];
                $rowSubTotal+=$row[$i]['rowSubTotal'];
                $style='';
                if($row[$i]['remainingBalance']!=0)
                {
                    $style='background-color: #aeb0af;';
                }
                $html .='<tr>
                <td align="center" width="45">'.(date('d-m-Y', strtotime($row[$i]['SaleDate']))).'</td>
                <td align="left" width="35">'.($row[$i]['PadNumber']).'</td>
                <td align="left" width="130">'.($row[$i]['Name']).'</td>
                <td align="right" width="40">'.($row[$i]['Quantity']).'</td>
                <td align="right" width="70">'.($row[$i]['Price']).'</td>
                <td align="right" width="45">'.($row[$i]['rowTotal']).'</td>
                <td align="right" width="40">'.(number_format($row[$i]['VAT'],2,'.',',')).'</td>
                <td align="right" width="50">'.($row[$i]['rowSubTotal']).'</td>
                <td align="right" width="50">'.($row[$i]['paidBalance']).'</td>
                <td align="right" width="50" style="'.$style.'">'.($row[$i]['remainingBalance']).'</td>
                </tr>';
            }
            $html.= '
                 <tr color="red">
                     <td width="45" align="right"></td>
                     <td width="35"></td>
                     <td width="130"></td>
                     <td width="40" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                     <td width="70"></td>
                     <td width="45" align="right">'.number_format($rowTotal_sum,2,'.',',').'</td>
                     <td width="40" align="right">'.number_format($VAT_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($rowSubTotal,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($paid_total_sum,2,'.',',').'</td>
                     <td width="50" align="right">'.number_format($balance_total_sum,2,'.',',').'</td>
                 </tr>';

            $html.='<tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="45">Date</th>
                    <th align="center" width="35">PAD#</th>
                    <th align="center" width="130">Customer</th>
                    <th align="center" width="40">Qty</th>
                    <th align="center" width="70">Rate</th>
                    <th align="center" width="45">Total</th>
                    <th align="center" width="40">VAT</th>
                    <th align="center" width="50">SubTotal</th>
                    <th align="center" width="50">Paid</th>
                    <th align="center" width="50">Balance</th>
                </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', '', 14);

            $cash_percentage=$paid_total_sum/$rowSubTotal*100;
            if($cash_percentage>50)
            {
                $cash_percentage='<span style="color: darkgreen">('.(number_format($cash_percentage,'2','.',',')).'%)</span>';
            }
            else
            {
                $cash_percentage='<span style="color: red">('.(number_format($cash_percentage,2,'.',',')).'%)</span>';
            }
            $html='<b>Cash Sales : '.number_format($paid_total_sum,2,'.',',').' '.$cash_percentage.'</b>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
            $pdf::Ln();
            $credit_percentage=$balance_total_sum/$rowSubTotal*100;
            if($credit_percentage<50)
            {
                $credit_percentage='<span style="color: darkgreen">('.(number_format($credit_percentage,'2','.',',')).'%)</span>';
            }
            else
            {
                $credit_percentage='<span style="color: red">('.(number_format($credit_percentage,2,'.',',')).'%)</span>';
            }

            $html='<b>Credit Sales : '.number_format($balance_total_sum,2,'.',',').' '.$credit_percentage.'</b>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::Ln();
            $html='<b>Total Sales : '.number_format($rowSubTotal,2,'.',',').' (100%)</b>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::lastPage();

            $time=time();
            $name='SERVICE_SALES_REPORT_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function PrintCustomerStatement()
    {
        $result_array=array();
        $customers=Customer::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id','!=',4)->where('isActive',1)->get();
        foreach ($customers as $customer)
        {
            //get diff of total debit and credit column
            $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Credit');
            $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Debit');
            $diff=$debit_sum-$credit_sum;
            $limit=CustomerPrice::select('customerLimit')->where('customer_id',$customer->id)->first();
            if($diff!=0)
            {
                $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff,'limit'=>$limit->customerLimit);
                $result_array[]=$temp;
                unset($temp);
            }
        }
        $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
        $row=array_values($row);
        //echo "<pre>";print_r($row);die;

        //$data=SalesResource::collection(Sale::get()->where('remainingBalance','!=',0));
        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='CUSTOMER RECEIVABLE SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s A');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $html='Balance';
            $pdf::Cell(95,5,$html,'',0,'L');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="20">SN</th>
                <th align="center" width="190">Customer Name</th>
                <th align="center" width="187">Cell</th>
                <th align="right" width="60">Balance</th>
                <th align="right" width="60">Limit</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            $total_advances=0.0;

            $pos_arr=array(); $neg_arr=array();
            foreach($row as $key=>$value)
            {
                ($value['Differentiate']<0) ?  $neg_arr[]=$value : $pos_arr[]=$value;
            }
            for($i=0;$i<count($pos_arr);$i++)
            {
                $color='green';
                if($pos_arr[$i]['Differentiate']>$pos_arr[$i]['limit'])
                {
                    $color='red';
                }
                $total_balance+=$pos_arr[$i]['Differentiate'];
                $html .='<tr>
                <td align="center" width="20">'.($i+1).'</td>
                <td align="left" width="190">'.($pos_arr[$i]['Name']).'</td>
                <td align="left" width="187">'.($pos_arr[$i]['Mobile']).'</td>
                <td align="right" width="60" style="color:'.$color.';">'.(number_format($pos_arr[$i]['Differentiate'],2,'.',',')).'</td>
                <td align="right" width="60">'.(number_format($pos_arr[$i]['limit'],2,'.',',')).'</td>
                </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            $html = '<table border="0" cellpadding="1"><tr color="red" style="font-weight: bold;font-size: 12px;">
                         <td width="450" align="right" colspan="3">Balance Total : </td>
                         <td width="80" align="right">'. number_format($total_balance, 2, '.', ',') .'</td>
                     </tr>';
            $html .= '</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $html='Advances';
            $pdf::Cell(95,5,$html,'',0,'L');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="20">SN</th>
                <th align="center" width="190">Customer Name</th>
                <th align="center" width="187">Cell</th>
                <th align="right" width="60">Balance</th>
                <th align="right" width="60">Limit</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            for($i=0;$i<count($neg_arr);$i++)
            {
                $color='green';
                if($neg_arr[$i]['Differentiate']>$neg_arr[$i]['limit'])
                {
                    $color='red';
                }
                $total_advances+=$neg_arr[$i]['Differentiate'];
                $html .='<tr>
                <td align="center" width="20">'.($i+1).'</td>
                <td align="left" width="190">'.($neg_arr[$i]['Name']).'</td>
                <td align="left" width="187">'.($neg_arr[$i]['Mobile']).'</td>
                <td align="right" width="60" style="color:'.$color.';">'.(number_format($neg_arr[$i]['Differentiate'],2,'.',',')).'</td>
                <td align="right" width="60">'.(number_format($neg_arr[$i]['limit'],2,'.',',')).'</td>
                </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            $html = '<table border="0" cellpadding="1"><tr color="red" style="font-weight: bold;font-size: 12px;">
                         <td width="450" align="right" colspan="3">Advance Total : </td>
                         <td width="80" align="right">'. number_format($total_advances, 2, '.', ',') .'</td>
                     </tr>';
            $html .= '</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0" cellpadding="0">';
            $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">NET TOTAL : </td>
                     <td width="80" align="right">'.number_format($total_balance-abs($total_advances),2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function PrintCustomerStatementForDate(Request $request)
    {
        if($request->toDate!='')
        {
            $result_array=array();
            $customers=Customer::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id','!=',4)->where('isActive',1)->get();
            foreach ($customers as $customer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('customer_id',$customer->id)->where('createdDate','<=',$request->toDate)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Credit');
                $debit_sum=AccountTransaction::where('customer_id',$customer->id)->where('createdDate','<=',$request->toDate)->whereNull('updateDescription')->whereNull('deleted_at')->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $limit=CustomerPrice::select('customerLimit')->where('customer_id',$customer->id)->first();
                if($diff!=0)
                {
                    $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff,'limit'=>$limit->customerLimit);
                    $result_array[]=$temp;
                    unset($temp);
                }
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            //echo "<pre>";print_r($row);die;

            //$data=SalesResource::collection(Sale::get()->where('remainingBalance','!=',0));
            if(!empty($row))
            {
                $footer=new CustomeFooter;
                $footer->footer();
                $pdf = new PDF();
                $pdf::setPrintHeader(false);

                $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf::SetAutoPageBreak(TRUE, 14);

                $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
                $pdf::SetFillColor(255,255,0);

                $pdf::SetFont('helvetica', '', 15);
                $html='CUSTOMER RECEIVABLE SUMMARY AS OF '.date('d-m-Y', strtotime($request->toDate));
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

                //$pdf::Cell(95,5,$title,'',0,'L');
                ///$pdf::Cell(95,5,$time,'',0,'R');
                //$pdf::Ln(6);

                $pdf::SetFont('helvetica', '', 12);
                $html='Date : '.date('d-m-Y h:i:s A');
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

                $html='Balance';
                $pdf::Cell(95,5,$html,'',0,'L');
                $pdf::Ln(6);

                $pdf::SetFont('helvetica', 'B', 14);
                $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="20">SN</th>
                <th align="center" width="190">Customer Name</th>
                <th align="center" width="187">Cell</th>
                <th align="right" width="60">Balance</th>
                <th align="right" width="60">Limit</th>
            </tr>';
                $pdf::SetFont('helvetica', '', 10);
                $total_balance=0.0;
                $total_advances=0.0;

                $pos_arr=array(); $neg_arr=array();
                foreach($row as $key=>$value)
                {
                    ($value['Differentiate']<0) ?  $neg_arr[]=$value : $pos_arr[]=$value;
                }
                for($i=0;$i<count($pos_arr);$i++)
                {
                    $color='green';
                    if($pos_arr[$i]['Differentiate']>$pos_arr[$i]['limit'])
                    {
                        $color='red';
                    }
                    $total_balance+=$pos_arr[$i]['Differentiate'];
                    $html .='<tr>
                <td align="center" width="20">'.($i+1).'</td>
                <td align="left" width="190">'.($pos_arr[$i]['Name']).'</td>
                <td align="left" width="187">'.($pos_arr[$i]['Mobile']).'</td>
                <td align="right" width="60" style="color:'.$color.';">'.(number_format($pos_arr[$i]['Differentiate'],2,'.',',')).'</td>
                <td align="right" width="60">'.(number_format($pos_arr[$i]['limit'],2,'.',',')).'</td>
                </tr>';
                }
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
                $html = '<table border="0" cellpadding="1"><tr color="red" style="font-weight: bold;font-size: 12px;">
                         <td width="450" align="right" colspan="3">Balance Total : </td>
                         <td width="80" align="right">'. number_format($total_balance, 2, '.', ',') .'</td>
                     </tr>';
                $html .= '</table>';
                $pdf::writeHTML($html, true, false, false, false, '');

                $html='Advances';
                $pdf::Cell(95,5,$html,'',0,'L');
                $pdf::Ln(6);

                $pdf::SetFont('helvetica', 'B', 14);
                $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="20">SN</th>
                <th align="center" width="190">Customer Name</th>
                <th align="center" width="187">Cell</th>
                <th align="right" width="60">Balance</th>
                <th align="right" width="60">Limit</th>
            </tr>';
                $pdf::SetFont('helvetica', '', 10);
                for($i=0;$i<count($neg_arr);$i++)
                {
                    $color='green';
                    if($neg_arr[$i]['Differentiate']>$neg_arr[$i]['limit'])
                    {
                        $color='red';
                    }
                    $total_advances+=$neg_arr[$i]['Differentiate'];
                    $html .='<tr>
                <td align="center" width="20">'.($i+1).'</td>
                <td align="left" width="190">'.($neg_arr[$i]['Name']).'</td>
                <td align="left" width="187">'.($neg_arr[$i]['Mobile']).'</td>
                <td align="right" width="60" style="color:'.$color.';">'.(number_format($neg_arr[$i]['Differentiate'],2,'.',',')).'</td>
                <td align="right" width="60">'.(number_format($neg_arr[$i]['limit'],2,'.',',')).'</td>
                </tr>';
                }
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
                $html = '<table border="0" cellpadding="1"><tr color="red" style="font-weight: bold;font-size: 12px;">
                         <td width="450" align="right" colspan="3">Advance Total : </td>
                         <td width="80" align="right">'. number_format($total_advances, 2, '.', ',') .'</td>
                     </tr>';
                $html .= '</table>';
                $pdf::writeHTML($html, true, false, false, false, '');

                $pdf::SetFont('helvetica', 'B', 13);
                $html='<table border="0" cellpadding="0">';
                $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">NET TOTAL : </td>
                     <td width="80" align="right">'.number_format($total_balance-abs($total_advances),2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');

                $pdf::lastPage();
                $time=time();
                $fileLocation = storage_path().'/app/public/report_files/';
                $fileNL = $fileLocation.'//'.$time.'.pdf';
                $pdf::Output($fileNL, 'F');
                $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
                $url=array('url'=>$url);
                return $url;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }
    }

    public function PrintSupplierStatementForDate(Request $request)
    {
        if($request->toDate!='')
        {
            $result_array=array();
            $suppliers=Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
            foreach ($suppliers as $supplier)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->where('createdDate','<=',$request->toDate)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->where('createdDate','<=',$request->toDate)->whereNull('updateDescription')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Name'=>$supplier->Name,'Mobile'=>$supplier->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);

            //$data=PurchaseResource::collection(Purchase::get()->where('remainingBalance','!=',0));
            if(!empty($row))
            {
                $footer=new CustomeFooter;
                $footer->footer();
                $pdf = new PDF();
                $pdf::setPrintHeader(false);

                $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf::SetAutoPageBreak(TRUE, 14);

                $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
                $pdf::SetFillColor(255,255,0);

                $pdf::SetFont('helvetica', '', 15);
                $html='SUPPLIER PAYABLE SUMMARY AS OF '.date('d-m-Y', strtotime($request->toDate));
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

                $pdf::SetFont('helvetica', '', 12);
                $html='Date : '.date('d-m-Y h:i:s A');
                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

                $pdf::SetFont('helvetica', 'B', 14);
                $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="50">S.No</th>
                <th align="center" width="300">Account</th>
                <th align="center" width="100">Cell</th>
                <th align="right" width="80">Balance</th>
            </tr>';
                $pdf::SetFont('helvetica', '', 10);
                $total_balance=0.0;
                $count=0;
                for($i=0;$i<count($row);$i++)
                {
                    if($row[$i]['Differentiate']!=0)
                    {
                        $total_balance+=$row[$i]['Differentiate'];
                        $html .='<tr>
                    <td align="center" width="50">'.(++$count).'</td>
                    <td align="left" width="300">'.($row[$i]['Name']).'</td>
                    <td align="center" width="100">'.($row[$i]['Mobile']).'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Differentiate'],2,'.',',')).'</td>
                    </tr>';
                    }
                }
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');

                $pdf::SetFont('helvetica', 'B', 13);
                $html='<table border="0" cellpadding="0">';
                $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">Total Balance : </td>
                     <td width="80" align="right">'.number_format($total_balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');

                $pdf::lastPage();
                $time=time();
                $fileLocation = storage_path().'/app/public/report_files/';
                $fileNL = $fileLocation.'//'.$time.'.pdf';
                $pdf::Output($fileNL, 'F');
                //$url=url('/').'/storage/report_files/'.$time.'.pdf';
                $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
                //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
                $url=array('url'=>$url);
                return $url;
            }
            else
            {
                return FALSE;
            }
        }
    }

    function array_sort($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        return $new_array;
    }

    public function PrintSupplierStatement()
    {
//        $row = DB::table('purchases as p')->select('p.supplier_id', DB::raw('SUM(p.remainingBalance) as PurchaseAmount'),'s.Name','s.Mobile')
//            ->groupBy('supplier_id')
//            ->orderBy('PurchaseAmount','desc')
//            ->leftjoin('suppliers as s', 's.id', '=', 'p.supplier_id')
//            ->get();
//        $row=json_decode(json_encode($row), true);

        // getting latest closing for all suppliers from account transaction table
        /*$row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.supplier_id','ac.company_id','ac.Differentiate','s.Name','s.Mobile')
            ->where('ac.supplier_id','!=',0)
            ->where('ac.company_id',session('company_id'))
            ->groupBy('ac.supplier_id')
            ->orderBy('ac.id','asc')
            ->leftjoin('suppliers as s', 's.id', '=', 'ac.supplier_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.supplier_id','ac.Differentiate','s.Name','s.Mobile')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->leftjoin('suppliers as s', 's.id', '=', 'ac.supplier_id')
            ->get();
        $row=json_decode(json_encode($row), true);*/
        //echo "<pre>";print_r($row);die;

        $result_array=array();
        $suppliers=Supplier::where('company_id',session('company_id'))->where('company_type_id',2)->get();
        foreach ($suppliers as $supplier)
        {
            //get diff of total debit and credit column
            $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Credit');
            $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Debit');
            $diff=$credit_sum-$debit_sum;
            $temp=array('Name'=>$supplier->Name,'Mobile'=>$supplier->Mobile,'Differentiate'=>$diff);
            $result_array[]=$temp;
            unset($temp);
        }
        $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
        $row=array_values($row);

        //$data=PurchaseResource::collection(Purchase::get()->where('remainingBalance','!=',0));
        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            //$row=$sales->sale_details;
            //$row=json_decode(json_encode($data), true);
            //echo "<pre>123";print_r($row);die;

            $pdf::SetFont('helvetica', '', 15);
            $html='SUPPLIER PAYABLE SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s A');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="50">S.No</th>
                <th align="center" width="300">Account</th>
                <th align="center" width="100">Cell</th>
                <th align="right" width="80">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            $count=0;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Differentiate']!=0)
                {
                    $total_balance+=$row[$i]['Differentiate'];
                    $html .='<tr>
                    <td align="center" width="50">'.(++$count).'</td>
                    <td align="left" width="300">'.($row[$i]['Name']).'</td>
                    <td align="center" width="100">'.($row[$i]['Mobile']).'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Differentiate'],2,'.',',')).'</td>
                    </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0" cellpadding="0">';
            $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">Total Balance : </td>
                     <td width="80" align="right">'.number_format($total_balance,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

//            $data=SupplierAdvanceResource::collection(SupplierAdvance::get()->where('Amount','!=',0)->where('isPushed','=',1));
//            if($data)
//            {
//                $pdf::SetFont('helvetica', '', 15);
//                $html='SUPPLIER ADVANCES';
//                $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);
//
//                $row=json_decode(json_encode($data), true);
//                //echo "<pre>";print_r($row);die;
//                $pdf::SetFont('helvetica', '', 10);
//                $html = '<table border="0.5" cellpadding="2">
//                <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
//                    <th align="center" width="50">S.No</th>
//                    <th align="center" width="300">Account</th>
//                    <th align="center" width="100">Cell</th>
//                    <th align="right" width="80">Balance</th>
//                </tr>';
//
//                $total_advances=0.0;
//                for($j=0;$j<count($row);$j++)
//                {
//                    $total_advances+=$row[$j]['Amount'];
//                    $html .='<tr>
//                    <td align="center" width="50">'.($j+1).'</td>
//                    <td align="left" width="300">'.($row[$j]['api_supplier']['Name']).'</td>
//                    <td align="center" width="100">'.($row[$j]['api_supplier']['Mobile']).'</td>
//                    <td align="right" width="80">'.(number_format($row[$j]['Amount'],2,'.',',')).'</td>
//                    </tr>';
//                }
//                $html.='</table>';
//                $pdf::writeHTML($html, true, false, false, false, '');
//
//                $pdf::SetFont('helvetica', 'B', 13);
//                $html='<table border="0" cellpadding="0">';
//                $html.= '
//                 <tr color="red">
//                     <td width="450" align="right" colspan="3">Total Advances : </td>
//                     <td width="80" align="right">'.number_format($total_advances,2,'.',',').'</td>
//                 </tr>';
//                $html.='</table>';
//                $pdf::writeHTML($html, true, false, false, false, '');
//            }
//
//            $pdf::SetFont('helvetica', '', 12);
//            $html='Outstanding Total : '.number_format($total_balance,2,'.',',');
//            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);
//
//            $html='Advances Total : '.number_format($total_advances,2,'.',',');
//            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);
//
//            $html='Differance Total : '.number_format($total_balance-$total_advances,2,'.',',');
//            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    /*public function PrintDetailSupplierStatement(Request $request)
    {
        //get daily sum of grandTotal from purchases for the given supplier from date to date
        $supplier_id=$request->supplier_id;
        $fromDate=$request->fromDate;
        $toDate=$request->toDate;
        //purchase entries
        $row = Purchase::select('PurchaseDate as Date', DB::raw('SUM(grandTotal) as PurchaseAmount'))
            ->where('supplier_id','=',$supplier_id)
            ->whereBetween('PurchaseDate',[$fromDate,$toDate])
            ->groupBy('PurchaseDate')
            ->get();
        $row=json_decode(json_encode($row), true);

        //supplier payment entries
        $row1 = SupplierPayment::select('transferDate as Date','paidAmount','referenceNumber','Description')
            ->where('supplier_id','=',$supplier_id)
            //->where('isPushed','=',1)
            ->whereBetween('transferDate',[$fromDate,$toDate])
            ->get();
        $row1=json_decode(json_encode($row1), true);
        $combined=array_merge($row,$row1);

        $ord = array();
        foreach ($combined as $key => $value){
            $ord[] = strtotime($value['Date']);
        }
        array_multisort($ord, SORT_ASC, $combined);
        //echo "<pre>123";print_r($combined);die;
        $row=$combined;

        if(!empty($row))
        {
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Supplier Name : '.$request->supplier_name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="60">Date</th>
                <th align="center" width="70">Ref#</th>
                <th align="center" width="200">Description</th>
                <th align="center" width="70">Debit</th>
                <th align="center" width="70">Credit</th>
                <th align="right" width="80">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $sum_of_credit=0.0;
            $sum_of_debit=0.0;
            $sum_of_differance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if(array_key_exists('PurchaseAmount',$row[$i]))
                {
                    //debit part = purchase entry
                    $sum_of_debit+=$row[$i]['PurchaseAmount'];
                    $sum_of_differance=$sum_of_differance+$row[$i]['PurchaseAmount'];
                    $html .='<tr>
                        <td align="center" width="60">'.($row[$i]['Date']).'</td>
                        <td align="left" width="70"></td>
                        <td align="left" width="200"></td>
                        <td align="right" width="70">'.(number_format($row[$i]['PurchaseAmount'],2,'.',',')).'</td>
                        <td align="right" width="70">'.(number_format(0.00,2,'.',',')).'</td>
                        <td align="right" width="80">'.(number_format($sum_of_differance,2,'.',',')).'</td>
                        </tr>';
                }
                elseif(array_key_exists('paidAmount',$row[$i]))
                {
                    //credit part = supplier payment entry
                    $sum_of_credit+=$row[$i]['paidAmount'];
                    $sum_of_differance=$sum_of_differance-$row[$i]['paidAmount'];
                    $html .='<tr>
                        <td align="center" width="60">'.($row[$i]['Date']).'</td>
                        <td align="center" width="70">'.($row[$i]['referenceNumber']).'</td>
                        <td align="left" width="200">'.($row[$i]['Description']).'</td>
                        <td align="right" width="70">'.(number_format(0.00,2,'.',',')).'</td>
                        <td align="right" width="70">'.(number_format($row[$i]['paidAmount'],2,'.',',')).'</td>
                        <td align="right" width="80">'.(number_format($sum_of_differance,2,'.',',')).'</td>
                        </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($sum_of_differance<0)
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="330" align="right" colspan="3">Total : </td>
                     <td width="70" align="right">'.number_format($sum_of_debit,2,'.',',').'</td>
                     <td width="70" align="right">'.number_format($sum_of_credit,2,'.',',').'</td>
                     <td width="80" align="right" color="red">'.number_format($sum_of_differance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="330" align="right" colspan="3">Total : </td>
                     <td width="70" align="right">'.number_format($sum_of_debit,2,'.',',').'</td>
                     <td width="70" align="right">'.number_format($sum_of_credit,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($sum_of_differance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }


            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }*/

    public function PrintDetailSupplierStatement(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $account_transactions = AccountTransaction::orderBy('createdDate','asc')->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('supplier_id','=',$request->supplier_id)->whereNull('updateDescription')->orderBy('createdDate','desc')->orderBy('id')->get();
            $sum_of_debit_before_from_date=AccountTransaction::where('supplier_id',$request->supplier_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=AccountTransaction::where('supplier_id',$request->supplier_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_credit_before_from_date-$sum_of_debit_before_from_date;
        }
        else
        {
            return FALSE;
        }
        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);

        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Supplier Name : '.$request->supplier_name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=' Opening Balance '.round($closing_amount,2);
            $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="60">Date</th>
                <th align="center" width="70">Ref#</th>
                <th align="center" width="150">Description</th>
                <th align="center" width="90">Debit</th>
                <th align="center" width="90">Credit</th>
                <th align="right" width="90">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $credit_total=0.0;
            $debit_total=0.0;
            $balance=$closing_amount;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Debit']!=0)
                {
                    $debit_total += $row[$i]['Debit'];
                    $balance = $balance - $row[$i]['Debit'];
                }
                elseif($row[$i]['Credit']!=0)
                {
                    $credit_total += $row[$i]['Credit'];
                    $balance = $balance + $row[$i]['Credit'];
                }
                else
                {
                    $balance += $row[$i]['Differentiate'];
                }
                if($i%2==0)
                {
                    $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                        <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                        <td align="left" width="150">'.$row[$i]['TransactionDesc'].'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($balance,2,'.',',')).'</td>
                        </tr>';
                }
                else
                {
                    $html .='<tr>
                        <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                        <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                        <td align="left" width="150">'.$row[$i]['TransactionDesc'].'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($balance,2,'.',',')).'</td>
                        </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($balance<0)
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="280" align="right" colspan="3">Total : </td>
                     <td width="90" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="90" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="280" align="right" colspan="3">Total : </td>
                     <td width="90" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function ViewDetailSupplierStatement(Request $request)
    {
        //get daily sum of grandTotal from purchases for the given supplier from date to date
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $account_transactions=AccountTransaction::get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('supplier_id','=',$request->supplier_id)->where('updateDescription','!=','hide');
        }
        else
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);
        if(empty($row))
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        else
        {
            $title='Supplier Name :-'.$request->supplier_name.' | FROM '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $html = '<table class="display" id="report_table"><thead>
            <tr>
                <th align="center">Date</th>
                <th align="center">Ref#</th>
                <th align="center">Description</th>
                <th align="center">Debit</th>
                <th align="center">Credit</th>
                <th align="right">Closing</th>
            </tr></thead><tbody>';

            $sum_of_credit=0.0;
            $sum_of_debit=0.0;
            $closing_amount=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($i==0)
                {
                    $closing_amount=$closing_amount+$row[$i]['Differentiate'];
                }
                else
                {
                    if($row[$i]['Debit']==0)
                    {
                        $closing_amount+=$row[$i]['Credit'];
                    }
                    else
                    {
                        $closing_amount-=$row[$i]['Debit'];
                    }
                }
                $sum_of_debit+=$row[$i]['Debit'];
                $sum_of_credit+=$row[$i]['Credit'];
                $html .='<tr>
                    <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left" width="170">'.$row[$i]['Description'].'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="90">'.(number_format($closing_amount,2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</tbody></table>';
            return view('admin.report.html_viewer',compact('html','title'))->render();
        }
    }

    public function PrintDailyCustomerStatement(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $sum_of_debit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
        }
        else
        {
            return FALSE;
        }
        $footer=new CustomeFooter;
        $footer->footer();
        $pdf = new PDF();
        $pdf::setPrintHeader(false);

        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, 14);

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);

        $pdf::SetFont('helvetica', '', 15);
        $html='Customer Name : '.$request->customer_name;
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $date=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
        $html='Opening Balance : '.round($closing_amount,2);

        $pdf::Cell(95,5,$date,'',0,'L');
        $pdf::SetFont('times', 'B', 12);
        $pdf::Cell(95,5,$html,'',0,'R');
        $pdf::Ln(6);

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
        <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
            <th align="center" width="60">Date</th>
            <th align="center" width="250">Ref#</th>
            <th align="center" width="80">Debit</th>
            <th align="center" width="80">Credit</th>
            <th align="right" width="80">Closing</th>
        </tr>';
        $pdf::SetFont('helvetica', '', 10);
        $credit_total=0.0;
        $debit_total=0.0;
        $balance=$closing_amount;

        $begin = new DateTime($request->fromDate);
        $end   = new DateTime($request->toDate);
        $final_array=array();
        for($i = $begin; $i <= $end; $i->modify('+1 day'))
        {
            $date=$i->format("Y-m-d");

//            $today_debit_sum=AccountTransaction::where('customer_id','=',$request->customer_id)->where('createdDate',$date)->whereNull('updateDescription')->sum('Debit');
//            $today_credit_sum=AccountTransaction::where('customer_id','=',$request->customer_id)->where('createdDate',$date)->whereNull('updateDescription')->sum('Credit');

            $account_transactions=AccountTransaction::where('customer_id','=',$request->customer_id)->whereBetween('createdDate', [$date, $date])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
            $row=json_decode(json_encode($account_transactions), true);
            $row=array_values($row);
            $cur_date_debits_pads=array();
            $cur_date_credits_pads=array();
            $cur_date_debits=array();
            $cur_date_credits=array();
            for($j=0;$j<count($row);$j++)
            {
                if($row[$j]['Debit']!=0)
                {

                    $cur_date_debits[]=$row[$j]['Debit'];
                    $cur_date_debits_pads[]=$row[$j]['referenceNumber'];
                }
                if($row[$j]['Credit']!=0)
                {
                    $cur_date_credits[]=$row[$j]['Credit'];
                    $cur_date_credits_pads[]=$row[$j]['referenceNumber'];
                }
            }
            if(isset($cur_date_debits) && !empty($cur_date_debits))
            {
                $today_debit_sum=array_sum($cur_date_debits);
                $description=implode('/',$cur_date_debits_pads);
                $description=str_replace('P#','',$description);
                $tmp_array=array('Date'=>$date,'Description'=>$description,'Debit'=>$today_debit_sum,'Credit'=>0);
                $final_array[]=$tmp_array;
                unset($description);
                unset($cur_date_debits_pads);
                unset($cur_date_debits);
            }
            if(isset($cur_date_credits) && !empty($cur_date_credits))
            {
                $today_credit_sum=array_sum($cur_date_credits);
                $description=implode('-',$cur_date_credits_pads);
                $tmp_array=array('Date'=>$date,'Description'=>$description,'Debit'=>0,'Credit'=>$today_credit_sum);
                $final_array[]=$tmp_array;
                unset($description);
                unset($cur_date_credits_pads);
                unset($cur_date_credits);
            }
//            if($today_debit_sum!=0)
//            {
//                $tmp_array=array('Date'=>$date,'Description'=>'sales','Debit'=>$today_debit_sum,'Credit'=>0);
//                $final_array[]=$tmp_array;
//            }
//            if($today_credit_sum!=0)
//            {
//                $tmp_array=array('Date'=>$date,'Description'=>'payments','Debit'=>0,'Credit'=>$today_credit_sum);
//                $final_array[]=$tmp_array;
//            }
        }
        //echo "<pre>";print_r($final_array);die;

        for($i=0;$i<count($final_array);$i++)
        {
            if($final_array[$i]['Debit']!=0)
            {
                $debit_total += $final_array[$i]['Debit'];
                $balance = $balance + $final_array[$i]['Debit'];
            }
            elseif($final_array[$i]['Credit']!=0)
            {
                $credit_total += $final_array[$i]['Credit'];
                $balance = $balance - $final_array[$i]['Credit'];
            }
            else
            {
                $balance += $final_array[$i]['Differentiate'];
            }

            $html .='<tr>
                <td align="center" width="60">'.(date('d-m-Y', strtotime($final_array[$i]['Date']))).'</td>
                <td align="left" width="250">'.$final_array[$i]['Description'].'</td>
                <td align="right" width="80">'.(number_format($final_array[$i]['Debit'],2,'.',',')).'</td>
                <td align="right" width="80">'.(number_format($final_array[$i]['Credit'],2,'.',',')).'</td>
                <td align="right" width="80">'.(number_format($balance,2,'.',',')).'</td>
                </tr>';
        }
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::SetFont('helvetica', 'B', 13);
        if($balance<0)
        {
            $html='<table border="0.5" cellpadding="1">';
            $html.= '
             <tr>
                 <td width="310" align="right" colspan="3">Total : </td>
                 <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="80" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
             </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
        }
        else
        {
            $html='<table border="0.5" cellpadding="1">';
            $html.= '
             <tr>
                 <td width="310" align="right" colspan="3">Total : </td>
                 <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                 <td width="80" align="right">'.number_format($balance,2,'.',',').'</td>
             </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
        }


        $pdf::lastPage();
        $time=time();
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$time.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }

    public function PrintDetailCustomerStatement(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $account_transactions=AccountTransaction::where('customer_id','=',$request->customer_id)->whereBetween('createdDate', [$request->fromDate, $request->toDate])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
            $sum_of_debit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
        }
        else
        {
            return FALSE;
        }
        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);

        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Customer Name : '.$request->customer_name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=' Opening Balance  '.round($closing_amount,2);
            $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="60">Date</th>
                <th align="center" width="70">Ref#</th>
                <th align="center" width="180">Description</th>
                <th align="center" width="80">Debit</th>
                <th align="center" width="80">Credit</th>
                <th align="right" width="80">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $credit_total=0.0;
            $debit_total=0.0;
            $balance=$closing_amount;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Debit']!=0)
                {
                    $debit_total += $row[$i]['Debit'];
                    $balance = $balance + $row[$i]['Debit'];
                }
                elseif($row[$i]['Credit']!=0)
                {
                    $credit_total += $row[$i]['Credit'];
                    $balance = $balance - $row[$i]['Credit'];
                }
                else
                {
                    $balance += $row[$i]['Differentiate'];
                }

                $html .='<tr>
                    <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left" width="180">'.$row[$i]['TransactionDesc'].'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="80">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="80">'.(number_format($balance,2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($balance<0)
            {
                $html='<table border="0.5" cellpadding="1">';
                $html.= '
                 <tr>
                     <td width="310" align="right" colspan="3">Total : </td>
                     <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="80" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="1">';
                $html.= '
                 <tr>
                     <td width="310" align="right" colspan="3">Total : </td>
                     <td width="80" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="80" align="right">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function ViewDetailCustomerStatement(Request $request)
    {
        //get daily sum of grandTotal from sales for the given customer from date to date
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $account_transactions=AccountTransaction::oldest('createdDate')->get()->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('customer_id','=',$request->customer_id)->where('updateDescription','!=','hide');
        }
        else
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);
        if(empty($row))
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        else
        {
            $title='Customer Name :-'.$request->customer_name.' | FROM '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));

            $html = '<table class="display" id="report_table"><thead>
            <tr>
                <th align="center">Date</th>
                <th align="center">Ref#</th>
                <th align="center">Description</th>
                <th align="center">Debit</th>
                <th align="center">Credit</th>
                <th align="right">Closing</th>
            </tr></thead><tbody>';

            $sum_of_credit=0.0;
            $sum_of_debit=0.0;
            $closing_amount=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($i==0)
                {
                    $closing_amount=$closing_amount+$row[$i]['Differentiate'];
                }
                else
                {
                    if($row[$i]['Debit']==0)
                    {
                        $closing_amount-=$row[$i]['Credit'];
                    }
                    else
                    {
                        $closing_amount+=$row[$i]['Debit'];
                    }
                }
                $sum_of_debit+=$row[$i]['Debit'];
                $sum_of_credit+=$row[$i]['Credit'];

                $html .='<tr>
                    <td align="center">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left">'.$row[$i]['referenceNumber'].'</td>
                    <td align="left">'.$row[$i]['Description'].'</td>
                    <td align="right">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right">'.(number_format($closing_amount,2,'.',',')).'</td>
                    </tr>';
            }
            $html.='</tbody></table>';
            return view('admin.report.html_viewer',compact('html','title'))->render();
        }
    }

    public function PrintPaidAdvancesSummary()
    {
        // getting latest closing for all suppliers from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.supplier_id','ac.Differentiate','s.Name','s.Mobile')
            ->where('ac.supplier_id','!=',0)
            ->groupBy('ac.supplier_id')
            ->orderBy('ac.id','asc')
            ->leftjoin('suppliers as s', 's.id', '=', 'ac.supplier_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.supplier_id','ac.Differentiate','s.Name','s.Mobile')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->leftjoin('suppliers as s', 's.id', '=', 'ac.supplier_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        //echo "<pre>";print_r($row);die;

        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='SUPPLIER ADVANCE SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="50">S.No</th>
                <th align="center" width="300">Account</th>
                <th align="center" width="100">Cell</th>
                <th align="right" width="80">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Differentiate']<0)
                {
                    $total_balance+=$row[$i]['Differentiate'];
                    $html .='<tr>
                    <td align="center" width="50">'.($i+1).'</td>
                    <td align="left" width="300">'.($row[$i]['Name']).'</td>
                    <td align="center" width="100">'.($row[$i]['Mobile']).'</td>
                    <td align="right" width="80">'.(number_format(abs($row[$i]['Differentiate']),2,'.',',')).'</td>
                    </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0" cellpadding="0">';
            $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">Total Balance : </td>
                     <td width="80" align="right">'.number_format(abs($total_balance),2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function PrintReceivedAdvancesSummary()
    {
        // getting latest closing for all customers from account transaction table
        $row = DB::table('account_transactions as ac')->select( DB::raw('MAX(ac.id) as max_id'),'ac.customer_id','ac.Differentiate','s.Name','s.Mobile')
            ->where('ac.customer_id','!=',0)
            ->groupBy('ac.customer_id')
            ->orderBy('ac.id','asc')
            ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        $needed_ids=array_column($row,'max_id');

        $row = DB::table('account_transactions as ac')->select( 'ac.id','ac.customer_id','ac.Differentiate','s.Name','s.Mobile')
            ->whereIn('ac.id',$needed_ids)
            ->orderBy('ac.id','asc')
            ->leftjoin('customers as s', 's.id', '=', 'ac.customer_id')
            ->get();
        $row=json_decode(json_encode($row), true);
        //echo "<pre>";print_r($row);die;

        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='CUSTOMER ADVANCE SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="50">S.No</th>
                <th align="center" width="300">Account</th>
                <th align="center" width="100">Cell</th>
                <th align="right" width="80">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Differentiate']<0)
                {
                    $total_balance+=$row[$i]['Differentiate'];
                    $html .='<tr>
                    <td align="center" width="50">'.($i+1).'</td>
                    <td align="left" width="300">'.($row[$i]['Name']).'</td>
                    <td align="center" width="100">'.($row[$i]['Mobile']).'</td>
                    <td align="right" width="80">'.(number_format(abs($row[$i]['Differentiate']),2,'.',',')).'</td>
                    </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0" cellpadding="0">';
            $html.= '
                 <tr color="red">
                     <td width="450" align="right" colspan="3">Total Balance : </td>
                     <td width="80" align="right">'.number_format(abs($total_balance),2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            //$url=url('/').'/storage/report_files/'.$time.'.pdf';
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            //$url=storage_path().'/purchase_order_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function PrintProfit_loss(Request $request)
    {
        if($request->month!='')
        {
            if(session('company_id')==4 OR session('company_id')==5 OR session('company_id')==8)
            {
                return $this->PrintProfit_lossService($request);
            }

            $current_month=date('m');
            $request_month=explode('-',$request->month);
            if($request_month[1]!=$current_month)
            {
                return $this->print_profit_and_loss_for_non_current_month($request);
            }

            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));
            $company_id = session('company_id');

            // start getting total sales amount with vat
            $total_sales=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_purchase=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_expense=Expense::where('expenseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('expenseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('grandTotal');

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $company_name=Company::where('id',$company_id)->first();

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 18);
            $html=$company_name->Name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFillColor(4,92,32);
            $title='PROFIT AND LOSS REPORT '.date('M Y', strtotime($request->month));
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);
            $pdf::SetFillColor(255,255,0);
            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));

            $total_purchase_qty_of_month=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_output_vat=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');
            //total sales quantity
            $total_sales_qty_of_month=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_input_vat=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');

            //find the previous month stock value
            //1. find previous month average purchase price
            $month=explode('-',$request->month);
            $prev_month=str_pad($month[1]-1, 2, '0', STR_PAD_LEFT);;
            $dt = $month[0].'-'.$prev_month.'-01';
            $_prev_start_date = date("Y-m-01", strtotime($dt));
            $_prev_end_date = date("Y-m-t", strtotime($dt));
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$_prev_start_date, $_prev_end_date])->sum('Quantity');
            $total_purchase_amount=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$_prev_start_date, $_prev_end_date])->sum('rowSubTotal');
            $prev_average_price=round($total_purchase_amount/$total_purchase_qty,2);
            //2.find previous month stock
            $prev_total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('createdDate','<',$start_date)->where('deleted_at','=',NULL)->sum('Quantity');
            //total sales quantity
            $prev_total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','<',$start_date)->where('deleted_at','=',NULL)->sum('Quantity');
            $prev_stock_qty=$prev_total_purchase_qty-$prev_total_sales_qty;
            //echo "<pre>";print_r($prev_stock_qty);die;
            if($prev_stock_qty==0)
            {
                $prev_total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('createdDate','<',$start_date)->where('deleted_at','=',NULL)->where('rem_stock_entry','!=',1)->sum('Quantity');
                //total sales quantity
                $prev_total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','<',$start_date)->where('deleted_at','=',NULL)->where('rem_stock_entry','!=',1)->sum('Quantity');
                $prev_stock_qty=$prev_total_purchase_qty-$prev_total_sales_qty;
            }
            //echo "<pre>";print_r($prev_stock_qty);die;

            //$prev_total_sales_qty=PurchaseDetail::where('company_id','=',session('company_id'))->whereBetween('createdDate', [$start_date, $end_date])->where('deleted_at','=',NULL)->where('rem_stock_entry',1)->first();


            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->where('createdDate','<=',$end_date)->sum('Quantity');
            //total sales quantity
            $total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->where('createdDate','<=',$end_date)->sum('Quantity');
            $stock_qty=$total_purchase_qty-$total_sales_qty;
            $payable_stock_value=($stock_qty)*$request->currentRate;

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color:#0e4714">
                     <td width="350" align="right" colspan="3">Total Sales <br> (QTY :'.number_format($total_sales_qty_of_month,2,'.',',').') <br> ( OUTPUT VAT AMOUNT : '.number_format($total_input_vat,2,'.',',').')</td>
                     <td width="150" align="right">'.number_format($total_sales,2,'.',',').'</td>
                    </tr>';
            if($prev_stock_qty>1)
            {
                $html.= '<tr>
                     <td width="350" align="right" colspan="3">Opening Stock Value (only showment-already in purchase) <br>'.number_format($prev_stock_qty,2,'.',',').'@'.$prev_average_price.'</td>
                     <td width="150" align="right">'.number_format(($prev_stock_qty*$prev_average_price),2,'.',',').'</td>
                    </tr>';
            }

            $html.= '<tr style="color:#a83232">
                     <td width="350" align="right" colspan="3">Total Purchase (-) <br> (QTY :'.number_format($total_purchase_qty_of_month,2,'.',',').') <br> ( INPUT VAT AMOUNT : '.number_format($total_output_vat,2,'.',',').')</td>
                     <td width="150" align="right">'.number_format($total_purchase,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#0e4714">
                     <td width="350" align="right" colspan="3">Closing Stock Value (-) <br>'.number_format($stock_qty,2,'.',',').'@'.$request->currentRate.'</td>
                     <td width="150" align="right">'.number_format($payable_stock_value,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#a83232">
                     <td width="350" align="right" colspan="3">Total Expenses (-) </td>
                     <td width="150" align="right">'.number_format($total_expense,2,'.',',').'</td>
                    </tr>';
            if($prev_stock_qty<1)
            {
                $html.= '<tr style="color:#0e4714">
                     <td width="350" align="right" colspan="3">Previous Negative Stock Value (+) <br>'.number_format($prev_stock_qty,2,'.',',').'@'.$prev_average_price.'</td>
                     <td width="150" align="right">'.number_format(($prev_stock_qty*$prev_average_price),2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#0e4714">
                     <td width="350" align="right" colspan="3">Net Income </td>
                     <td width="150" align="right">'.number_format((($total_sales-$total_purchase+$payable_stock_value-$total_expense)+(abs($prev_stock_qty*$prev_average_price))),2,'.',',').'</td>
                    </tr>';
            }
            else
            {
                $html.= '<tr style="color:#0e4714">
                     <td width="350" align="right" colspan="3">Net Income </td>
                     <td width="150" align="right">'.number_format((($total_sales-$total_purchase+$payable_stock_value-$total_expense)),2,'.',',').'</td>
                    </tr>';
            }
//            $html.= '<tr style="color:#0e4714">
//                     <td width="300" align="right" colspan="3">Net Income </td>
//                     <td width="200" align="right">'.number_format((($total_sales-$total_purchase-$total_expense)-($prev_stock_qty*$prev_average_price)),2,'.',',').'</td>
//                    </tr>';
            //$pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            //echo "<pre>";print_r($html);die;
            $pdf::writeHTML($html, true, false, false, false, '');

            //////////other stock information////////////////////////
            $title='OTHER INFORMATION ';
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            //$pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);
            $pdf::SetFillColor(255,255,0);

            $in_sum=OtherStock::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('in');
            $out_sum=OtherStock::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('out');
            $other_stock=$in_sum-$out_sum;

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color:#0e4714">
                     <td width="350" align="right" colspan="3">Other Stock </td>
                     <td width="150" align="right">'.number_format($other_stock,2,'.',',').'</td>
                    </tr>';
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            //////////other stock information////////////////////////

            $pdf::lastPage();
            $time='p_and_l_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function print_profit_and_loss_for_non_current_month($request)
    {
        if($request->month!='')
        {
            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));
            $company_id = session('company_id');

            // start getting total sales amount with vat
            $total_sales=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_purchase=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_expense=Expense::where('expenseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('expenseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('grandTotal');

            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));

            $total_purchase_qty_of_month=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_output_vat=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');
            //total sales quantity
            $total_sales_qty_of_month=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_input_vat=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');

            //find the previous month stock value
            //1. find previous month average purchase price
            $month=explode('-',$request->month);
            $prev_month=str_pad($month[1]-1, 2, '0', STR_PAD_LEFT);;
            $dt = $month[0].'-'.$prev_month.'-01';
            $_prev_start_date = date("Y-m-01", strtotime($dt));
            $_prev_end_date = date("Y-m-t", strtotime($dt));
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$_prev_start_date, $_prev_end_date])->sum('Quantity');
            $total_purchase_amount=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$_prev_start_date, $_prev_end_date])->sum('rowSubTotal');
            $prev_average_price=round($total_purchase_amount/$total_purchase_qty,2);
            //2.find previous month stock
            $prev_total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('createdDate','<',$start_date)->where('deleted_at','=',NULL)->sum('Quantity');
            //total sales quantity
            //$prev_total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->whereBetween('createdDate',[$start_date, $end_date])->where('deleted_at','=',NULL)->where('rem_stock_entry',1)->first();

            $prev_total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','<',$start_date)->where('deleted_at','=',NULL)->sum('Quantity');

            $prev_month_stock=$prev_total_purchase_qty-$prev_total_sales_qty;

            if($prev_month_stock<-1)
            {
                return $this->Print_profit_loss_for_non_current_month_for_negative_beg_inventory($request);
            }

            $prev_total_sales_qty=PurchaseDetail::where('company_id','=',session('company_id'))->whereBetween('createdDate', [$start_date, $end_date])->where('deleted_at','=',NULL)->where('rem_stock_entry',1)->first();
            if($prev_total_sales_qty)
            {
                $prev_stock_qty=$prev_total_sales_qty->Quantity;
            }
            else
            {
                $prev_stock_qty=0;
            }

            //$total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->where('createdDate','<=',$end_date)->where('rem_stock_entry','!=',1)->sum('Quantity');
            //total sales quantity
            $total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate',[$start_date, $end_date])->where('rem_stock_entry',1)->first();
            $negative_closing_stock=false;
            if($total_sales_qty)
            {
                $stock_qty=$total_sales_qty->Quantity;
                $payable_stock_value=($stock_qty)*$request->currentRate;
            }
            else
            {
                // here is closing stock is negative so need to deduct in profit and loss calculation
                $negative_closing_stock=true;
                $negative_stock_qty=$total_purchase_qty_of_month-$total_sales_qty_of_month;
            }

            //$stock_qty=$total_purchase_qty-$total_sales_qty;


            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $company_name=Company::where('id',$company_id)->first();

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 18);
            $html=$company_name->Name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $title='PROFIT AND LOSS REPORT ('.date('M Y', strtotime($request->month)).')';
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);


            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color:#0e4714">
                     <td width="350" align="right" colspan="3">Total Sales <br> (QTY :'.number_format($total_sales_qty_of_month,2,'.',',').') <br> ( INPUT VAT AMOUNT : '.number_format($total_input_vat,2,'.',',').')</td>
                     <td width="150" align="right">'.number_format($total_sales,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="350" align="right" colspan="3">Opening Stock Value (only showment-already in purchase) <br>'.number_format($prev_stock_qty,2,'.',',').'@'.$prev_average_price.'</td>
                     <td width="150" align="right">'.number_format(($prev_stock_qty*$prev_average_price),2,'.',',').'</td>
                    </tr>';

            $html.= '<tr style="color:#a83232">
                     <td width="350" align="right" colspan="3">Total Purchase (-) <br> (QTY :'.number_format($total_purchase_qty_of_month,2,'.',',').') <br> ( OUTPUT VAT AMOUNT : '.number_format($total_output_vat,2,'.',',').')</td>
                     <td width="150" align="right">'.number_format($total_purchase,2,'.',',').'</td>
                    </tr>';
            if($negative_closing_stock==true)
            {
                $html.= '<tr>
                     <td width="350" align="right" colspan="3">Current Month Negative Stock Value (-) <br>'.number_format($negative_stock_qty,2,'.',',').'@'.$request->currentRate.'</td>
                     <td width="150" align="right">'.number_format(($negative_stock_qty*$request->currentRate),2,'.',',').'</td>
                    </tr>';
            }
            else
            {
                $html.= '<tr>
                     <td width="350" align="right" colspan="3">Closing Stock Value (only showment-already in sales) <br>'.number_format($stock_qty,2,'.',',').'@'.$request->currentRate.'</td>
                     <td width="150" align="right">'.number_format($payable_stock_value,2,'.',',').'</td>
                    </tr>';
            }
            $html.= '<tr style="color:#a83232">
                     <td width="350" align="right" colspan="3">Total Expenses (-) </td>
                     <td width="150" align="right">'.number_format($total_expense,2,'.',',').'</td>
                    </tr>';
            if($negative_closing_stock==true)
            {
                $html.= '<tr style="color:#0e4714">
                      <td width="350" align="right" colspan="3">Net Income </td>
                      <td width="150" align="right">'.number_format((($total_sales-$total_purchase+($negative_stock_qty*$request->currentRate)-$total_expense)),2,'.',',').'</td>
                     </tr>';
            }
            else
            {
                $html.= '<tr style="color:#0e4714">
                      <td width="350" align="right" colspan="3">Net Income </td>
                      <td width="150" align="right">'.number_format((($total_sales-$total_purchase-$total_expense)),2,'.',',').'</td>
                     </tr>';
            }

            //$pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            //echo "<pre>";print_r($html);die;
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time='p_and_l_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;

        }
        else
        {
            return FALSE;
        }
    }

    public function Print_profit_loss_for_non_current_month_for_negative_beg_inventory($request)
    {
        if($request->month!='')
        {
            $company_id = session('company_id');

            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));

            //total sales for this month + neg. beg inv - purchase - expense

            // start getting total sales amount with vat
            $total_sales=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');

            $total_purchase=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');

            $total_expense=Expense::where('expenseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('expenseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('grandTotal');

            $total_purchase_qty_of_month=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_output_vat=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');
            //total sales quantity
            $total_sales_qty_of_month=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_input_vat=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');

            //find the previous month stock value
            //1. find previous month average purchase price
            $month=explode('-',$request->month);
            $prev_month=str_pad($month[1]-1, 2, '0', STR_PAD_LEFT);;
            $dt = $month[0].'-'.$prev_month.'-01';
            $_prev_start_date = date("Y-m-01", strtotime($dt));
            $_prev_end_date = date("Y-m-t", strtotime($dt));
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$_prev_start_date, $_prev_end_date])->sum('Quantity');
            $total_purchase_amount=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$_prev_start_date, $_prev_end_date])->sum('rowSubTotal');
            $prev_average_price=round($total_purchase_amount/$total_purchase_qty,2);
            //2.find previous month stock
            $prev_total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('createdDate','<',$start_date)->where('deleted_at','=',NULL)->sum('Quantity');
            //total sales quantity
            $prev_total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','<',$start_date)->where('deleted_at','=',NULL)->sum('Quantity');
            $prev_month_stock=$prev_total_purchase_qty-$prev_total_sales_qty;

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $company_id = session('company_id');
            $company_name=Company::where('id',$company_id)->first();

            $pdf::AddPage();
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 18);
            $html=$company_name->Name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $title='PROFIT AND LOSS REPORT ('.date('M Y', strtotime($request->month)).')';
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color:#0e4714">
                     <td width="350" align="right" colspan="3">Total Sales <br> (QTY :'.number_format($total_sales_qty_of_month,2,'.',',').') <br> ( INPUT VAT AMOUNT : '.number_format($total_input_vat,2,'.',',').')</td>
                     <td width="150" align="right">'.number_format($total_sales,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="350" align="right" colspan="3">Negative Opening Inv (+) <br>'.number_format($prev_month_stock,2,'.',',').'@'.$prev_average_price.'</td>
                     <td width="150" align="right">'.number_format(abs(($prev_month_stock*$prev_average_price)),2,'.',',').'</td>
                    </tr>';

            $html.= '<tr style="color:#a83232">
                     <td width="350" align="right" colspan="3">Total Purchase (-) <br> (QTY :'.number_format($total_purchase_qty_of_month,2,'.',',').') <br> ( OUTPUT VAT AMOUNT : '.number_format($total_output_vat,2,'.',',').')</td>
                     <td width="150" align="right">'.number_format($total_purchase,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="350" align="right" colspan="3">Closing Stock Value (only showment-already in sales) <br>'.number_format(0,2,'.',',').'@'.$request->currentRate.'</td>
                     <td width="150" align="right">'.number_format(0,2,'.',',').'</td></tr>';
            $html.= '<tr style="color:#a83232">
                     <td width="350" align="right" colspan="3">Total Expenses (-) </td>
                     <td width="150" align="right">'.number_format($total_expense,2,'.',',').'</td>
                    </tr>';

            $html.= '<tr style="color:#0e4714">
                      <td width="350" align="right" colspan="3">Net Income </td>
                      <td width="150" align="right">'.number_format((($total_sales+(abs($prev_month_stock*$prev_average_price)))-$total_purchase-$total_expense),2,'.',',').'</td>
                     </tr>';
            $html.='</table>';
            //echo "<pre>";print_r($html);die;
            $pdf::writeHTML($html, false, false, false, false, '');

            $pdf::lastPage();
            $time='p_and_l_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function PrintProfit_lossService(Request $request)
    {
        if($request->month!='')
        {
            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));
            $company_id = session('company_id');

            // start getting total sales amount with vat
            $total_sales=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_purchase=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_expense=Expense::where('expenseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('expenseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('grandTotal');

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $company_name=Company::where('id',$company_id)->first();

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 18);
            $html=$company_name->Name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $title='PROFIT AND LOSS REPORT '.date('M Y', strtotime($request->month));
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            /*$total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            //total sales quantity
            $total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $stock_qty=$total_purchase_qty-$total_sales_qty;
            $payable_stock_value=$stock_qty*$request->currentRate;*/

            /*$dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));

            $total_purchase_qty_of_month=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_output_vat=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');*/
            //total sales quantity
            $total_sales_qty_of_month=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_input_vat=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');


            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color: #1358C8">
                     <td width="300" align="right" colspan="3">Total Sales <br> ( INPUT VAT AMOUNT : '.number_format($total_input_vat,2,'.',',').')</td>
                     <td width="200" align="right">'.number_format($total_sales,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Expenses </td>
                     <td width="200" align="right">'.number_format($total_expense,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Net Income </td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)),2,'.',',').'</td>
                    </tr>';
            //$pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            //echo "<pre>";print_r($html);die;
            $pdf::writeHTML($html, true, false, false, false, '');

            if(session('company_id')==4)
            {
                $html='<table border="0.5" cellpadding="2">';
                $html.= '<tr style="color: #1358C8">
                     <td width="300" align="right" colspan="3">Garage Rent (25%) :</td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)*25/100),2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Wahid (37.5%) :</td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)*37.5/100),2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Other (37.5%) :</td>
                     <td width="200" align="right">'.number_format((((($total_sales-$total_expense)*37.5/100))),2,'.',',').'</td>
                    </tr>';
                //$pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                //echo "<pre>";print_r($html);die;
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            if(session('company_id')==5)
            {
                $html='<table border="0.5" cellpadding="2">';
                $html.= '<tr style="color: #1358C8">
                     <td width="300" align="right" colspan="3">Wahid (33.33%) :</td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)*33.33/100),2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Other A (33.33%) :</td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)*33.33/100),2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Other B (33.33%) :</td>
                     <td width="200" align="right">'.number_format((((($total_sales-$total_expense)*33.33/100))),2,'.',',').'</td>
                    </tr>';
                //$pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                //echo "<pre>";print_r($html);die;
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            /////////////////* just display garage value data here */////////////////
            /*$dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));
            $company_id = session('company_id');*/

            //total receivable from customers
            $result_array=array();
            $customers=Customer::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id','!=',4)->where('isActive','=',1)->get();
            foreach ($customers as $customer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_receivable=array_sum($row);

            //cash in hand
            $sum_of_debit_cash=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('Debit');
            $sum_of_credit_cash=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('Credit');
            $cash_in_hand=$sum_of_debit_cash-$sum_of_credit_cash;

            //sum of all bank balances
            $all_banks = Bank::select('id',)->where(['deleted_at'=> NULL,])->where('company_id',session('company_id'))->get();
            $total_balance_in_bank=0.00;
            foreach($all_banks as $bank)
            {
                $credit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->sum('Credit');
                $debit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $total_balance_in_bank+=$diff;
            }

            $result_array=array();
            $suppliers=Supplier::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id',2)->get();
            foreach ($suppliers as $supplier)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Name'=>$supplier->Name,'Mobile'=>$supplier->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_supplier_outstanding=array_sum($row);

            //loans
            //loan payable
            $loan_payable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',1)->sum('inward_RemainingBalance');

            //loan receivable
            $loan_receivable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',0)->sum('outward_RemainingBalance');

            //investor amount
            $result_array=array();
            $investors=Investor::get();
            foreach ($investors as $investor)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->sum('Credit');
                $debit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=array_column($result_array,'Differentiate');
            $investor=array_sum($row);
            //echo "<pre>";print_r($investor);die;

            $title='COMPANY VALUE ';
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color: #d70820">
                     <td width="300" align="right" colspan="3">Total Receivable </td>
                     <td width="200" align="right">' .number_format($total_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="300" align="right" colspan="3">Total Cash +</td>
                     <td width="200" align="right">'.number_format($cash_in_hand,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Bank +</td>
                     <td width="200" align="right">'.number_format($total_balance_in_bank,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Receivable (only showment)</td>
                     <td width="200" align="right">'.number_format($loan_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Subtotal = </td>
                     <td width="200" align="right">'.number_format(($total_receivable+$cash_in_hand+$total_balance_in_bank),2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Supplier Outstanding -</td>
                     <td width="200" align="right">'.number_format($total_supplier_outstanding,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Payable -</td>
                     <td width="200" align="right">'.number_format($loan_payable,2,'.',',').'</td>
                    </tr>';
            if($investor>0)
            {
                $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Payable to Investor -</td>
                     <td width="200" align="right">'.number_format($investor,2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Garage Value </td>
                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank)-($total_supplier_outstanding+$loan_payable+$investor)),2,'.',',').'</td>
                    </tr>';
            }
            else
            {
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Garage Value </td>
                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank)-($total_supplier_outstanding+$loan_payable)),2,'.',',').'</td>
                    </tr>';
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            /////////////////////* just display garage value data here *//////////////////////////////////

            $pdf::lastPage();
            $time='p_and_l_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function PrintProfit_loss_by_date(Request $request)
    {
        if($request->fromDate!='' && $request->toDate!='')
        {
            if(session('company_id')==4 OR session('company_id')==5 OR session('company_id')==8)
            {
                return $this->PrintProfit_loss_by_dateService($request);
            }

            $start_date=$request->fromDate;
            $end_date=$request->toDate;
            $company_id = session('company_id');

            // average purchase price between two dates
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_purchase_amount=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowSubTotal');
            $average_price=round($total_purchase_amount/$total_purchase_qty,2);

            // start getting total sales amount with vat
            $total_sales=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_purchase=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_expense=Expense::where('expenseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('expenseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('grandTotal');

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $company_name=Company::where('id',$company_id)->first();

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            /////////////////

            $pdf::SetFont('helvetica', '', 18);
            $title=$company_name->Name;
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $title='PROFIT AND LOSS REPORT FROM  '.date('d-m-y', strtotime($start_date)).' To '.date('d-m-y', strtotime($end_date));

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::Ln(8);
            //////////////

            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            //total sales quantity
            $total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->sum('Quantity');
            $stock_qty=$total_purchase_qty-$total_sales_qty;
            $payable_stock_value=$stock_qty*$average_price;

            $total_purchase_qty_of_month=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_output_vat=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');
            //total sales quantity
            $total_sales_qty_of_month=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_input_vat=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');


            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color: #1358C8">
                     <td width="300" align="right" colspan="3">Total Sales <br> (QTY :'.number_format($total_sales_qty_of_month,2,'.',',').') <br> ( INPUT VAT AMOUNT : '.number_format($total_input_vat,2,'.',',').')</td>
                     <td width="200" align="right">'.number_format($total_sales,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="300" align="right" colspan="3">Total Purchase <br> (QTY :'.number_format($total_purchase_qty_of_month,2,'.',',').') <br> ( OUTPUT VAT AMOUNT : '.number_format($total_output_vat,2,'.',',').')</td>
                     <td width="200" align="right">'.number_format($total_purchase,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Expenses </td>
                     <td width="200" align="right">'.number_format($total_expense,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Stock Value <br>'.number_format($stock_qty,2,'.',',').'@'.$average_price.'</td>
                     <td width="200" align="right">'.number_format($payable_stock_value,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Net Income </td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_purchase-$total_expense)+$payable_stock_value),2,'.',',').'</td>
                    </tr>';
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time='p_and_l_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function PrintProfit_loss_by_dateService(Request $request)
    {
        if($request->fromDate!='' && $request->toDate!='')
        {
            $dt = $request->month.'-01';
            $start_date=$request->fromDate;
            $end_date=$request->toDate;
            $company_id = session('company_id');

            // start getting total sales amount with vat
            $total_sales=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('grandTotal');
            $total_expense=Expense::where('expenseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('expenseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('grandTotal');

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $company_name=Company::where('id',$company_id)->first();

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 18);
            $title=$company_name->Name;
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $title='PROFIT AND LOSS REPORT FROM  '.date('d-m-y', strtotime($start_date)).' To '.date('d-m-y', strtotime($end_date));

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::Ln(8);

            //total sales quantity
            $total_sales_qty_of_month=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('Quantity');
            $total_input_vat=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->whereBetween('createdDate', [$start_date, $end_date])->sum('rowVatAmount');

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color: #1358C8">
                     <td width="300" align="right" colspan="3">Total Sales <br> ( INPUT VAT AMOUNT : '.number_format($total_input_vat,2,'.',',').')</td>
                     <td width="200" align="right">'.number_format($total_sales,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Expenses </td>
                     <td width="200" align="right">'.number_format($total_expense,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Net Income </td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)),2,'.',',').'</td></tr>';
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            if(session('company_id')==4)
            {
                $html='<table border="0.5" cellpadding="2">';
                $html.= '<tr style="color: #1358C8">
                     <td width="300" align="right" colspan="3">Garage Rent (25%) :</td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)*25/100),2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Wahid (37.5%) :</td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)*37.5/100),2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Other (37.5%) :</td>
                     <td width="200" align="right">'.number_format((((($total_sales-$total_expense)*37.5/100))),2,'.',',').'</td>
                    </tr>';
                //$pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                //echo "<pre>";print_r($html);die;
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            if(session('company_id')==5)
            {
                $html='<table border="0.5" cellpadding="2">';
                $html.= '<tr style="color: #1358C8">
                     <td width="300" align="right" colspan="3">Wahid (33.33%) :</td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)*33.33/100),2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Other A (33.33%) :</td>
                     <td width="200" align="right">'.number_format((($total_sales-$total_expense)*33.33/100),2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Other B (33.33%) :</td>
                     <td width="200" align="right">'.number_format((((($total_sales-$total_expense)*33.33/100))),2,'.',',').'</td>
                    </tr>';
                //$pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                //echo "<pre>";print_r($html);die;
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            /////////////////* just display garage value data here */////////////////
            /*$dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));
            $company_id = session('company_id');*/

            //total receivable from customers
            $result_array=array();
            $customers=Customer::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id','!=',4)->where('isActive','=',1)->get();
            foreach ($customers as $customer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_receivable=array_sum($row);

            //cash in hand
            $sum_of_debit_cash=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('Debit');
            $sum_of_credit_cash=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('Credit');
            $cash_in_hand=$sum_of_debit_cash-$sum_of_credit_cash;

            //sum of all bank balances
            $all_banks = Bank::select('id',)->where(['deleted_at'=> NULL,])->where('company_id',session('company_id'))->get();
            $total_balance_in_bank=0.00;
            foreach($all_banks as $bank)
            {
                $credit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->sum('Credit');
                $debit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $total_balance_in_bank+=$diff;
            }

            $result_array=array();
            $suppliers=Supplier::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id',2)->get();
            foreach ($suppliers as $supplier)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Credit');
                $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Name'=>$supplier->Name,'Mobile'=>$supplier->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_supplier_outstanding=array_sum($row);

            //loans
            //loan payable
            $loan_payable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',1)->sum('inward_RemainingBalance');

            //loan receivable
            $loan_receivable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',0)->sum('outward_RemainingBalance');

            //investor amount
            //if company_id is 4 workshop then
            if(session('company_id')==4)
            {
                $result_array=array();
                $investors=Investor::where('id',4)->get();
                foreach ($investors as $investor)
                {
                    //get diff of total debit and credit column
                    $credit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->sum('Credit');
                    $debit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->sum('Debit');
                    $diff=$credit_sum-$debit_sum;
                    $temp=array('Differentiate'=>$diff);
                    $result_array[]=$temp;
                    unset($temp);
                }
                $row=array_column($result_array,'Differentiate');
                $investor=array_sum($row);
                //echo "<pre>";print_r($investor);die;
            }
            else
            {
                $result_array=array();
                $investors=Investor::where('company_id',session('company_id'))->get();
                foreach ($investors as $investor)
                {
                    //get diff of total debit and credit column
                    $credit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->sum('Credit');
                    $debit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->sum('Debit');
                    $diff=$credit_sum-$debit_sum;
                    $temp=array('Differentiate'=>$diff);
                    $result_array[]=$temp;
                    unset($temp);
                }
                $row=array_column($result_array,'Differentiate');
                $investor=array_sum($row);
                //echo "<pre>";print_r($investor);die;
            }

            $title='COMPANY VALUE ';
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color: #d70820">
                     <td width="300" align="right" colspan="3">Total Receivable </td>
                     <td width="200" align="right">' .number_format($total_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="300" align="right" colspan="3">Total Cash +</td>
                     <td width="200" align="right">'.number_format($cash_in_hand,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Bank +</td>
                     <td width="200" align="right">'.number_format($total_balance_in_bank,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Receivable (only showment)</td>
                     <td width="200" align="right">'.number_format($loan_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Subtotal = </td>
                     <td width="200" align="right">'.number_format(($total_receivable+$cash_in_hand+$total_balance_in_bank),2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Supplier Outstanding -</td>
                     <td width="200" align="right">'.number_format($total_supplier_outstanding,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Payable -</td>
                     <td width="200" align="right">'.number_format($loan_payable,2,'.',',').'</td>
                    </tr>';
            if($investor>0)
            {
                $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Payable to Investor -</td>
                     <td width="200" align="right">'.number_format($investor,2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Garage Value </td>
                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank)-($total_supplier_outstanding+$loan_payable+$investor)),2,'.',',').'</td>
                    </tr>';
            }
            else
            {
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Garage Value </td>
                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank)-($total_supplier_outstanding+$loan_payable)),2,'.',',').'</td>
                    </tr>';
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            /////////////////////* just display garage value data here *//////////////////////////////////

            $pdf::lastPage();
            $time='p_and_l_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function PrintGarage_value(Request $request)
    {
        if($request->month!='' && $request->currentRate!='')
        {
            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));
            $company_id = session('company_id');

            $current_month=date('m');
            $request_month=explode('-',$request->month);
            if($request_month[1]!=$current_month)
            {
                return $this->print_garage_value_for_non_current_month($request);
            }

            //total receivable from customers
//            $total_receivable=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('remainingBalance');

            $result_array=array();
            $customers=Customer::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id','!=',4)->where('isActive','=',1)->get();
            foreach ($customers as $customer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->where('createdDate','<=',$end_date)->sum('Credit');
                $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->where('createdDate','<=',$end_date)->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_receivable=array_sum($row);

            //cash in hand
//            $cash_in_hand=CashTransaction::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->max('id');
//            $lastTransaction = CashTransaction::where(['id'=> $cash_in_hand,])->get()->first();
//            $cash_in_hand=$lastTransaction->Differentiate;

            $sum_of_debit_before_from_date=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Debit');
            $sum_of_credit_before_from_date=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Credit');
            $cash_in_hand=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;

            //sum of all bank balances
            $all_banks = Bank::select('id',)->where(['deleted_at'=> NULL,])->where('company_id',session('company_id'))->get();
            $total_balance_in_bank=0.00;
            foreach($all_banks as $bank)
            {
//                $last_transaction=BankTransaction::where('bank_id','=',$bank->id)->where('deleted_at','=',NULL)->max('id');
//                $lastTransaction = BankTransaction::where(['id'=> $last_transaction,])->get()->first();
//                $total_balance_in_bank+=$lastTransaction->Differentiate;
                $credit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Credit');
                $debit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $total_balance_in_bank+=$diff;
            }

            //stock value
                //total purchase quantity
                //$total_purchase_qty=PurchaseDetail::where('createdDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('createdDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('Quantity');
                $total_purchase_qty=PurchaseDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('createdDate','<=',$end_date)->sum('Quantity');
                //total sales quantity
                //$total_sales_qty=SaleDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('createdDate','<=',$end_date)->where('rem_stock_entry',0)->sum('Quantity');
                $total_sales_qty=SaleDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('createdDate','<=',$end_date)->sum('Quantity');
                $stock_qty=$total_purchase_qty-$total_sales_qty;
                $stock_value=$stock_qty*$request->currentRate;

                //supplier outstanding
//            $total_supplier_outstanding=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('remainingBalance');

            $result_array=array();
            $suppliers=Supplier::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id',2)->get();
            foreach ($suppliers as $supplier)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->where('createdDate','<=',$end_date)->sum('Credit');
                $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->where('createdDate','<=',$end_date)->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Name'=>$supplier->Name,'Mobile'=>$supplier->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_supplier_outstanding=array_sum($row);

            //loans
            //loan payable
            $loan_payable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',1)->where('isActive',1)->where('loanDate','<=',$end_date)->sum('inward_RemainingBalance');

            // getting latest closing for all financer from account transaction table
            /*$result_array=array();
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
            $loan_payable=array_sum($row);*/
            //loan receivable
            $loan_receivable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',0)->where('loanDate','<=',$end_date)->sum('outward_RemainingBalance');

            //investor amount
            $result_array=array();
            $investors=Investor::get();
            foreach ($investors as $investor)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Credit');
                $debit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=array_column($result_array,'Differentiate');
            $investor=array_sum($row);
            //echo "<pre>";print_r($investor);die;

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $company_name=Company::where('id',$company_id)->first();
            $pdf::SetFont('helvetica', '', 18);
            $html='<u>'.$company_name->Name.'</u>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $title='GARAGE VALUE REPORT '.date('M Y', strtotime($request->month));
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color: #d70820">
                     <td width="300" align="right" colspan="3">Total Receivable </td>
                     <td width="200" align="right">' .number_format($total_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="300" align="right" colspan="3">Total Cash +</td>
                     <td width="200" align="right">'.number_format($cash_in_hand,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Bank +</td>
                     <td width="200" align="right">'.number_format($total_balance_in_bank,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Current Stock Value + <br>'.number_format($stock_qty,2,'.',',').'@'.$request->currentRate.'</td>
                     <td width="200" align="right">'.number_format($stock_value,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Receivable(only showment)</td>
                     <td width="200" align="right">'.number_format($loan_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Subtotal = </td>
                     <td width="200" align="right">'.number_format(($total_receivable+$cash_in_hand+$total_balance_in_bank+$stock_value),2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Supplier Outstanding -</td>
                     <td width="200" align="right">'.number_format($total_supplier_outstanding,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Payable -</td>
                     <td width="200" align="right">'.number_format($loan_payable,2,'.',',').'</td>
                    </tr>';
            if($investor>0)
            {
                $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Payable to Investor -</td>
                     <td width="200" align="right">'.number_format($investor,2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Garage Value </td>
                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank+$stock_value+$loan_receivable)-($total_supplier_outstanding+$loan_payable+$investor)),2,'.',',').'</td>
                    </tr>';
            }
            else
            {
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Garage Value </td>
                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank+$stock_value)-($total_supplier_outstanding+$loan_payable)),2,'.',',').'</td>
                    </tr>';
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::registrationMarkCMYK(207, 294, 2);


            $pdf::lastPage();
            $time='Garage_Value_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;

        }
        else
        {
            return FALSE;
        }
    }

    public function print_garage_value_for_non_current_month(Request $request)
    {
        if($request->month!='' && $request->currentRate!='')
        {
            $dt = $request->month.'-01';
            $start_date=date("Y-m-01", strtotime($dt));
            $end_date=date("Y-m-t", strtotime($dt));
            $company_id = session('company_id');

            $current_month=date('m');
            $request_month=explode('-',$request->month);

            $result_array=array();
            $customers=Customer::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id','!=',4)->where('isActive','=',1)->get();
            foreach ($customers as $customer)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->where('createdDate','<=',$end_date)->sum('Credit');
                $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->where('createdDate','<=',$end_date)->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_receivable=array_sum($row);

            $sum_of_debit_before_from_date=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Debit');
            $sum_of_credit_before_from_date=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Credit');
            $cash_on_hand=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;

            //sum of all bank balances
            $all_banks = Bank::select('id',)->where(['deleted_at'=> NULL,])->where('company_id',session('company_id'))->get();
            $total_balance_in_bank=0.00;
            foreach($all_banks as $bank)
            {
//                $last_transaction=BankTransaction::where('bank_id','=',$bank->id)->where('deleted_at','=',NULL)->max('id');
//                $lastTransaction = BankTransaction::where(['id'=> $last_transaction,])->get()->first();
//                $total_balance_in_bank+=$lastTransaction->Differentiate;
                $credit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Credit');
                $debit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Debit');
                $diff=$debit_sum-$credit_sum;
                $total_balance_in_bank+=$diff;
            }

            //stock value
            //total purchase quantity
            //$total_purchase_qty=PurchaseDetail::where('createdDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('createdDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('Quantity');
            $total_purchase_qty=PurchaseDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('createdDate','<=',$end_date)->sum('Quantity');
            //total sales quantity
            //$total_sales_qty=SaleDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('createdDate','<=',$end_date)->where('rem_stock_entry',0)->sum('Quantity');
            $total_sales_qty=SaleDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->where('createdDate','<=',$end_date)->sum('Quantity');
            $stock_qty=$total_purchase_qty-$total_sales_qty;
            $stock_value=$stock_qty*$request->currentRate;

            //supplier outstanding
//            $total_supplier_outstanding=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('remainingBalance');

            $result_array=array();
            $suppliers=Supplier::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id',2)->get();
            foreach ($suppliers as $supplier)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->where('createdDate','<=',$end_date)->sum('Credit');
                $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->where('createdDate','<=',$end_date)->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Name'=>$supplier->Name,'Mobile'=>$supplier->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
            $row=array_values($row);
            $row=array_column($row,'Differentiate');
            $total_supplier_outstanding=array_sum($row);

            //loans
            //loan payable
            $loan_payable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',1)->where('isActive',1)->where('loanDate','<=',$end_date)->sum('inward_RemainingBalance');

            // getting latest closing for all financer from account transaction table
            /*$result_array=array();
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
            $loan_payable=array_sum($row);*/
            //loan receivable
            $loan_receivable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',0)->where('loanDate','<=',$end_date)->sum('outward_RemainingBalance');

            //investor amount
            $result_array=array();
            $investors=Investor::get();
            foreach ($investors as $investor)
            {
                //get diff of total debit and credit column
                $credit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Credit');
                $debit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->where('createdDate','<=',$end_date)->sum('Debit');
                $diff=$credit_sum-$debit_sum;
                $temp=array('Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
            $row=array_column($result_array,'Differentiate');
            $investor=array_sum($row);
            //echo "<pre>";print_r($investor);die;

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::SetXY(5,5);
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $company_name=Company::where('id',$company_id)->first();
            $pdf::SetFont('helvetica', '', 18);
            $html='<u>'.$company_name->Name.'</u>';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $title='GARAGE VALUE REPORT '.date('M Y', strtotime($request->month));
            $time='Date : '.date('d-m-Y h:i:s');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 12);
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $html='<table border="0.5" cellpadding="2">';
            $html.= '<tr style="color: #d70820">
                     <td width="300" align="right" colspan="3">Total Receivable </td>
                     <td width="200" align="right">' .number_format($total_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr>
                     <td width="300" align="right" colspan="3">Total Cash +</td>
                     <td width="200" align="right">'.number_format($cash_on_hand,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Bank +</td>
                     <td width="200" align="right">'.number_format($total_balance_in_bank,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Current Stock Value + <br>'.number_format($stock_qty,2,'.',',').'@'.$request->currentRate.'</td>
                     <td width="200" align="right">'.number_format($stock_value,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Receivable(only showment)</td>
                     <td width="200" align="right">'.number_format($loan_receivable,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Subtotal = </td>
                     <td width="200" align="right">'.number_format(($total_receivable+$cash_on_hand+$total_balance_in_bank+$stock_value),2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Total Supplier Outstanding -</td>
                     <td width="200" align="right">'.number_format($total_supplier_outstanding,2,'.',',').'</td>
                    </tr>';
            $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Loan Payable -</td>
                     <td width="200" align="right">'.number_format($loan_payable,2,'.',',').'</td>
                    </tr>';
            if($investor>0)
            {
                $html.= '<tr style="color:#5e3431">
                     <td width="300" align="right" colspan="3">Payable to Investor -</td>
                     <td width="200" align="right">'.number_format($investor,2,'.',',').'</td>
                    </tr>';
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Garage Value </td>
                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank+$stock_value+$loan_receivable)-($total_supplier_outstanding+$loan_payable+$investor)),2,'.',',').'</td>
                    </tr>';
            }
            else
            {
                $html.= '<tr style="color:#0e4714">
                     <td width="300" align="right" colspan="3">Garage Value </td>
                     <td width="200" align="right">'.number_format((($total_receivable+$cash_on_hand+$total_balance_in_bank+$stock_value)-($total_supplier_outstanding+$loan_payable)),2,'.',',').'</td>
                    </tr>';
            }
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::registrationMarkCMYK(207, 294, 2);

            $pdf::lastPage();
            $time='Garage_Value_'.time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

//    public function PrintGarage_value(Request $request)
//    {
//        if($request->month!='' && $request->currentRate!='')
//        {
//            $dt = $request->month.'-01';
//            $start_date=date("Y-m-01", strtotime($dt));
//            $end_date=date("Y-m-t", strtotime($dt));
//            $company_id = session('company_id');
//
//            //total receivable from customers
////            $total_receivable=Sale::where('SaleDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('SaleDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('remainingBalance');
//
//            $result_array=array();
//            $customers=Customer::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id','!=',4)->where('isActive','=',1)->get();
//            foreach ($customers as $customer)
//            {
//                //get diff of total debit and credit column
//                $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Credit');
//                $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Debit');
//                $diff=$debit_sum-$credit_sum;
//                $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff);
//                $result_array[]=$temp;
//                unset($temp);
//            }
//            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
//            $row=array_values($row);
//            $row=array_column($row,'Differentiate');
//            $total_receivable=array_sum($row);
//
//            //cash in hand
////            $cash_in_hand=CashTransaction::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->max('id');
////            $lastTransaction = CashTransaction::where(['id'=> $cash_in_hand,])->get()->first();
////            $cash_in_hand=$lastTransaction->Differentiate;
//
//            $sum_of_debit_before_from_date=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('Debit');
//            $sum_of_credit_before_from_date=CashTransaction::where('company_id',session('company_id'))->whereNull('deleted_at')->sum('Credit');
//            $cash_in_hand=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;
//
//            //sum of all bank balances
//            $all_banks = Bank::select('id',)->where(['deleted_at'=> NULL,])->where('company_id',session('company_id'))->get();
//            $total_balance_in_bank=0.00;
//            foreach($all_banks as $bank)
//            {
////                $last_transaction=BankTransaction::where('bank_id','=',$bank->id)->where('deleted_at','=',NULL)->max('id');
////                $lastTransaction = BankTransaction::where(['id'=> $last_transaction,])->get()->first();
////                $total_balance_in_bank+=$lastTransaction->Differentiate;
//                $credit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->sum('Credit');
//                $debit_sum=BankTransaction::where('bank_id',$bank->id)->whereNull('deleted_at')->sum('Debit');
//                $diff=$debit_sum-$credit_sum;
//                $total_balance_in_bank+=$diff;
//            }
//
//            //stock value
//            //total purchase quantity
//            //$total_purchase_qty=PurchaseDetail::where('createdDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('createdDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('Quantity');
//            $total_purchase_qty=PurchaseDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('Quantity');
//            //total sales quantity
//            $total_sales_qty=SaleDetail::where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('Quantity');
//            $stock_qty=$total_purchase_qty-$total_sales_qty;
//            $stock_value=$stock_qty*$request->currentRate;
//
//            //supplier outstanding
////            $total_supplier_outstanding=Purchase::where('PurchaseDate','>=',date("y/m/d", strtotime($start_date.' 00:00:00')))->where('PurchaseDate','<=',$end_date.' 23:59:59')->where('company_id','=',$company_id)->where('deleted_at','=',NULL)->sum('remainingBalance');
//
//            $result_array=array();
//            $suppliers=Supplier::select('id','Name','Mobile')->where('company_id',session('company_id'))->where('company_type_id',2)->get();
//            foreach ($suppliers as $supplier)
//            {
//                //get diff of total debit and credit column
//                $credit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Credit');
//                $debit_sum=AccountTransaction::where('supplier_id',$supplier->id)->whereNull('updateDescription')->sum('Debit');
//                $diff=$credit_sum-$debit_sum;
//                $temp=array('Name'=>$supplier->Name,'Mobile'=>$supplier->Mobile,'Differentiate'=>$diff);
//                $result_array[]=$temp;
//                unset($temp);
//            }
//            $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
//            $row=array_values($row);
//            $row=array_column($row,'Differentiate');
//            $total_supplier_outstanding=array_sum($row);
//
//            //loans
//            //loan payable
//            $loan_payable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',1)->where('isActive',1)->sum('inward_RemainingBalance');
//
//            // getting latest closing for all financer from account transaction table
//            /*$result_array=array();
//            $financers=Financer::where('company_id',session('company_id'))->get();
//            foreach ($financers as $financer)
//            {
//                //get diff of total debit and credit column
//                $credit_sum=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$financer->id)->whereNull('deleted_at')->sum('Credit');
//                $debit_sum=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$financer->id)->whereNull('deleted_at')->sum('Debit');
//                $diff=$credit_sum-$debit_sum;
//                $temp=array('Differentiate'=>$diff);
//                $result_array[]=$temp;
//                unset($temp);
//            }
//            $row=array_column($result_array,'Differentiate');
//            $loan_payable=array_sum($row);*/
//            //loan receivable
//            $loan_receivable=LoanMaster::where('company_id','=',$company_id)->where('isPushed','=',1)->where('deleted_at','=',NULL)->where('loanType',0)->sum('outward_RemainingBalance');
//
//            //investor amount
//            $result_array=array();
//            $investors=Investor::get();
//            foreach ($investors as $investor)
//            {
//                //get diff of total debit and credit column
//                $credit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->sum('Credit');
//                $debit_sum=AccountTransaction::where('company_id',session('company_id'))->where('investor_id',$investor->id)->whereNull('deleted_at')->sum('Debit');
//                $diff=$credit_sum-$debit_sum;
//                $temp=array('Differentiate'=>$diff);
//                $result_array[]=$temp;
//                unset($temp);
//            }
//            $row=array_column($result_array,'Differentiate');
//            $investor=array_sum($row);
//            //echo "<pre>";print_r($investor);die;
//
//            $footer=new CustomeFooter;
//            $footer->footer();
//            $pdf = new PDF();
//            $pdf::SetXY(5,5);
//            $pdf::setPrintHeader(false);
//
//            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//            $pdf::SetAutoPageBreak(TRUE, 14);
//
//            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
//            $pdf::SetFillColor(255,255,0);
//
//            $company_name=Company::where('id',$company_id)->first();
//            $pdf::SetFont('helvetica', '', 18);
//            $html='<u>'.$company_name->Name.'</u>';
//            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);
//
//            $title='GARAGE VALUE REPORT '.date('M Y', strtotime($request->month));
//            $time='Date : '.date('d-m-Y h:i:s');
//
//            $pdf::Cell(95,5,$title,'',0,'L');
//            $pdf::SetFont('helvetica', '', 12);
//            $pdf::Cell(95,5,$time,'',0,'R');
//            $pdf::Ln(8);
//
//            $html='<table border="0.5" cellpadding="2">';
//            $html.= '<tr style="color: #d70820">
//                     <td width="300" align="right" colspan="3">Total Receivable </td>
//                     <td width="200" align="right">' .number_format($total_receivable,2,'.',',').'</td>
//                    </tr>';
//            $html.= '<tr>
//                     <td width="300" align="right" colspan="3">Total Cash +</td>
//                     <td width="200" align="right">'.number_format($cash_in_hand,2,'.',',').'</td>
//                    </tr>';
//            $html.= '<tr style="color:#5e3431">
//                     <td width="300" align="right" colspan="3">Total Bank +</td>
//                     <td width="200" align="right">'.number_format($total_balance_in_bank,2,'.',',').'</td>
//                    </tr>';
//            $html.= '<tr style="color:#5e3431">
//                     <td width="300" align="right" colspan="3">Current Stock Value + <br>'.number_format($stock_qty,2,'.',',').'@'.$request->currentRate.'</td>
//                     <td width="200" align="right">'.number_format($stock_value,2,'.',',').'</td>
//                    </tr>';
//            $html.= '<tr style="color:#5e3431">
//                     <td width="300" align="right" colspan="3">Loan Receivable(only showment)</td>
//                     <td width="200" align="right">'.number_format($loan_receivable,2,'.',',').'</td>
//                    </tr>';
//            $html.= '<tr style="color:#5e3431">
//                     <td width="300" align="right" colspan="3">Subtotal = </td>
//                     <td width="200" align="right">'.number_format(($total_receivable+$cash_in_hand+$total_balance_in_bank+$stock_value),2,'.',',').'</td>
//                    </tr>';
//            $html.= '<tr style="color:#5e3431">
//                     <td width="300" align="right" colspan="3">Total Supplier Outstanding -</td>
//                     <td width="200" align="right">'.number_format($total_supplier_outstanding,2,'.',',').'</td>
//                    </tr>';
//            $html.= '<tr style="color:#5e3431">
//                     <td width="300" align="right" colspan="3">Loan Payable -</td>
//                     <td width="200" align="right">'.number_format($loan_payable,2,'.',',').'</td>
//                    </tr>';
//            if($investor>0)
//            {
//                $html.= '<tr style="color:#5e3431">
//                     <td width="300" align="right" colspan="3">Payable to Investor -</td>
//                     <td width="200" align="right">'.number_format($investor,2,'.',',').'</td>
//                    </tr>';
//                $html.= '<tr style="color:#0e4714">
//                     <td width="300" align="right" colspan="3">Garage Value </td>
//                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank+$stock_value+$loan_receivable)-($total_supplier_outstanding+$loan_payable+$investor)),2,'.',',').'</td>
//                    </tr>';
//            }
//            else
//            {
//                $html.= '<tr style="color:#0e4714">
//                     <td width="300" align="right" colspan="3">Garage Value </td>
//                     <td width="200" align="right">'.number_format((($total_receivable+$cash_in_hand+$total_balance_in_bank+$stock_value)-($total_supplier_outstanding+$loan_payable)),2,'.',',').'</td>
//                    </tr>';
//            }
//            $pdf::SetFillColor(255, 0, 0);
//            $html.='</table>';
//            $pdf::writeHTML($html, true, false, false, false, '');
//
//            $pdf::registrationMarkCMYK(207, 294, 2);
//
//
//            $pdf::lastPage();
//            $time='Garage_Value_'.time();
//            $fileLocation = storage_path().'/app/public/report_files/';
//            $fileNL = $fileLocation.'//'.$time.'.pdf';
//            $pdf::Output($fileNL, 'F');
//            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
//            $url=array('url'=>$url);
//            return $url;
//
//        }
//        else
//        {
//            return FALSE;
//        }
//    }

    public function GetSalesQuantitySummary()
    {
        return view('admin.report.get_sales_quantity_summary');
    }

    public function PrintSalesQuantitySummary(Request $request)
    {
        if($request->fromDate!='' && $request->toDate!='')
        {
            $begin = new DateTime($request->fromDate);
            $end   = new DateTime($request->toDate);
            $all_dates=array();
            $final_array=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $qty=SaleDetail::where('company_id','=',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('Quantity');
                $tmp_array=array('Date'=>$date,'Quantity'=>$qty);
                $final_array[]=$tmp_array;
            }

//            $final_array=array();
//            $sales=SalesResource::collection(Sale::with('sale_details')->where('company_id',session('company_id'))->where('SaleDate','>=',$request->fromDate)->where('SaleDate','<=',$request->toDate)->where('isActive','=','1')->where('deleted_at','=',NULL)->orderBy('SaleDate')->get());
//            $row=json_decode(json_encode($sales), true);
//            for($i=0;$i<count($row);$i++)
//            {
//                if($row[$i]['sale_details'][0]['Quantity']==25.00)
//                {
//                    $qty = $row[$i]['sale_details'][0]['id'];
//                    //$tmp_array=array('Date'=>'null','Quantity'=>$qty);
//                    $final_array[] = $qty;
//                }
//            }
//            $sales_ids=SaleDetail::where('Quantity','=',25)->where('company_id','=',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('deleted_at','=',NULL)->where('isActive','=',1)->get();
//            $sales_ids=json_decode(json_encode($sales_ids), true);
//            //echo "<pre>";print_r($final_array);die;
//
//            $sales_ids=array_column($sales_ids,'id');
//            echo "<pre>";print_r($sales_ids);die;
//            $sales_ids=array_diff($final_array,$sales_ids);
//            //$final_array=array_sum($final_array);
//            //$sales_ids=array_sum($sales_ids);
//            echo "<pre>";print_r($sales_ids);die;
        }
        else
        {
            return FALSE;
        }

        if(!empty($final_array))
        {
            $company_title='WATAN PHARMA LLP.';
            $company_address='MUSSAFAH M13,PLOT 100, ABU DHABI,UAE';
            $company_email='Email : info@alhamood.ae';
            $company_mobile='Mobile : +971-25550870  +971-557383866  +971-569777861';
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);
            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $title='DAILY SALES QUANTITY REPORT';
            $time='Date : '.date('d-m-Y h:i:s A');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $row=$final_array;

            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="100">Date</th>
                    <th align="center" width="100">Quantity</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 12);

            $qty_sum=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $qty_sum+=$row[$i]['Quantity'];
                if($i%2==1)
                {
                    $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
            }

            $html.= '<tr color="red">
                     <td width="100" align="right">Total</td>
                     <td width="100" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                 </tr>';

            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();

            $time=time();
            $name='SALES_QTY_SUMMARY_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function GetPurchaseQuantitySummary()
    {
        return view('admin.report.get_purchase_quantity_summary');
    }

    public function PrintPurchaseQuantitySummary(Request $request)
    {
        if($request->fromDate!='' && $request->toDate!='')
        {
            $begin = new DateTime($request->fromDate);
            $end   = new DateTime($request->toDate);
            $all_dates=array();
            $final_array=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('Quantity');
                $tmp_array=array('Date'=>$date,'Quantity'=>$qty);
                $final_array[]=$tmp_array;
            }
        }
        else
        {
            return FALSE;
        }

        if(!empty($final_array))
        {
            $company_title='WATAN PHARMA LLP.';
            $company_address='MUSSAFAH M13,PLOT 100, ABU DHABI,UAE';
            $company_email='Email : info@alhamood.ae';
            $company_mobile='Mobile : +971-25550870  +971-557383866  +971-569777861';

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $title='DAILY PURCHASE QUANTITY REPORT';
            $time='Date : '.date('d-m-Y h:i:s A');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $row=$final_array;

            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="100">Date</th>
                    <th align="center" width="100">Quantity</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 12);

            $qty_sum=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $qty_sum+=$row[$i]['Quantity'];
                if($i%2==1)
                {
                    $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
            }

            $html.= '<tr color="red">
                     <td width="100" align="right">Total</td>
                     <td width="100" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                 </tr>';

            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();

            $time=time();
            $name='PURCHASE_QTY_SUMMARY_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function GetDailyCashSummary()
    {
        return view('admin.report.get_daily_cash_summary');
    }

    public function PrintDailyCashSummary(Request $request)
    {
        if($request->fromDate!='' && $request->toDate!='')
        {
            $begin = new DateTime($request->fromDate);
            $end   = new DateTime($request->toDate);
            $final_array=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $sum_of_debit=CashTransaction::where('company_id',session('company_id'))->where('createdDate','=',$date)->whereNull('deleted_at')->sum('Debit');
                $sum_of_credit=CashTransaction::where('company_id',session('company_id'))->where('createdDate','=',$date)->whereNull('deleted_at')->sum('Credit');
                $amount=$sum_of_debit-$sum_of_credit;
//                $ids=Sale::select('id')->where('customer_id','=',79)->where('SaleDate','=',$date)->whereNull('deleted_at')->get();
//                $ids=json_decode(json_encode($ids), true);
//                $ids=array_column($ids,'id');
//                $amount=SaleDetail::whereIn('sale_id',$ids)->whereNull('deleted_at')->sum('rowSubTotal');
//                echo "<pre>";print_r($amount);die;
                $tmp_array=array('Date'=>$date,'Amount'=>$amount);
                $final_array[]=$tmp_array;
            }
        }
        else
        {
            return FALSE;
        }

        if(!empty($final_array))
        {
            $company_title='WATAN PHARMA LLP.';
            $company_address='MUSSAFAH M13,PLOT 100, ABU DHABI,UAE';
            $company_email='Email : info@alhamood.ae';
            $company_mobile='Mobile : +971-25550870  +971-557383866  +971-569777861';
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);


            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='DAILY CASH REPORT';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $row=$final_array;

            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="100">Date</th>
                    <th align="center" width="100">Amount</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 12);

            $qty_sum=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $qty_sum+=$row[$i]['Amount'];
                if($i%2==1)
                {
                    $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Amount'],2,'.',',').'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Amount'],2,'.',',').'</td>
                    </tr>';
                }
            }

            $html.= '<tr color="red">
                     <td width="100" align="right">Total</td>
                     <td width="100" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                 </tr>';

            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();

            $time=time();
            $name='DAILY_CASH_SUMMARY_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    /* start analysis reports*/
    public function GetReceivableSummaryAnalysis()
    {
        $customers=Customer::select('id','Name')->where('company_id',session('company_id'))->get();
        return view('admin.report.get_receivable_summary_analysis',compact('customers'));
    }

    public function ViewReceivableSummaryAnalysis(Request $request)
    {
        $begin = new DateTime($request->fromDate);
        $end   = new DateTime($request->toDate);
        $all_dates=array();
        for($i = $begin; $i <= $end; $i->modify('+1 day'))
        {
            $all_dates[]=$i->format("Y-m-d");
        }
        $data=Receivable_summary_log::with(['customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('RecordDate', [$request->fromDate, $request->toDate])->orderBy('RecordDate')->get();
        $data=json_decode(json_encode($data), true);
        $customers=Customer::select('id','Name')->where('company_id',session('company_id'))->get();
        return view('admin.report.view_receivable_summary_analysis',compact('data','all_dates','customers'));
    }

    public function PrintReceivableSummaryAnalysisByCustomer(Request $request)
    {
        /*$begin = new DateTime($request->fromDate);
        $end   = new DateTime($request->toDate);
        $all_dates=array();
        for($i = $begin; $i <= $end; $i->modify('+1 day'))
        {
            $all_dates[]=$i->format("Y-m-d");
        }
        $data=Receivable_summary_log::with(['customer'=>function($q){$q->select('id','Name');}])->where('company_id',session('company_id'))->whereBetween('RecordDate', [$request->fromDate, $request->toDate])->orderBy('RecordDate')->get();
        $data=json_decode(json_encode($data), true);
        $customers=Customer::select('id','Name')->where('company_id',session('company_id'))->get();
        return view('admin.report.view_receivable_summary_analysis',compact('data','all_dates','customers'));*/

        $result_array=array();
        $data=Receivable_summary_log::where('customer_id',$request->customer_id)->where('company_id',session('company_id'))->whereBetween('RecordDate', [$request->fromDate, $request->toDate])->orderBy('RecordDate')->get();

        $row=json_decode(json_encode($data), true);;
        //$row=array_values($row);
        //echo "<pre>";print_r($row);die;

        //$data=SalesResource::collection(Sale::get()->where('remainingBalance','!=',0));
        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='CUSTOMER RECEIVABLE SUMMARY DATE TO DATE';
            $date='From '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::Cell(200,5,$html,'',0,'C');
            $pdf::Ln(6);
            $pdf::Cell(95,5,$request->customer_name,'',0,'L');
            $pdf::Cell(95,5,$date,'',0,'R');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="50">SN</th>
                <th align="center" width="200">Date</th>
                <th align="right" width="100">Balance</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);

            /*$pos_arr=array(); $neg_arr=array();
            foreach($row as $key=>$value)
            {
                ($value['Differentiate']<0) ?  $neg_arr[]=$value : $pos_arr[]=$value;
            }*/
            for($i=0;$i<count($row);$i++)
            {
                $html .='<tr>
                <td align="center" width="50">'.($i+1).'</td>
                <td align="left" width="200">'.(date('d-M-Y', strtotime($row[$i]['RecordDate']))).'</td>
                <td align="right" width="100">'.(number_format($row[$i]['BalanceAmount'],2,'.',',')).'</td>
                </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function GetExpenseAnalysis()
    {
        return view('admin.report.get_expense_analysis');
    }

    public function ViewExpenseAnalysis(Request $request)
    {
        $begin = new DateTime($request->fromDate);
        $end   = new DateTime($request->toDate);
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
        $average_of_expenses=$sum_of_expenses/count($all_expenses);
        return view('admin.report.view_expense_analysis',compact('all_expenses','all_dates','sum_of_expenses','average_of_expenses'));
    }

    public function GetExpenseAnalysisByCategory()
    {
        return view('admin.report.get_expense_analysis_by_category');
    }

    public function ViewExpenseAnalysisByCategory(Request $request)
    {
        $title='Category wise Expense Analysis From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y',strtotime($request->toDate));
        $expense_category=ExpenseCategory::all();
        $final_array=array();
        foreach($expense_category as $item)
        {
            $ids=ExpenseDetail::select('expense_id')->where('company_id',session('company_id'))->where('expense_category_id',$item->id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'expense_id');
            $temp=Expense::where('company_id',session('company_id'))->whereIn('id',$ids)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->sum('grandTotal');
            if($temp!=0)
            {
                $tmp_array=[
                    'category_name'=>$item->Name,
                    'total_expense'=>$temp,
                ];
                $final_array[]=$tmp_array;
            }
        }
        $total_exp=array_column($final_array,'total_expense');
        $sum_of_expenses=array_sum($total_exp);
        return view('admin.report.view_expense_analysis_by_category',compact('final_array','sum_of_expenses','title'));
    }

    public function GetExpenseAnalysisByEmployee()
    {
        return view('admin.report.get_expense_analysis_by_employee');
    }

    public function ViewExpenseAnalysisByEmployee(Request $request)
    {
        $title='Employee wise Expense Analysis From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y',strtotime($request->toDate));
        $employees=Employee::where('company_id',session('company_id'))->get();
        $final_array=array();
        foreach($employees as $item)
        {
            $temp=Expense::where('company_id',session('company_id'))->where('employee_id',$item->id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->sum('grandTotal');
            if($temp!=0)
            {
                $tmp_array=[
                    'employee_name'=>$item->Name,
                    'total_expense'=>$temp,
                ];
                $final_array[]=$tmp_array;
            }
        }
        $total_exp=array_column($final_array,'total_expense');
        $sum_of_expenses=array_sum($total_exp);
        return view('admin.report.view_expense_analysis_by_employee',compact('title','final_array','sum_of_expenses'));
    }

    public function GetExpenseAnalysisBySupplier()
    {
        return view('admin.report.get_expense_analysis_by_supplier');
    }

    public function ViewExpenseAnalysisBySupplier(Request $request)
    {
        $title='Supplier wise Expense Analysis From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y',strtotime($request->toDate));
        $suppliers = Supplier::where('company_type_id','=',3)->where('company_id',session('company_id'))->get();
        $final_array=array();
        foreach($suppliers as $item)
        {
            $temp=Expense::where('company_id',session('company_id'))->where('supplier_id',$item->id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->sum('grandTotal');
            if($temp!=0)
            {
                $tmp_array=[
                    'supplier_name'=>$item->Name,
                    'total_expense'=>$temp,
                ];
                $final_array[]=$tmp_array;
            }
        }
        $total_exp=array_column($final_array,'total_expense');
        $sum_of_expenses=array_sum($total_exp);
        return view('admin.report.view_expense_analysis_by_supplier',compact('title','final_array','sum_of_expenses'));
    }

    public function PrintExpenseAnalysisByDate(Request $request)
    {
        $title='Category wise Expense Analysis From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y',strtotime($request->toDate));
        $expense_category=ExpenseCategory::all();
        $final_array=array();
        foreach($expense_category as $item)
        {
            $ids=ExpenseDetail::select('expense_id')->where('company_id',session('company_id'))->where('expense_category_id',$item->id)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->get();
            $ids = json_decode(json_encode($ids), true);
            $ids = array_column($ids,'expense_id');
            $temp=Expense::where('company_id',session('company_id'))->whereIn('id',$ids)->whereBetween('expenseDate', [$request->fromDate, $request->toDate])->sum('grandTotal');
            if($temp!=0)
            {
                $tmp_array=[
                    'category_name'=>$item->Name,
                    'total_expense'=>$temp,
                ];
                $final_array[]=$tmp_array;
            }
        }
        $total_exp=array_column($final_array,'total_expense');
        $sum_of_expenses=array_sum($total_exp);

        $footer=new CustomeFooter;
        $footer->footer();
        $pdf = new PDF();
        $pdf::setPrintHeader(false);

        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetAutoPageBreak(TRUE, 14);

        $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
        $pdf::SetFillColor(255,255,0);

        $pdf::SetFont('helvetica', '', 12);
        $html=$title;
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $pdf::SetFont('helvetica', 'B', 14);
        $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="250">Expense Category</th>
                <th align="center" width="100">Amount</th>
            </tr>';
        $pdf::SetFont('helvetica', '', 10);

        $final_array=$this->array_sort($final_array, 'total_expense', SORT_DESC);
        foreach($final_array as $single)
        {
            $html .='<tr><td align="left" width="250">'.($single['category_name']).'</td>
            <td align="right" width="100">'.((number_format($single['total_expense'],2,'.',','))).'</td></tr>';
        }
        $html.='</table>';
        $pdf::writeHTML($html, true, false, false, false, '');

        $pdf::SetFont('helvetica', '', 12);
        $html='Sum Of Expense => '.$sum_of_expenses;
        $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

        $pdf::lastPage();
        $time=time();
        $fileLocation = storage_path().'/app/public/report_files/';
        $fileNL = $fileLocation.'//'.$time.'.pdf';
        $pdf::Output($fileNL, 'F');
        $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
        $url=array('url'=>$url);
        return $url;
    }

    /* end analysis reports */

    public function GetInwardLoanStatement()
    {
        $financers = Financer::where('company_id',session('company_id'))->get();
        return view('admin.report.inward_loan_statement',compact('financers'));
    }

    public function PrintInwardLoanStatement(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='' && $request->financer_id!='')
        {
            $account_transactions = AccountTransaction::orderBy('createdDate','asc')->where('company_id',session('company_id'))->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('financer_id','=',$request->financer_id)->orderBy('createdDate','desc')->orderBy('id')->get();
            $sum_of_debit_before_from_date=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$request->financer_id)->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Debit');
            $sum_of_credit_before_from_date=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$request->financer_id)->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->sum('Credit');
            $closing_amount=$sum_of_credit_before_from_date-$sum_of_debit_before_from_date;
        }
        else
        {
            return FALSE;
        }
        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);

        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Financer Name : '.$request->financer_name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=' Opening Balance '.round($closing_amount,2);
            $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="60">Date</th>
                <th align="center" width="70">Ref#</th>
                <th align="center" width="150">Description</th>
                <th align="center" width="90">Debit</th>
                <th align="center" width="90">Credit</th>
                <th align="right" width="90">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $credit_total=0.0;
            $debit_total=0.0;
            $balance=$closing_amount;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Debit']!=0)
                {
                    $debit_total += $row[$i]['Debit'];
                    $balance = $balance - $row[$i]['Debit'];
                }
                elseif($row[$i]['Credit']!=0)
                {
                    $credit_total += $row[$i]['Credit'];
                    $balance = $balance + $row[$i]['Credit'];
                }
                else
                {
                    $balance += $row[$i]['Differentiate'];
                }
                if($i%2==0)
                {
                    $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                        <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                        <td align="left" width="150">'.$row[$i]['Description'].'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($balance,2,'.',',')).'</td>
                        </tr>';
                }
                else
                {
                    $html .='<tr>
                        <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                        <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                        <td align="left" width="150">'.$row[$i]['Description'].'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($balance,2,'.',',')).'</td>
                        </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($balance<0)
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="280" align="right" colspan="3">Total : </td>
                     <td width="90" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="90" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="280" align="right" colspan="3">Total : </td>
                     <td width="90" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function GetOutwardLoanStatement()
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        return view('admin.report.outward_loan_statement',compact('customers'));
    }

    public function PrintOutwardLoanStatement(Request $request)
    {
        if($request->fromDate!='' && $request->toDate!='' && $request->customer_id!='' )
        {
            $account_transactions = AccountTransaction::orderBy('createdDate','asc')->where('createdDate','>=',$request->fromDate)->where('createdDate','<=',$request->toDate)->where('customer_id','=',$request->customer_id)->where('Description', 'like', '%'.'Outgoing'.'%')->orderBy('createdDate','desc')->orderBy('id')->get();
            $sum_of_debit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->where('Description', 'like', '%'.'Outgoing'.'%')->sum('Debit');
            $sum_of_credit_before_from_date=AccountTransaction::where('customer_id',$request->customer_id)->whereNull('deleted_at')->where('createdDate','<',$request->fromDate)->where('Description', 'like', '%'.'Outgoing'.'%')->sum('Credit');
            $closing_amount=$sum_of_credit_before_from_date-$sum_of_debit_before_from_date;
        }
        else
        {
            return FALSE;
        }

        $row=json_decode(json_encode($account_transactions), true);
        $row=array_values($row);

        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='Customer Name : '.$request->customer_name;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html=' Opening Balance '.round($closing_amount,2);
            $pdf::writeHTMLCell(0, 0, ''    , '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="60">Date</th>
                <th align="center" width="70">Ref#</th>
                <th align="center" width="150">Description</th>
                <th align="center" width="90">Debit</th>
                <th align="center" width="90">Credit</th>
                <th align="right" width="90">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $credit_total=0.0;
            $debit_total=0.0;
            $balance=$closing_amount;
            for($i=0;$i<count($row);$i++)
            {
                if($row[$i]['Debit']!=0)
                {
                    $debit_total += $row[$i]['Debit'];
                    $balance = $balance - $row[$i]['Debit'];
                }
                elseif($row[$i]['Credit']!=0)
                {
                    $credit_total += $row[$i]['Credit'];
                    $balance = $balance + $row[$i]['Credit'];
                }
                else
                {
                    $balance += $row[$i]['Differentiate'];
                }
                if($i%2==0)
                {
                    $html .='<tr style="background-color: #e3e3e3;">
                        <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                        <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                        <td align="left" width="150">'.$row[$i]['Description'].'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($balance,2,'.',',')).'</td>
                        </tr>';
                }
                else
                {
                    $html .='<tr>
                        <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                        <td align="left" width="70">'.$row[$i]['referenceNumber'].'</td>
                        <td align="left" width="150">'.$row[$i]['Description'].'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                        <td align="right" width="90">'.(number_format($balance,2,'.',',')).'</td>
                        </tr>';
                }
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            if($balance<0)
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="280" align="right" colspan="3">Total : </td>
                     <td width="90" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="90" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }
            else
            {
                $html='<table border="0.5" cellpadding="0">';
                $html.= '
                 <tr>
                     <td width="280" align="right" colspan="3">Total : </td>
                     <td width="90" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($balance,2,'.',',').'</td>
                 </tr>';
                $pdf::SetFillColor(255, 0, 0);
                $html.='</table>';
                $pdf::writeHTML($html, true, false, false, false, '');
            }

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function GetLoginActivity()
    {
        $data=LoginLog::with('user')->where('company_id',session('company_id'))->where('user_id',session('user_id'))->orderBy('created_at')->get();
        if(!$data->first())
        {
            return redirect()->back()->with('error', 'NO RECORDS FOUND');
        }
        $row=json_decode(json_encode($data), true);
        $title='LOGIN ACTIVITY';

        $html = '<table class="display" id="report_table"><thead><tr><th align="center">User</th><th align="center">Login DateTime</th></tr></thead><tbody>';
        for($i=0;$i<count($row);$i++)
        {
            $html .='<tr><td align="center">'.($row[$i]['user']['name']).'</td><td align="center">'.(date('d-M-Y h:i:s', strtotime($row[$i]['created_at']))).'</td></tr>';
        }
        $html.='</tbody></table>';
        return view('admin.report.html_viewer',compact('html','title'))->render();
    }

    public function GetActivityReport()
    {
        $companies= Company::get();
        return view('admin.report.activity_report',compact('companies'));
    }

    public function PrintActivityReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!=''  && $request->filter=='all' && $request->company=='all')
        {
            $activity=UpdateNote::with(['user'=>function($q){$q->select('id','name');},'company'=>function($q){$q->select('id','Name');}])->select('id','RelationTable','RelationId','Description','UpdateDescription','user_id','company_id','created_at')->whereBetween('created_at', [$request->fromDate.' 00:00:00', $request->toDate.' 23:59:59'])->orderBy('created_at')->get();
        }
        else if ($request->fromDate!='' && $request->toDate!=''  && $request->filter!='all' && $request->company!='all')
        {
            if($request->filter=='update')
            {
                $activity=UpdateNote::with(['user'=>function($q){$q->select('id','name');},'company'=>function($q){$q->select('id','Name');}])->select('id','RelationTable','RelationId','Description','UpdateDescription','user_id','company_id','created_at')->where('company_id',$request->company)->whereNull('UpdateDescription')->whereBetween('created_at', [$request->fromDate.' 00:00:00', $request->toDate.' 23:59:59'])->orderBy('created_at')->get();
            }
            elseif($request->filter=='delete')
            {
                $activity=UpdateNote::with(['user'=>function($q){$q->select('id','name');},'company'=>function($q){$q->select('id','Name');}])->select('id','RelationTable','RelationId','Description','UpdateDescription','user_id','company_id','created_at')->where('company_id',$request->company)->whereNull('Description')->whereBetween('created_at', [$request->fromDate.' 00:00:00', $request->toDate.' 23:59:59'])->orderBy('created_at')->get();
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->company=='all' && $request->filter!='all')
        {
            if($request->filter=='update')
            {
                $activity=UpdateNote::with(['user'=>function($q){$q->select('id','name');},'company'=>function($q){$q->select('id','Name');}])->select('id','RelationTable','RelationId','Description','UpdateDescription','user_id','company_id','created_at')->whereNull('UpdateDescription')->whereBetween('created_at', [$request->fromDate.' 00:00:00', $request->toDate.' 23:59:59'])->orderBy('created_at')->get();
            }
            elseif($request->filter=='delete')
            {
                $activity=UpdateNote::with(['user'=>function($q){$q->select('id','name');},'company'=>function($q){$q->select('id','Name');}])->select('id','RelationTable','RelationId','Description','UpdateDescription','user_id','company_id','created_at')->whereNull('Description')->whereBetween('created_at', [$request->fromDate.' 00:00:00', $request->toDate.' 23:59:59'])->orderBy('created_at')->get();
            }
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->filter=='all' && $request->company!='all')
        {
            $activity=UpdateNote::with(['user'=>function($q){$q->select('id','name');},'company'=>function($q){$q->select('id','Name');}])->select('id','RelationTable','RelationId','Description','UpdateDescription','user_id','company_id','created_at')->where('company_id',$request->company)->whereBetween('created_at', [$request->fromDate.' 00:00:00', $request->toDate.' 23:59:59'])->orderBy('created_at')->get();
        }
        else
        {
            return FALSE;
        }

        if($activity->first())
        {
            $row=json_decode(json_encode($activity), true);
            //echo "<pre>";print_r($row);die;
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('L', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 12);
            $title='Activity Report - From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y', strtotime($request->toDate));
            //$time='Printed on : '.date('d-m-Y h:i:s');
            $pdf::setXY(10,4);
            $pdf::Cell(95,1,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 8);
            //$pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(5);

            $pdf::SetFont('helvetica', '', 9);
            $html = '<table border="0.5" cellpadding="1.5">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="80">Date</th>
                <th align="center" width="40">Action</th>
                <th align="center" width="70">User</th>
                <th align="center" width="70">Company</th>
                <th align="center" width="90">Table</th>
                <th align="center" width="130">REF#</th>
                <th align="center" width="265">Description</th>
            </tr>';
            for($i=0;$i<count($row);$i++)
            {
                $action='DELETE';
                if($row[$i]['UpdateDescription']==NULL)
                {
                    $action='UPDATE';
                    $description=$row[$i]['Description'];
                }
                else
                {
                    $description=$row[$i]['UpdateDescription'];
                }
                $ref=null;
                switch($row[$i]['RelationTable'])
                {
                    case "sales":
                        $data=SaleDetail::select('PadNumber')->withTrashed()->where('sale_id',$row[$i]['RelationId'])->first();
                        if(!empty($data))
                        {
                            $ref=$data['PadNumber'];
                        }
                        else
                        {
                            $ref='';
                        }
                        break;
                    case "purchases":
                        $data=PurchaseDetail::select('PadNumber')->withTrashed()->where('purchase_id',$row[$i]['RelationId'])->first();
                        $ref=$data['PadNumber'];
                        break;
                    case "expenses":
                        $data=Expense::select('referenceNumber')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['referenceNumber'];
                        break;
                    case "payment_receives":
                        $data=PaymentReceive::select('referenceNumber')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['referenceNumber'];
                        break;
                    case "customer_advances":
                        $data=CustomerAdvance::select('receiptNumber')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['receiptNumber'];
                        break;
                    case "supplier_payments":
                        $data=SupplierPayment::select('referenceNumber')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['referenceNumber'];
                        break;
                    case "supplier_advances":
                        $data=SupplierAdvance::select('receiptNumber')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['receiptNumber'];
                        break;
                    case "expense_categories":
                        $data=ExpenseCategory::select('Name')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['Name'];
                        break;
                    case "meter_readings":
                        $data=MeterReading::select('readingDate')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        //echo "<pre>";print_r($data);die;
                        if(!empty($data))
                        {
                            $ref=$data['readingDate'];
                        }
                        else
                        {
                            $ref='';
                        }
                        break;
                    case "deposits":
                        $data=Deposit::select('Reference')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['Reference'];
                        break;
                    case "withdrawals":
                        $data=Withdrawal::select('Reference')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['Reference'];
                        break;
                    case "vehicles":
                        $data=Vehicle::select('registrationNumber')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['registrationNumber'];
                        break;
                    case "customers":
                        $data=Customer::select('Name')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['Name'];
                        break;
                    case "suppliers":
                        $data=Supplier::select('Name')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['Name'];
                        break;
                    case "task_masters":
                        $data=TaskMaster::select('Name')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['Name'];
                        break;
                    case "tasks":
                        $data=Task::select('code')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['code'];
                        break;
                    case "file_managers":
                        $data=FileManager::select('FileCode')->withTrashed()->where('id',$row[$i]['RelationId'])->first();
                        $ref=$data['FileCode'];
                        break;
                }
                $username='NA';
                if(isset($row[$i]['user']['name']) && $row[$i]['user']['name']!='')
                {
                    $username=$row[$i]['user']['name'];
                }
                $html .='<tr>
                <td align="center" width="80">'.(date('d-m-y h:i:s', strtotime($row[$i]['created_at']))).'</td>
                <td align="center" width="40">'.($action).'</td>
                <td align="left" width="70">'.$username.'</td>
                <td align="center" width="70">'.$row[$i]['company']['Name'].'</td>
                <td align="center" width="90">'.$row[$i]['RelationTable'].'</td>
                <td align="left" width="130">'.$ref.'</td>
                <td align="left" width="265">'.($description).'</td>
                </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function GetLoginReport()
    {
        $users= User::get();
        return view('admin.report.login_report',compact('users'));
    }

    public function PrintLoginReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!=''  && $request->user_id=='all')
        {
            $data=LoginLog::with(['user'=>function($q){$q->select('id','name');},])->select('id','machineIp','created_at','user_id','company_id')->whereBetween('created_at', [$request->fromDate.' 00:00:00', $request->toDate.' 23:59:59'])->orderBy('user_id')->orderBy('created_at','desc')->get();
        }
        elseif($request->fromDate!='' && $request->toDate!='' && $request->user_id!='all')
        {
            $data=LoginLog::with(['user'=>function($q){$q->select('id','name');},])->select('id','machineIp','created_at','user_id','company_id')->where('user_id',$request->user_id)->whereBetween('created_at', [$request->fromDate.' 00:00:00', $request->toDate.' 23:59:59'])->orderBy('created_at','desc')->get();
        }
        else
        {
            return FALSE;
        }

        if($data->first())
        {
            $row=json_decode(json_encode($data), true);
            $row=array_values($row);
            //echo "<pre>";print_r($row);die;
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 12);
            $title='Login Report - From '.date('d-M-Y', strtotime($request->fromDate)).' To '.date('d-M-Y', strtotime($request->toDate));
            //$time='Printed on : '.date('d-m-Y h:i:s');
            $pdf::setXY(10,4);
            $pdf::Cell(95,1,$title,'',0,'L');
            $pdf::SetFont('helvetica', '', 8);
            //$pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(5);

            $pdf::SetFont('helvetica', '', 9);
            $html = '<table border="0.5" cellpadding="1.5">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="30">#</th>
                <th align="center" width="100">Date</th>
                <th align="center" width="50">User</th>
                <th align="center" width="100">IP</th>
                <th align="center" width="30">#</th>
                <th align="center" width="100">Date</th>
                <th align="center" width="50">User</th>
                <th align="center" width="100">IP</th>
            </tr>';
            for($i=0;$i<count($row);)
            {
                //echo "<pre>";print_r($row[$i]['user']['name']);die;
                $name='NA';
                if(isset($row[$i]['user']['name']))
                {
                    $name=$row[$i]['user']['name'];
                }
                $html .='<tr>
                <td align="left" width="30">'.($i).'</td>
                <td align="left" width="100">'.(date('d-m-y h:i:sA', strtotime($row[$i]['created_at']))).'</td>
                <td align="left" width="50">'.($name).'</td>
                <td align="left" width="100">'.$row[$i]['machineIp'].'</td>';
                if(isset($row[$i+1]))
                {
                    $html .='
                <td align="left" width="30">'.($i+1).'</td>
                <td align="left" width="100">'.(date('d-m-y h:i:sA', strtotime($row[$i+1]['created_at']))).'</td>
                <td align="left" width="50">'.($name).'</td>
                <td align="left" width="100">'.$row[$i+1]['machineIp'].'</td>';
                }
                $html.='</tr>';
                $i=$i+2;
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function GetInwardLoanSummary()
    {
        return view('admin.report.inward_loan_summary');
    }

    public function PrintInwardLoanSummary(Request $request)
    {
        $result_array=array();
        $financers=Financer::select('id','Name','Mobile')->where('company_id',session('company_id'))->get();
        foreach ($financers as $financer)
        {
            //get diff of total debit and credit column
            $sum_of_credit=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$financer->id)->whereNull('deleted_at')->where('Description', 'like', '%'.'Inward'.'%')->sum('Credit');
            $sum_of_debit=AccountTransaction::where('company_id',session('company_id'))->where('financer_id',$financer->id)->whereNull('deleted_at')->where('Description', 'like', '%'.'Inward'.'%')->sum('Debit');
            $diff=$sum_of_credit-$sum_of_debit;
            if($diff!=0)
            {
                $temp=array('Name'=>$financer->Name,'Mobile'=>$financer->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
        }
        $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
        $row=array_values($row);
        //echo "<pre>";print_r($row);die;

        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='INWARD LOAN SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s A');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
        <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
            <th align="center" width="50">S.No</th>
            <th align="center" width="200">Financer Name</th>
            <th align="center" width="200">Cell</th>
            <th align="right" width="80">Amount</th>
        </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            $total_advances=0.0;
            $pos_arr=array(); $neg_arr=array();
            foreach($row as $key=>$value)
            {
                ($value['Differentiate']<0) ?  $neg_arr[]=$value : $pos_arr[]=$value;
            }
            for($i=0;$i<count($pos_arr);$i++)
            {
                $total_balance+=$pos_arr[$i]['Differentiate'];
                $html .='<tr>
            <td align="center" width="50">'.($i+1).'</td>
            <td align="left" width="200">'.($pos_arr[$i]['Name']).'</td>
            <td align="left" width="200">'.($pos_arr[$i]['Mobile']).'</td>
            <td align="right" width="80">'.(number_format($pos_arr[$i]['Differentiate'],2,'.',',')).'</td>
            </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            $html = '<table border="0" cellpadding="1"><tr color="red" style="font-weight: bold;font-size: 12px;">
                     <td width="450" align="right" colspan="3"> Total : </td>
                     <td width="80" align="right">'. number_format($total_balance, 2, '.', ',') .'</td>
                 </tr>';
            $html .= '</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function GetOutwardLoanSummary()
    {
        return view('admin.report.outward_loan_summary');
    }

    public function PrintOutwardLoanSummary(Request $request)
    {
        $result_array=array();
        $customers=Customer::select('id','Name','Mobile')->where('company_id',session('company_id'))->get();
        foreach ($customers as $customer)
        {
            //get diff of total debit and credit column
            $sum_of_credit=AccountTransaction::where('company_id',session('company_id'))->where('customer_id',$customer->id)->whereNull('deleted_at')->where('Description', 'like', '%'.'Outward'.'%')->sum('Credit');
            $sum_of_debit=AccountTransaction::where('company_id',session('company_id'))->where('customer_id',$customer->id)->whereNull('deleted_at')->where('Description', 'like', '%'.'Outward'.'%')->sum('Debit');
            $diff=$sum_of_credit-$sum_of_debit;
            if($diff!=0)
            {
                $temp=array('Name'=>$customer->Name,'Mobile'=>$customer->Mobile,'Differentiate'=>$diff);
                $result_array[]=$temp;
                unset($temp);
            }
        }
        $row=$this->array_sort($result_array, 'Differentiate', SORT_DESC);
        $row=array_values($row);
        //echo "<pre>";print_r($row);die;

        if(!empty($row))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 15);
            $html='OUTWARD LOAN SUMMARY';
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $html='Date : '.date('d-m-Y h:i:s A');
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'R', true);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
        <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
            <th align="center" width="50">S.No</th>
            <th align="center" width="200">Customer Name</th>
            <th align="center" width="200">Cell</th>
            <th align="right" width="80">Amount</th>
        </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $total_balance=0.0;
            $total_advances=0.0;
            $pos_arr=array(); $neg_arr=array();
            foreach($row as $key=>$value)
            {
                ($value['Differentiate']<0) ?  $neg_arr[]=$value : $pos_arr[]=$value;
            }
            for($i=0;$i<count($pos_arr);$i++)
            {
                $total_balance+=$pos_arr[$i]['Differentiate'];
                $html .='<tr>
            <td align="center" width="50">'.($i+1).'</td>
            <td align="left" width="200">'.($pos_arr[$i]['Name']).'</td>
            <td align="left" width="200">'.($pos_arr[$i]['Mobile']).'</td>
            <td align="right" width="80">'.(number_format($pos_arr[$i]['Differentiate'],2,'.',',')).'</td>
            </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');
            $html = '<table border="0" cellpadding="1"><tr color="red" style="font-weight: bold;font-size: 12px;">
                     <td width="450" align="right" colspan="3"> Total : </td>
                     <td width="80" align="right">'. number_format($total_balance, 2, '.', ',') .'</td>
                 </tr>';
            $html .= '</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function GetPaymentLedger()
    {
        return view('admin.report.payment_ledger');
    }

    public function PrintPaymentLedger(Request $request)
    {
        if($request->ledger_party=='customers')
        {
            if($request->fromDate!='' && $request->toDate!='' && $request->filter=='all')
            {
                if($request->parties=='all')
                {
                    $customer_advance_data=CustomerAdvance::with(['customer'=>function($q){$q->select('id','Name');},])->select('TransferDate as date','paymentType as type','Amount as amount','customer_id','receiptNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('isPushed',1)->get();
                    $customer_payment_data=PaymentReceive::with(['customer'=>function($q){$q->select('id','Name');},])->select('transferDate as date','payment_type as type','paidAmount as amount','referenceNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('transferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('isPushed',1)->get();
                }
                else
                {
                    $customer_advance_data=CustomerAdvance::with(['customer'=>function($q){$q->select('id','Name');},])->select('TransferDate as date','paymentType as type','Amount as amount','customer_id','receiptNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('isPushed',1)->where('customer_id',$request->parties)->get();
                    $customer_payment_data=PaymentReceive::with(['customer'=>function($q){$q->select('id','Name');},])->select('transferDate as date','payment_type as type','paidAmount as amount','referenceNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('transferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('isPushed',1)->where('customer_id',$request->parties)->get();
                }
            }
            else if($request->fromDate!='' && $request->toDate!='' && $request->filter!='all' )
            {
                if($request->parties=='all')
                {
                    $customer_advance_data=CustomerAdvance::with(['customer'=>function($q){$q->select('id','Name');},])->select('TransferDate as date','paymentType as type','Amount as amount','customer_id','receiptNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('paymentType',$request->filter)->where('isPushed',1)->get();
                    $customer_payment_data=PaymentReceive::with(['customer'=>function($q){$q->select('id','Name');},])->select('transferDate as date','payment_type as type','paidAmount as amount','referenceNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('transferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('payment_type',$request->filter)->where('isPushed',1)->get();
                }
                else
                {
                    $customer_advance_data=CustomerAdvance::with(['customer'=>function($q){$q->select('id','Name');},])->select('TransferDate as date','paymentType as type','Amount as amount','customer_id','receiptNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('paymentType',$request->filter)->where('isPushed',1)->where('customer_id',$request->parties)->get();
                    $customer_payment_data=PaymentReceive::with(['customer'=>function($q){$q->select('id','Name');},])->select('transferDate as date','payment_type as type','paidAmount as amount','referenceNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('transferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('payment_type',$request->filter)->where('isPushed',1)->where('customer_id',$request->parties)->get();
                }
            }
            $row=json_decode(json_encode($customer_advance_data), true);
            $row1=json_decode(json_encode($customer_payment_data), true);
            $all_data=array_merge($row,$row1);
        }
        else if($request->ledger_party=='suppliers')
        {
            if($request->fromDate!='' && $request->toDate!='' && $request->filter=='all')
            {
                if($request->parties=='all')
                {
                    $supplier_advance_data=SupplierAdvance::with(['supplier'=>function($q){$q->select('id','Name');},])->select('TransferDate as date','paymentType as type','Amount as amount','supplier_id','receiptNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('isPushed',1)->get();
                    $supplier_payment_data=SupplierPayment::with(['supplier'=>function($q){$q->select('id','Name');},])->select('transferDate as date','payment_type as type','paidAmount as amount','referenceNumber as ref','supplier_id','Description')->where('company_id',session('company_id'))->whereBetween('transferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('isPushed',1)->get();
                }
                else
                {
                    $supplier_advance_data=SupplierAdvance::with(['supplier'=>function($q){$q->select('id','Name');},])->select('TransferDate as date','paymentType as type','Amount as amount','supplier_id','receiptNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('isPushed',1)->where('supplier_id',$request->parties)->get();
                    $supplier_payment_data=SupplierPayment::with(['supplier'=>function($q){$q->select('id','Name');},])->select('transferDate as date','payment_type as type','paidAmount as amount','referenceNumber as ref','supplier_id','Description')->where('company_id',session('company_id'))->whereBetween('transferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('isPushed',1)->where('supplier_id',$request->parties)->get();
                }
            }
            else if($request->fromDate!='' && $request->toDate!='' && $request->filter!='all' )
            {
                if($request->parties=='all')
                {
                    $supplier_advance_data=SupplierAdvance::with(['supplier'=>function($q){$q->select('id','Name');},])->select('TransferDate as date','paymentType as type','Amount as amount','supplier_id','receiptNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('paymentType',$request->filter)->where('isPushed',1)->get();
                    $supplier_payment_data=SupplierPayment::with(['supplier'=>function($q){$q->select('id','Name');},])->select('transferDate as date','payment_type as type','paidAmount as amount','referenceNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('transferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('payment_type',$request->filter)->where('isPushed',1)->get();
                }
                else
                {
                    $supplier_advance_data=SupplierAdvance::with(['supplier'=>function($q){$q->select('id','Name');},])->select('TransferDate as date','paymentType as type','Amount as amount','supplier_id','receiptNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('TransferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('paymentType',$request->filter)->where('isPushed',1)->where('supplier_id',$request->parties)->get();
                    $supplier_payment_data=SupplierPayment::with(['supplier'=>function($q){$q->select('id','Name');},])->select('transferDate as date','payment_type as type','paidAmount as amount','referenceNumber as ref','Description')->where('company_id',session('company_id'))->whereBetween('transferDate',[$request->fromDate,$request->toDate])->where('isActive',1)->where('payment_type',$request->filter)->where('isPushed',1)->where('supplier_id',$request->parties)->get();
                }
            }
            $row=json_decode(json_encode($supplier_advance_data), true);
            $row1=json_decode(json_encode($supplier_payment_data), true);
            $all_data=array_merge($row,$row1);
            //need to sort full array by date
        }
        else
        {
            return FALSE;
        }

        if(!empty($all_data))
        {
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', 'B', 10);
            $html='Payment Ledger';
            $pdf::Cell(200,5,$html,'',0,'C');
            $pdf::Ln(6);
            $html='Party Name : '.$request->party_name;
            $date='From '.date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::Cell(95,5,$html,'',0,'L');
            $pdf::Cell(95,5,$date,'',0,'R');
            $pdf::Ln(6);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="1">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="60">Date</th>
                <th align="center" width="70">Ref#</th>
                <th align="center" width="150">Name</th>
                <th align="center" width="175">Desc.</th>
                <th align="center" width="30">Type</th>
                <th align="center" width="55">Amount</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);
            $row=$all_data;
            $amount_total=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $name='';
                if(isset($row[$i]['customer']))
                {
                    $name=$row[$i]['customer']['Name'];
                }
                else if(isset($row[$i]['supplier']))
                {
                    $name=$row[$i]['supplier']['Name'];
                }
                $html.='<tr>
                    <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['date']))).'</td>
                    <td align="left" width="70">'.$row[$i]['ref'].'</td>
                    <td align="left" width="150">'.$name.'</td>
                    <td align="left" width="175">'.($row[$i]['Description']).'</td>
                    <td align="center" width="30">'.($row[$i]['type']).'</td>
                    <td align="right" width="55">'.(number_format($row[$i]['amount'],2,'.',',')).'</td>
                    </tr>';
                $amount_total+=$row[$i]['amount'];
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);
            $html='<table border="0" cellpadding="0">';
            $html.= '
                 <tr color="red">
                     <td width="445" align="right" colspan="3">TOTAL : </td>
                     <td width="90" align="right">'.number_format($amount_total,2,'.',',').'</td>
                 </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }

    public function GetYearlyProfitAndLoss()
    {
        return view('admin.report.get_yearly_profit_loss');
    }

    public function PrintYearlyProfitAndLoss(Request $request)
    {
        if($request->yearpicker!='')
        {
            // get the all month of year up until current month
            $begin = new DateTime($request->fromDate);
            $end   = new DateTime($request->toDate);
            $all_dates=array();
            $final_array=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");
                $all_dates[]=$date=$i->format("Y-m-d");
                $qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('createdDate','=',$date)->where('deleted_at','=',NULL)->where('isActive','=',1)->sum('Quantity');
                $tmp_array=array('Date'=>$date,'Quantity'=>$qty);
                $final_array[]=$tmp_array;
            }
        }
        else
        {
            return FALSE;
        }

        if(!empty($final_array))
        {
            $company_title='WATAN PHARMA LLP.';
            $company_address='MUSSAFAH M13,PLOT 100, ABU DHABI,UAE';
            $company_email='Email : info@alhamood.ae';
            $company_mobile='Mobile : +971-25550870  +971-557383866  +971-569777861';

            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage('', 'A4');
            $pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);

            $pdf::SetFont('helvetica', '', 12);
            $html=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $pdf::SetFont('helvetica', '', 12);
            $title='DAILY PURCHASE QUANTITY REPORT';
            $time='Date : '.date('d-m-Y h:i:s A');

            $pdf::Cell(95,5,$title,'',0,'L');
            $pdf::Cell(95,5,$time,'',0,'R');
            $pdf::Ln(8);

            $row=$final_array;

            $pdf::SetFont('helvetica', 'B', 8);
            $html = '<table border="0.5" cellpadding="1">
                <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                    <th align="center" width="100">Date</th>
                    <th align="center" width="100">Quantity</th>
                </tr>';
            $pdf::SetFont('helvetica', '', 12);

            $qty_sum=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $qty_sum+=$row[$i]['Quantity'];
                if($i%2==1)
                {
                    $html .='<tr style="background-color: #aba9a9">
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
                else
                {
                    $html .='<tr>
                    <td align="center" width="100">'.(date('d-M-Y', strtotime($row[$i]['Date']))).'</td>
                    <td align="right" width="100">'.number_format($row[$i]['Quantity'],2,'.',',').'</td>
                    </tr>';
                }
            }

            $html.= '<tr color="red">
                     <td width="100" align="right">Total</td>
                     <td width="100" align="right">'.number_format($qty_sum,2,'.',',').'</td>
                 </tr>';

            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();

            $time=time();
            $name='PURCHASE_QTY_SUMMARY_'.date('d-m-Y', strtotime($request->fromDate)).'_To_'.date('d-m-Y', strtotime($request->toDate)).'_'.$time;
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$name.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$name.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else{
            return false;
        }
    }

    public function GetInventoryReport()
    {
        return view('admin.report.get_inventory_report');
    }

    public function PrintInventoryReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='')
        {
            $total_purchase_qty=PurchaseDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->where('createdDate','<',$request->fromDate)->sum('Quantity');
            $total_sales_qty=SaleDetail::where('company_id','=',session('company_id'))->where('deleted_at','=',NULL)->where('createdDate','<',$request->fromDate)->sum('Quantity');
            $opening_stock_qty=$total_purchase_qty-$total_sales_qty;
            //echo "<pre>";print_r($opening_stock_qty);die;

            $begin = new DateTime($request->fromDate);
            $end   = new DateTime($request->toDate);
            $final_array=array();
            for($i = $begin; $i <= $end; $i->modify('+1 day'))
            {
                $date=$i->format("Y-m-d");

                $today_purchase_sum=PurchaseDetail::where('company_id',session('company_id'))->where('createdDate',$date)->where('isActive',1)->whereNull('deleted_at')->sum('Quantity');
                $today_sales_sum=SaleDetail::where('company_id',session('company_id'))->where('createdDate',$date)->where('isActive',1)->whereNull('deleted_at')->sum('Quantity');

                $tmp_array=array('Date'=>$date,'purchase'=>$today_purchase_sum,'sales'=>$today_sales_sum);
                $final_array[]=$tmp_array;
            }
            //echo "<pre>";print_r($final_array);die;
            $footer=new CustomeFooter;
            $footer->footer();
            $pdf = new PDF();
            $pdf::setPrintHeader(false);

            $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf::SetAutoPageBreak(TRUE, 14);

            $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
            $pdf::SetFillColor(255,255,0);
            $row=$final_array;

            $pdf::SetFont('helvetica', '', 15);
            $date_heading=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));
            $html='INVENTORY : '.$date_heading;
            $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'C', true);

            $opening=' Opening Stock : '.round($opening_stock_qty,2);
            $pdf::Cell(50,5,'','',0,'L');
            $pdf::Cell(95,5,$opening,'',0,'L');
            $pdf::Ln(8);

            $pdf::SetFont('helvetica', 'B', 14);
            $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);font-weight: bold;">
                <th align="center" width="60">Date</th>
                <th align="right" width="80">Purchase</th>
                <th align="right" width="80">Sales</th>
                <th align="right" width="90">Closing</th>
            </tr>';
            $pdf::SetFont('helvetica', '', 10);

            $stock=$opening_stock_qty;
            $purchase_total=0.0;
            $sales_total=0.0;
            for($i=0;$i<count($row);$i++)
            {
                $purchase_total += $row[$i]['purchase'];
                $sales_total += $row[$i]['sales'];
                $stock = $stock + $row[$i]['purchase'] - $row[$i]['sales'];

                $html .='<tr>
                <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['Date']))).'</td>
                <td align="right" width="80">'.($row[$i]['purchase']).'</td>
                <td align="right" width="80">'.($row[$i]['sales']).'</td>
                <td align="right" width="90">'.number_format($stock,2,'.',',').'</td>
                </tr>';
            }
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::SetFont('helvetica', 'B', 13);

            $html='<table border="0.5" cellpadding="2">';
            $html.= '
             <tr>
             <td width="60" align="right" colspan="2">Total : </td>
             <td width="80" align="right">'.number_format($purchase_total,2,'.',',').'</td>
             <td width="80" align="right">'.number_format($sales_total,2,'.',',').'</td>
             <td width="90" align="right">'.number_format($stock,2,'.',',').'</td>
             </tr>';
            $pdf::SetFillColor(255, 0, 0);
            $html.='</table>';
            $pdf::writeHTML($html, true, false, false, false, '');

            $pdf::lastPage();
            $time=time();
            $fileLocation = storage_path().'/app/public/report_files/';
            $fileNL = $fileLocation.'//'.$time.'.pdf';
            $pdf::Output($fileNL, 'F');
            $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
            $url=array('url'=>$url);
            return $url;
        }
        else
        {
            return FALSE;
        }
    }
}
