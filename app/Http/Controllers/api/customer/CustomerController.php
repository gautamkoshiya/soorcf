<?php

namespace App\Http\Controllers\api\customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sales\SalesResource;
use App\MISC\ServiceResponse;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\CustomerAdvanceBooking;
use App\Models\LoanMaster;
use App\Models\PaymentReceive;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PDF;

class CustomerController extends Controller
{
    protected $userResponse;
    public function __construct(ServiceResponse $serviceResponse)
    {
        $this->userResponse = $serviceResponse;
    }

    public function customer_login(Request $request)
    {
        try
        {
            if($request['login_email']!='' and $request['password']!='')
            {
                $result=Customer::select('id','Name','login_email','app_access','Representative','Mobile','Address','Email','openingBalanceAsOfDate')->where('login_email','=',$request['login_email'])->where('password','=',md5($request['password']))->first();
                if($result)
                {
                    Customer::where('id', $result->id)->update(array('password_last_updated' => date('Y-m-d h:i:s')));
                    return $this->userResponse->LoginSuccess( null ,$result,null ,'Login Successful');
                }
                else
                {
                    Return $this->userResponse->LoginFailed();
                }
            }
            else
            {
                Return $this->userResponse->LoginFailed();
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function customer_logout(Request $request)
    {
        try
        {
            if($request['customer_id']!='')
            {
                $result = Customer::find($request['customer_id']);
                if($result)
                {
                    Customer::where('id', $result->id)->update(array('password_last_updated' => date('0000-00-00 00:00:00')));
                    return $this->userResponse->LogOut();
                }
                else
                {
                    return $this->userResponse->Exception('Something is wrong, failed to logOut');
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong, failed to logOut');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function customer_change_password(Request $request)
    {
        try
        {
            if($request['login_email']!='' and $request['currentPassword']!='' and $request['password']!='')
            {
                $result=Customer::select('id','Name','login_email')->where('login_email','=',$request['login_email'])->where('password','=',md5($request['currentPassword']))->first();
                if($result)
                {
                    Customer::where('id', $result->id)->update(array('password_last_updated' => date('Y-m-d h:i:s'),'password'=>md5($request['password'])));
                    return $this->userResponse->Success($result);
                }
                else
                {
                    return $this->userResponse->Exception('email id or password not matching.');
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_vehicles(Request $request)
    {
        try
        {
            if($request['customer_id']!='')
            {
                $result=Vehicle::select('id','registrationNumber','isActive')->where('customer_id','=',$request['customer_id'])->get();
                if($result)
                {
                    return $this->userResponse->Success($result);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_purchase(Request $request,$page_no,$page_size)
    {
        try
        {
            if($request['customer_id']!='' &&  $request['fromDate']=='' && $request['toDate']=='')
            {
                $result=Sale::select('id','customer_id','SaleDate','grandTotal','paidBalance','remainingBalance','IsPaid')->where('isActive','=',1)->where('customer_id','=',$request['customer_id'])->with(['sale_details','sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');}])->get()->sortDesc()->forPage($page_no,$page_size);
                $final_array=array();
                //=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price','rowTotal','rowVatAmount','rowSubTotal');}
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            elseif($request['customer_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                $result=Sale::select('id','customer_id','SaleDate','grandTotal','paidBalance','remainingBalance','IsPaid')->where('isActive','=',1)->where('customer_id','=',$request['customer_id'])->whereBetween('SaleDate', [$request['fromDate'], $request['toDate']])->with(['sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');}])->get()->sortDesc()->forPage($page_no,$page_size);
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_purchase_by_vehicle(Request $request)
    {
        try
        {
            if($request['customer_id']!='' && $request['vehicle_id']!='' &&  $request['fromDate']=='' && $request['toDate']=='')
            {
                $result=Sale::select('id','customer_id','SaleDate','grandTotal','paidBalance','remainingBalance','IsPaid')->where('isActive','=',1)->where('customer_id','=',$request['customer_id'])->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','PadNumber','Quantity','Price');},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');}])->get()->sortDesc();
                $final_array=array();
                foreach ($result as $item)
                {
                    if($item->sale_details[0]->vehicle->id==$request['vehicle_id'])
                    {
                        $final_array[]=$item;
                    }
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            elseif($request['customer_id']!='' && $request['vehicle_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                $result=Sale::select('id','customer_id','SaleDate','grandTotal','paidBalance','remainingBalance','IsPaid')->where('isActive','=',1)->where('customer_id','=',$request['customer_id'])->whereBetween('SaleDate', [$request['fromDate'], $request['toDate']])->with('sale_details')->get()->sortDesc();
                $final_array=array();
                foreach ($result as $item)
                {
                    if($item->sale_details[0]->vehicle->id==$request['vehicle_id'])
                    {
                        $final_array[]=$item;
                    }
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_account_status(Request $request)
    {
        try
        {
            if($request['customer_id']!='')
            {
                $amount=0.00;
                $customers=Customer::where('id',$request['customer_id'])->get();
                foreach ($customers as $customer)
                {
                    $credit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Credit');
                    $debit_sum=AccountTransaction::where('customer_id',$customer->id)->whereNull('updateDescription')->sum('Debit');
                    $amount=$debit_sum-$credit_sum;
                }
                return $this->userResponse->Success($amount);
            }
            else
            {
                return $this->userResponse->Exception('customer not found.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_account_statement_by_vehicle(Request $request)
    {
        try
        {
            if($request['customer_id']!='' && $request['vehicle_id']!='' && $request['fromDate']!='' && $request['toDate']!='')
            {
                if($request['vehicle_id']=='all')
                {
                    $sales=Sale::where('customer_id',$request['customer_id'])->where('isActive', '=', 1)->where('SaleDate','>=',$request['fromDate'])->where('SaleDate','<=',$request['toDate'])->first();
                }
                else
                {
                    $sales=Sale::where('customer_id',$request['customer_id'])->where('isActive', '=', 1)->where('SaleDate','>=',$request['fromDate'])->where('SaleDate','<=',$request['toDate'])->first();
                }

                if($sales->first())
                {
                    $row=json_decode(json_encode($sales), true);
                    $pdf = new PDF();
                    $pdf::setPrintHeader(false);
                    $pdf::setPrintFooter(false);
                    $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                    $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                    $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
                    $pdf::SetFillColor(255,255,0);

                    $pdf::SetFont('helvetica', '', 15);

                    $date='';
                    $customer_name=Customer::select('Name')->where('id',$request['customer_id'])->first();
                    $html='Company Name : '.$customer_name->Name;

                    $pdf::Cell(95,5,$html,'',0,'L');
                    $pdf::Cell(95,5,$date,'',0,'R');
                    $pdf::Ln(6);

                    $booking_status_string='';
                    $booking_rem_quantity=CustomerAdvanceBooking::where('customer_id',$request['customer_id'])->sum('remainingQuantity');

                    if($booking_rem_quantity>0)
                    {
                        $booking_status_string=' Advance booked Quantity : '.$booking_rem_quantity;
                    }
                    else
                    {
                        $sum_of_overfilled_qty=SaleDetail::where('customer_id',$request['customer_id'])->whereNull('deleted_at')->where('isActive',1)->whereNotNull('booking_shortage')->sum('Quantity');
                        if($sum_of_overfilled_qty>0)
                        {
                            $booking_status_string='Overfilled Quantity is : '.$sum_of_overfilled_qty;
                        }
                    }
                    if($booking_status_string!='')
                    {
                        $pdf::SetFont('helvetica', 'B', 10);
                        $html='ADVANCE BOOKING REPORT';
                        $date=date('d-m-Y', strtotime($request['fromDate'])).' To '.date('d-m-Y', strtotime($request['toDate']));

                        $pdf::Cell(95,5,$html,'',0,'L');
                        $pdf::Cell(95,5,$date,'',0,'R');
                        $pdf::Ln(6);

                        $booking=CustomerAdvanceBooking::with(['booking_details'])->where('customer_id',$request['customer_id'])->whereBetween('BookingDate',[$request['fromDate'],$request['toDate']])->get();
                        if($booking->first())
                        {
                            $pdf::SetFont('helvetica', '', 8);
                            $row=json_decode(json_encode($booking), true);
                            if(!empty($row))
                            {
                                //echo "<pre>";print_r($row);die;
                                //booking heading
                                $html = '<table border="0.5" cellpadding="1"><tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); font-weight: bold;font-size: 10px;">
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
                    }
                    $pdf::Ln(6);

                    $pdf::SetFont('helvetica', 'B', 10);
                    $html='SALES REPORT BY VEHICLE';
                    $date=date('d-m-Y', strtotime($request['fromDate'])).' To '.date('d-m-Y', strtotime($request['toDate']));

                    $pdf::Cell(95,5,$html,'',0,'L');
                    $pdf::Cell(95,5,$date,'',0,'R');
                    $pdf::Ln(6);

                    $pdf::SetFont('helvetica', 'B', 8);

                    $pdf::SetFont('helvetica', '', 8);
                    if($request['vehicle_id']==='all')
                    {
                        $customer_all_vehicle=Vehicle::where('customer_id',$request['customer_id'])->get();
                        $customer_all_vehicle=$customer_all_vehicle->sortBy('registrationNumber');
                        foreach($customer_all_vehicle as $single)
                        {
                            $sales_ids=SaleDetail::select('sale_id')->where('vehicle_id',$single->id)->whereBetween('createdDate',[$request['fromDate'],$request['toDate']])->get();
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
                        $advances=CustomerAdvance::where('customer_id',$request['customer_id'])->where('isPushed',1)->where('remainingBalance','>',0)->whereBetween('TransferDate',[$request['fromDate'],$request['toDate']])->get();
                        if($advances->first())
                        {
                            $html='Advances';
                            $pdf::Cell(95,5,$html,'',0,'L');
                            $pdf::Ln(6);

                            $html = '<table border="0.5" cellpadding="1" style="font-weight: bold;">
                        <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
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
                        $loans=LoanMaster::where('customer_id',$request['customer_id'])->where('isPushed',1)->where('outward_isPaid',0)->where('loanType',0)->get();
                        if($loans->first())
                        {
                            $html='Loans';
                            $pdf::Cell(95,5,$html,'',0,'L');
                            $pdf::Ln(6);

                            $html = '<table border="0.5" cellpadding="1">
                        <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);font-weight: bold;">
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
                        $sum_of_debit_before_from_date=AccountTransaction::where('customer_id',$request['customer_id'])->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request['fromDate'])->sum('Debit');
                        $sum_of_credit_before_from_date=AccountTransaction::where('customer_id',$request['customer_id'])->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request['fromDate'])->sum('Credit');
                        $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;

                        //find sum of payments and advances between to and from date
                        $total_paid_amount_prev=PaymentReceive::where('customer_id',$request['customer_id'])->where('isPushed',1)->whereNull('deleted_at')->whereBetween('transferDate',[$request['fromDate'],$request['toDate']])->sum('paidAmount');
                        $total_advance_amount_prev=CustomerAdvance::where('customer_id',$request['customer_id'])->where('isPushed',1)->whereNull('deleted_at')->whereBetween('transferDate',[$request['fromDate'],$request['toDate']])->sum('Amount');
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

                        //$total_advance_amount=CustomerAdvance::where('customer_id',$request['customer_id'])->where('isPushed',1)->whereNull('deleted_at')->whereBetween('TransferDate',[$request['fromDate'],$request['toDate']])->sum('Amount');
                        if($request['customer_id']==157)
                        {
                            // spark
                            $total_advance_amount=CustomerAdvance::where('customer_id',$request['customer_id'])->where('isPushed',1)->whereNull('deleted_at')->sum('remainingBalance');
                        }
                        else
                        {
                            $total_advance_amount=CustomerAdvance::where('customer_id',$request['customer_id'])->where('isPushed',1)->whereNull('deleted_at')->whereBetween('TransferDate',[$request['fromDate'],$request['toDate']])->sum('remainingBalance');
                        }


                        $credit_sum=AccountTransaction::where('customer_id',$request['customer_id'])->whereNull('updateDescription')->sum('Credit');
                        $debit_sum=AccountTransaction::where('customer_id',$request['customer_id'])->whereNull('updateDescription')->sum('Debit');
                        $diff=$debit_sum-$credit_sum;

                        $prev_from_sale=Sale::where('customer_id',$request['customer_id'])->whereNull('deleted_at')->where('SaleDate','<',$request['fromDate'])->sum('remainingBalance');
                        //$prev_from_sale=Sale::where('customer_id',$request['customer_id'])->whereNull('deleted_at')->where('SaleDate','<',$request['fromDate'])->where('isActive',1)->sum('remainingBalance');

                        /*$html = '<table border="0" cellpadding="0">
                        <tr style="font-weight: bold;font-size: 10px;">
                             <td width="455" align="right"><u>PREVIOUS</u></td>
                             <td width="80" align="right"><u>'. number_format($closing_amount-$total_paid_amount, 2, '.', ',') .'</u></td>
                        </tr>';*/
                        $prev=$prev_from_sale;
                        /*$html = '<table border="0" cellpadding="0">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="455" align="right"><u>PREVIOUS</u></td>
                         <td width="80" align="right"><u>'. number_format($prev, 2, '.', ',') .'</u></td>
                    </tr>';*/
                        $html = '<table border="0" cellpadding="0">
                    <tr style="font-weight: bold;font-size: 10px;">
                         <td width="240" align="right"><u>'.$booking_status_string.'</u></td>
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
                    }
                    else
                    {
                        $sales_ids=SaleDetail::select('sale_id')->where('vehicle_id',$request['vehicle_id'])->whereBetween('createdDate',[$request['fromDate'],$request['toDate']])->get();
                        $sales_ids_array=json_decode(json_encode($sales_ids), true);
                        $sales_ids=array_column($sales_ids_array,'sale_id');
                        $sales=Sale::with(['sale_details','sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');}])->whereIn('id',$sales_ids)->where('isActive', '=', 1)->get();

                        $html = '<table border="0.5" cellpadding="3">
                    <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255); font-weight: bold;font-size: 10px;">
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
                        $level_one_qty_sum=0;
                        $level_one_total_sum=0;
                        $level_one_sub_total_sum=0;
                        $level_one_vat_sum=0;
                        $level_one_paid_sum=0;
                        $level_one_balance_sum=0;

                        foreach ($sales as $single)
                        {
                            $current_vat_amount = $single->sale_details[0]->rowTotal * $single->sale_details[0]->VAT/100;
                            $level_one_qty_sum+=$single->sale_details[0]->Quantity;
                            $level_one_total_sum+=$single->sale_details[0]->rowTotal;
                            $level_one_sub_total_sum+=$single->sale_details[0]->rowSubTotal;
                            $level_one_vat_sum+=$current_vat_amount;
                            $level_one_paid_sum+=$single->paidBalance;
                            $level_one_balance_sum+=$single->remainingBalance;

                            $html .= '<tr>
                                <td align="center" width="60">'.($single->sale_details[0]->vehicle->registrationNumber).'</td>
                                <td align="center" width="70">'.($single->sale_details[0]->PadNumber).'</td>
                                <td align="center" width="70">'.(date('d-M-Y', strtotime($single->SaleDate))).'</td>
                                <td align="right" width="40">'.($single->sale_details[0]->Quantity).'</td>
                                <td align="center" width="40">'.($single->sale_details[0]->Price).'</td>
                                <td align="right" width="50">'.($single->sale_details[0]->rowTotal).'</td>
                                <td align="right" width="40">'.(number_format($current_vat_amount, 2, '.', ',')).'</td>
                                <td align="right" width="55">'.($single->sale_details[0]->rowSubTotal).'</td>
                                <td align="right" width="55">'.($single->paidBalance).'</td>
                                <td align="right" width="55">'.($single->remainingBalance).'</td>
                            </tr>';
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
                        $pdf::SetFillColor(255, 0, 0);
                        $html .= '</table>';

                        $pdf::writeHTML($html, true, false, false, false, '');
                    }

                    $pdf::lastPage();
                    $time=time();
                    $fileLocation = storage_path().'/app/public/report_files/';
                    $fileNL = $fileLocation.'//'.$time.'.pdf';
                    $pdf::Output($fileNL, 'F');
                    $url=url('/').'/storage/app/public/report_files/'.$time.'.pdf';
                    $url=array('url'=>$url);
                    return $this->userResponse->Success($url);
                }
                else
                {
                    return $this->userResponse->Failed($sales = (object)[],'NO RECORDS FOUND.');
                }
            }
            else
            {
                return $this->userResponse->Exception('customer not found.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_payments(Request $request,$page_no,$page_size)
    {
        try
        {
            if($request['customer_id']!='' &&  $request['fromDate']=='' && $request['toDate']=='')
            {
                $result=PaymentReceive::select('id','paidAmount','amountInWords','payment_type','transferDate','ChequeNumber','accountNumber')->where('isActive','=',1)->where('isPushed','=',1)->where('customer_id','=',$request['customer_id'])->get()->sortDesc()->forPage($page_no,$page_size);
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            elseif($request['customer_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                //$request['toDate']=$this->get_one_day_back($request['toDate']);
                $result=PaymentReceive::select('id','paidAmount','amountInWords','payment_type','transferDate','ChequeNumber','accountNumber')->where('isActive','=',1)->where('isPushed','=',1)->where('customer_id','=',$request['customer_id'])->whereBetween('transferDate', [$request['fromDate'], $request['toDate']])->get()->sortDesc()->forPage($page_no,$page_size);
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_advances(Request $request,$page_no,$page_size)
    {
        try
        {
            if($request['customer_id']!='' &&  $request['fromDate']=='' && $request['toDate']=='')
            {
                $result=CustomerAdvance::select('id','Amount','sumOf','paymentType','TransferDate','ChequeNumber','spentBalance','remainingBalance','accountNumber')->where('isActive','=',1)->where('isPushed','=',1)->where('customer_id','=',$request['customer_id'])->get()->sortDesc()->forPage($page_no,$page_size);
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            elseif($request['customer_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                //$request['toDate']=$this->get_one_day_back($request['toDate']);
                $result=CustomerAdvance::select('id','Amount','sumOf','paymentType','TransferDate','ChequeNumber','spentBalance','remainingBalance','accountNumber')->where('isActive','=',1)->where('isPushed','=',1)->where('customer_id','=',$request['customer_id'])->whereBetween('transferDate', [$request['fromDate'], $request['toDate']])->get()->sortDesc()->forPage($page_no,$page_size);
                $final_array=array();
                foreach ($result as $item)
                {
                    $final_array[]=$item;
                }
                if($result)
                {
                    return $this->userResponse->Success($final_array);
                }
                else
                {
                    return $this->userResponse->Success($result);
                }
            }
            else
            {
                return $this->userResponse->Exception('something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function my_account_statement(Request $request)
    {
        try
        {
            if($request['customer_id']!='' &&  $request['fromDate']!='' && $request['toDate']!='')
            {
                //$request['toDate']=$this->get_one_day_back($request['toDate']);
                $account_transactions=AccountTransaction::where('customer_id','=',$request['customer_id'])->whereBetween('createdDate', [$request['fromDate'], $request['toDate']])->whereNull('updateDescription')->orderBy('createdDate')->orderBy('id')->get();
                $sum_of_debit_before_from_date=AccountTransaction::where('customer_id',$request['customer_id'])->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request['fromDate'])->sum('Debit');
                $sum_of_credit_before_from_date=AccountTransaction::where('customer_id',$request['customer_id'])->whereNull('updateDescription')->whereNull('deleted_at')->where('createdDate','<',$request['fromDate'])->sum('Credit');
                $closing_amount=$sum_of_debit_before_from_date-$sum_of_credit_before_from_date;

                $customer_name=Customer::select('Name')->where('id',$request['customer_id'])->first();

                $row=json_decode(json_encode($account_transactions), true);
                $row=array_values($row);
                if(!empty($row))
                {
                    $pdf = new PDF();
                    $pdf::setPrintHeader(false);
                    $pdf::setPrintFooter(false);
                    $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                    $pdf::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                    $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
                    $pdf::SetFillColor(255,255,0);

                    $pdf::SetFont('helvetica', '', 15);
                    $html='Account Statement :-  '.$customer_name->Name;
                    $pdf::writeHTMLCell(0, 0, '', '', $html,0, 1, 0, true, 'L', true);

                    $html=' Opening Balance  '.round($closing_amount,2);
                    $date_range=date('d-m-Y', strtotime($request['fromDate'])).' To '.date('d-m-Y', strtotime($request['toDate']));
                    $pdf::SetFont('helvetica', '', 12);
                    $pdf::Cell(95,5,$date_range,'',0,'L');
                    $pdf::Cell(95,5,$html,'',0,'R');
                    $pdf::Ln(8);

                    $pdf::SetFont('helvetica', 'B', 14);
                    $html = '<table border="0.5" cellpadding="2">
            <tr style="background-color: rgb(122,134,216); color: rgb(255,255,255);">
                <th align="center" width="70">Date</th>
                <th align="center" width="185">Ref#</th>
                <th align="center" width="90">Debit</th>
                <th align="center" width="90">Credit</th>
                <th align="right" width="100">Closing</th>
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
                    <td align="center" width="70">'.(date('d-m-Y', strtotime($row[$i]['createdDate']))).'</td>
                    <td align="left" width="185">'.$row[$i]['referenceNumber'].'</td>
                    <td align="right" width="90">'.(number_format($row[$i]['Debit'],2,'.',',')).'</td>
                    <td align="right" width="90">'.(number_format($row[$i]['Credit'],2,'.',',')).'</td>
                    <td align="right" width="100">'.(number_format($balance,2,'.',',')).'</td>
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
                     <td width="255" align="right" colspan="3">Total : </td>
                     <td width="90" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="100" align="right" color="red">'.number_format($balance,2,'.',',').'</td>
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
                     <td width="255" align="right" colspan="3">Total : </td>
                     <td width="90" align="right">'.number_format($debit_total,2,'.',',').'</td>
                     <td width="90" align="right">'.number_format($credit_total,2,'.',',').'</td>
                     <td width="100" align="right">'.number_format($balance,2,'.',',').'</td>
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

                    return $this->userResponse->Success($url);
                }
                else
                {
                    return $this->userResponse->Failed($sales = (object)[],'NO RECORDS FOUND.');
                }
            }
            else
            {
                return $this->userResponse->Exception('something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function check_app_access_status(Request $request)
    {
        try
        {
            if($request['customer_id']!='')
            {
                $result=Customer::select('app_access')->where('id','=',$request['customer_id'])->first();
                if($result)
                {
                    return $this->userResponse->Success($result);
                }
                else
                {
                    return $this->userResponse->Exception('customer not found.');
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function get_dashboard_data(Request $request)
    {
        try
        {
            if($request['customer_id']!='')
            {
                $result['total_purchase_qty']=SaleDetail::where('customer_id','=',$request['customer_id'])->sum('Quantity');
                $result['total_vehicle']=Vehicle::where('customer_id','=',$request['customer_id'])->count();
                $result['active_vehicle']=Vehicle::where('customer_id','=',$request['customer_id'])->where('isActive',1)->count();
                $result['deactive_vehicle']=Vehicle::where('customer_id','=',$request['customer_id'])->where('isActive',0)->count();
                $result['customer_since']=Customer::select('openingBalanceAsOfDate')->where('id','=',$request['customer_id'])->first()->openingBalanceAsOfDate;
                if($result)
                {
                    return $this->userResponse->Success($result);
                }
                else
                {
                    return $this->userResponse->Exception('customer not found.');
                }
            }
            else
            {
                return $this->userResponse->Exception('Something is wrong.');
            }
        }
        catch (\Exception $ex)
        {
            Return $this->userResponse->Exception($ex);
        }
    }

    public function get_one_day_back($date)
    {
        return date('Y-m-d', strtotime('-1 day', strtotime($date)));
    }
}
