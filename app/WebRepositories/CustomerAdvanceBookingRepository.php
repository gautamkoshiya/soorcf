<?php


namespace App\WebRepositories;


use App\MISC\CustomeFooter;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvanceBooking;
use App\Models\CustomerAdvanceBookingDetail;
use App\Models\CustomerPrice;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\WebRepositories\Interfaces\ICustomerAdvanceBookingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use PDF;

class CustomerAdvanceBookingRepository implements ICustomerAdvanceBookingRepositoryInterface
{
    public function index()
    {
        return view('admin.customer_advance_booking.index');
    }

    public function all_bookings(Request $request)
    {
        $columns = array(
            0 =>'id',
            1 =>'SaleDate',
            2=> 'customer_id',
            3=> 'id',
        );
        $totalData = CustomerAdvanceBooking::where('company_id',session('company_id'))->count();

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if(empty($request->input('search.value')))
        {
            $sql = 'select b.*,c.Name from customer_advance_bookings as b left join customers as c on c.id = b.customer_id where b.company_id = '.session('company_id').' and b.deleted_at is null  order by id desc limit '.$limit.' offset '.$start ;
            $booking = DB::select( DB::raw($sql));
        }
        else
        {
            $search = $request->input('search.value');

            $sql = 'select b.*,c.Name from customer_advance_bookings as b left join customers as c on c.id = b.customer_id where b.company_id = '.session('company_id').' and b.deleted_at is null and b.code LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $booking = DB::select( DB::raw($sql));

            $sql_count = 'select COUNT(*) TotalCount,b.*,c.Name from customer_advance_bookings as b left join customers as c on c.id = b.customer_id where b.company_id = '.session('company_id').' and b.deleted_at is null and b.code LIKE "%'.$search.'%" order by id desc limit '.$limit.' offset '.$start ;
            $count = DB::select(DB::raw($sql_count));
            if(!empty($count))
            {
                $totalFiltered = $count[0]->TotalCount;
            }
        }
        $data = array();
        if(!empty($booking))
        {
            foreach ($booking as $single)
            {
                $nestedData['id'] = $single->id;
                $nestedData['BookingDate'] = date('d-m-Y', strtotime($single->BookingDate));
                $nestedData['code'] = $single->code ?? "N.A.";
                $nestedData['totalQuantity'] = $single->totalQuantity ?? 0.00;
                $nestedData['consumedQuantity'] = $single->consumedQuantity ?? 0.00;
                $nestedData['remainingQuantity'] = $single->remainingQuantity ?? 0.00;
                $nestedData['Rate'] = $single->Rate ?? 0.00;
                $nestedData['Name'] = $single->Name ?? "N.A.";
                $nestedData['Description'] = $single->Description ?? "N.A.";
                $button='';
                if($single->consumedQuantity==0)
                {
                    $button.='<a href="'.route('customer_advance_bookings.edit', $single->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                }
                $button.='&nbsp;';
                if($single->consumedQuantity==0)
                {
                    $button.='<a href="#" data-id="'.$single->id.'" data-toggle="modal" class="salesDelete btn btn-danger btn-sm" data-target="#deleteSales"><i style="font-size: 20px" class="fa fa-trash"></i></a>';
                }
                $button.='&nbsp;<button class="btn btn-primary"  onclick="show_detail(this.id)" type="button" id="show_'.$single->id.'"><i class="fa fa-eye" ></i></i></button>';
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
        $init_data = $this->BookingNumber();
        $customers = Customer::select('id','Name')->where('company_id',session('company_id'))->where('isActive',1)->orderBy('id', 'desc')->get();
        return view('admin.customer_advance_booking.create',compact('customers','init_data'));
    }

    public function BookingNumber()
    {
        $max_id = CustomerAdvanceBooking::max('id');
        if($max_id)
        {
            $max_id = CustomerAdvanceBooking::where('id',$max_id)->first();
            $last=explode('#',$max_id->code);
            if(isset($last[1]))
            {
                $newInvoiceID = 'BK#'.str_pad(($last[1] + 1), 4, '0', STR_PAD_LEFT);
            }
            return $newInvoiceID;
        }
        else
        {
            $newInvoiceID = 'BK#'.str_pad((0 + 1), 4, '0', STR_PAD_LEFT);
        }
        return $newInvoiceID;
    }

    public function store(Request $request)
    {
        $result=false;
        $msg='';
        DB::transaction(function () use($request,&$result,&$msg)
        {
            $user_id = session('user_id');
            $company_id = session('company_id');
            //echo "<pre>";print_r($request->all());die;

            $zero_consumed_booking=CustomerAdvanceBooking::where('customer_id',$request->customer_id)->where('consumedQuantity',0)->get();
            $zero_consumed_booking_count=$zero_consumed_booking->count();
            if($zero_consumed_booking_count>=1)
            {
                $msg='NEW BOOKING NOT ALLOWED BECAUSE THERE IS ONE UN-CONSUMED BOOKING !!!';
            }
            else
            {
                if($request->overfilled_quantity_value==0)
                {
                    // nothing overfilled
                    $booking = new CustomerAdvanceBooking();
                    $booking->code = preg_replace("/\s+/", "", $request->code);
                    $booking->user_id = $user_id;
                    $booking->company_id = $company_id;
                    $booking->BookingDate = $request->BookingDate;
                    $booking->customer_id = $request->customer_id;
                    $booking->totalQuantity = $request->totalQuantity;
                    $booking->remainingQuantity = $request->totalQuantity;
                    $booking->Rate = $request->Rate;
                    $booking->Description =  strip_tags($request->Description);

                    /*$filename='';
                    if ($request->hasfile('booking_file'))
                    {
                        $document = $request->file('booking_file');
                        $extension = $document->getClientOriginalExtension();
                        $filename = uniqid('advance_booking_') . '.' . $extension;
                        $document->storeAs('file_manager/', $filename, 'public');
                    }
                    $booking->document = $filename;*/
                    $booking->save();

                    $msg='BOOKING SUCCESSFULLY SAVED...';
                    $result=true;
                }
                elseif($request->overfilled_quantity_value!=0 AND $request->update_over_filled==0)
                {
                    //something is overfilled but no need to update previous sales entry
                    $booking = new CustomerAdvanceBooking();
                    $booking->code = preg_replace("/\s+/", "", $request->code);
                    $booking->user_id = $user_id;
                    $booking->company_id = $company_id;
                    $booking->BookingDate = $request->BookingDate;
                    $booking->customer_id = $request->customer_id;
                    $booking->totalQuantity = $request->totalQuantity;
                    $booking->remainingQuantity = $request->totalQuantity;
                    $booking->Rate = $request->Rate;
                    $booking->Description =  strip_tags($request->Description);
                    $booking->save();

                    $msg='BOOKING SUCCESSFULLY SAVED...';
                    $result=true;
                }
                //echo "<pre>";print_r($request->all());die;
                elseif($request->overfilled_quantity_value!=0 AND $request->update_over_filled==1)
                {
                    //first check booking qty is more than sum of overfilled qty
                    $sum_of_overfilled_qty=SaleDetail::where('customer_id',$request->customer_id)->whereNull('deleted_at')->where('isActive',1)->whereNotNull('booking_shortage')->sum('Quantity');
                    if($request->totalQuantity>$sum_of_overfilled_qty)
                    {
                        //something is overfilled and also need to update previous sales entry
                        $overfilled_sales_detail=SaleDetail::where('customer_id',$request->customer_id)->whereNotNull('booking_shortage')->get();
                        $row=json_decode(json_encode($overfilled_sales_detail), true);
                        $row=array_column($row,'sale_id');
                        $count_of_sales_not_paid=Sale::whereIn('id',$row)->where('isActive',1)->where('remainingBalance',0)->get();
                        $count_of_sales_not_paid = $count_of_sales_not_paid->count();
                        if($count_of_sales_not_paid<=0)
                        {
                            $sum_of_quantity_disbursed_before_booking_entry=0;
                            $detail_entry_ids=[];
                            // can proceed with other db operations
                            foreach($overfilled_sales_detail as $single)
                            {
                                // create new entry with booking rate
                                $total=$single->Quantity*$request->Rate;
                                $vat_amount=$single->Quantity*$request->Rate*$single->VAT/100;
                                $total_with_vat=$total+$vat_amount;
                                SaleDetail::create([
                                    "sale_id" => $single->sale_id,
                                    "user_id" => $user_id,
                                    "company_id" => $company_id,
                                    "product_id" => $single->product_id,
                                    "unit_id" => $single->unit_id,
                                    "vehicle_id" => $single->vehicle_id,
                                    "createdDate" => $single->createdDate,
                                    "PadNumber" => $single->PadNumber,
                                    "Quantity" => $single->Quantity,
                                    "Price" => $request->Rate,
                                    "rowTotal" => $total,
                                    "VAT" => $single->VAT,
                                    "rowVatAmount" => $vat_amount,
                                    "rowSubTotal" => $total_with_vat,
                                    "customer_id" => $single->customer_id,
                                ]);
                                // delete old rate entry
                                $single->delete();

                                // update parent entry
                                $sales=Sale::where('id',$single->sale_id)->first();
                                $sales->update([
                                    'user_id'=>session('user_id'),
                                    'Total'=>$total,
                                    'subTotal'=>$total_with_vat,
                                    'totalVat'=>$vat_amount,
                                    'grandTotal'=>$total_with_vat,
                                    'remainingBalance'=>$total_with_vat,
                                ]);

                                // update account transaction entry
                                $description_string='Sales|'.$single->sale_id;
                                $previous_entry = AccountTransaction::get()->where('customer_id','=',$single->customer_id)->where('Description','like',$description_string)->last();
                                AccountTransaction::where('id', $previous_entry->id)->update([
                                    'Debit' => $total_with_vat,
                                    'Differentiate' => $previous_entry->Differentiate+$total_with_vat-$previous_entry->Debit,
                                ]);

                                $detail_entry=CustomerAdvanceBookingDetail::create([
                                    "Quantity" => $single->Quantity,
                                    "user_id" => $user_id,
                                    "company_id" => $company_id,
                                    "customer_id" => $single->customer_id,
                                    "booking_id" => 0,
                                    'sale_id' => $single->sale_id,
                                    'BookingDate' => $request->BookingDate,
                                    'PadNumber' => $single->PadNumber,
                                ]);
                                $detail_entry_ids[]=$detail_entry->id;
                                $sum_of_quantity_disbursed_before_booking_entry+=$single->Quantity;
                            }

                            $booking = new CustomerAdvanceBooking();
                            $booking->code = preg_replace("/\s+/", "", $request->code);
                            $booking->user_id = $user_id;
                            $booking->company_id = $company_id;
                            $booking->BookingDate = $request->BookingDate;
                            $booking->customer_id = $request->customer_id;
                            $booking->totalQuantity = $request->totalQuantity;
                            $booking->consumedQuantity = $sum_of_quantity_disbursed_before_booking_entry;
                            $booking->remainingQuantity = $request->totalQuantity-$sum_of_quantity_disbursed_before_booking_entry;
                            $booking->Rate = $request->Rate;
                            $booking->Description =  strip_tags($request->Description);
                            $booking->save();

                            if($sum_of_quantity_disbursed_before_booking_entry!=0 && !empty($detail_entry_ids))
                            {
                                // update booking id in detail entries
                                CustomerAdvanceBookingDetail::whereIN('id',$detail_entry_ids)->update([
                                    'booking_id' => $booking->id,
                                ]);
                            }

                            $msg='BOOKING SUCCESSFULLY SAVED...';
                            $result=true;
                        }
                        else
                        {
                            // not possible to do booking because overfilled sales entry payment done
                            $msg='NOT ABLE TO DO BOOKING BECAUSE SOME OF SALES ENTRY IS ALREADY PAID !!!';
                        }
                    }
                    else
                    {
                        // booking qty must be more than overfilled qty
                        $msg='BOOKING QTY MUST BE GREATER THAN OVERFILLED QTY !!!';
                    }
                }
            }
        });
        $data=array('result'=>$result,'message'=>$msg);
        echo json_encode($data);
    }

    public function update(Request $request, $Id)
    {
        DB::transaction(function () use($request,$Id)
        {
            $advance = CustomerAdvanceBooking::find($Id);
            $user_id = session('user_id');
            $advance->update([
                'code' =>preg_replace("/\s+/", "", $request->Data['code']),
                'BookingDate' =>$request->Data['BookingDate'],
                'customer_id' =>$request->Data['customer_id'],
                'totalQuantity' =>$request->Data['totalQuantity'],
                'remainingQuantity' =>$request->Data['totalQuantity'],
                'Rate' =>$request->Data['Rate'],
                'Description' =>$request->Data['Description'] ?? null,
                'user_id' =>$user_id,
            ]);
        });
        $data=array('result'=>true,'message'=>'Record Updated Successfully.');
        echo json_encode($data);
    }

    public function edit($Id)
    {
        $customers = Customer::where('company_id',session('company_id'))->get();
        $booking = CustomerAdvanceBooking::with('customer')->find($Id);
        return view('admin.customer_advance_booking.edit',compact('customers','booking',));
    }

    public function customer_booking_delete_post($Id)
    {
        // TODO: Implement customer_booking_delete_post() method.
    }

    public function CustomerBookingOverfilledDetails($Id)
    {
        //echo "<pre>";print_r($Id);die;
        $sum_of_overfilled_qty=SaleDetail::where('customer_id',$Id)->whereNull('deleted_at')->where('isActive',1)->whereNotNull('booking_shortage')->sum('Quantity');
        if($sum_of_overfilled_qty>0)
        {
            $data=array('result'=>true,'data'=>$sum_of_overfilled_qty);
            echo json_encode($data);
        }
        else
        {
            $data=array('result'=>false,'data'=>'');
            echo json_encode($data);
        }
    }

    public function getBookingDetail($Id)
    {
        $booking=CustomerAdvanceBooking::with(['customer','user'])->where('id',$Id)->first();
        //echo "<pre>";print_r($payment->user);die;
        $booking_detail=CustomerAdvanceBookingDetail::where('booking_id',$Id)->get();
        $html='<div class="row"><div class="col-md-12"><label>Customer Name : '.$booking->customer->Name.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Booking Date : '.date('d-M-Y',strtotime($booking->BookingDate)).'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Reference No. : '.$booking->code.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Total Quantity : '.$booking->totalQuantity.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Consumed Quantity : '.$booking->consumedQuantity.'</label></div></div>';
        $html.='<div class="row"><div class="col-md-12"><label>Remaining Quantity : '.$booking->remainingQuantity.'</label></div></div>';
        $html.='<div class="row text-right"><div class="col-md-12"><label>(created by : '.$booking->user->name.'-'.$booking->created_at.')</label></div></div>';
        $html.='<table class="table table-sm table-responsive"><thead><th>SR</th><th>Disbursed QTY</th><th>Sale Date</th><th>PAD</th></thead><tbody>';
        $i=0;
        foreach ($booking_detail as $item)
        {
            $html.='<tr>';
            $html.='<td>'.++$i.'</td>';
            $html.='<td>'.$item->Quantity ?? "NA".'</td>';
            $html.='<td>'.date('d-M-Y',strtotime($item->created_at)) ?? "NA".'</td>';
            $html.='<td>'.$item->PadNumber ?? "NA".'</td>';
            $html.='</tr>';
        }
        $html.='</tbody>';
        return Response()->json($html);
    }

    public function getAdvanceBookingReport()
    {
        $booking_customers=CustomerAdvanceBooking::select('customer_id')->where('company_id',session('company_id'))->get();
        $row=json_decode(json_encode($booking_customers), true);
        $row=array_column($row,'customer_id');
        $customers = Customer::whereIn('id',$row)->where('company_id',session('company_id'))->get();
        return view('admin.customer_advance_booking.advance_booking_report',compact('customers'));
    }

    public function PrintAdvanceBookingReport(Request $request)
    {
        if ($request->fromDate!='' && $request->toDate!='' &&  $request->customer_id!='')
        {
            $booking=CustomerAdvanceBooking::with(['booking_details'])->where('company_id',session('company_id'))->where('customer_id',$request->customer_id)->whereBetween('BookingDate',[$request->fromDate,$request->toDate])->get();
            //echo "<pre>";print_r($booking);die;

            if($booking->first())
            {
                $footer=new CustomeFooter;
                $footer->footer();
                $row=json_decode(json_encode($booking), true);
                $pdf = new PDF();
                $pdf::setPrintHeader(false);

                $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf::SetAutoPageBreak(TRUE, 14);

                $pdf::AddPage();$pdf::SetFont('helvetica', '', 6);
                $pdf::SetFillColor(255,255,0);

                $pdf::SetFont('helvetica', '', 15);
                $html='ADVANCE BOOKING REPORT BY CUSTOMER';
                $date=date('d-m-Y', strtotime($request->fromDate)).' To '.date('d-m-Y', strtotime($request->toDate));

                $pdf::Cell(95,5,$html,'',0,'L');
                $pdf::Cell(95,5,$date,'',0,'R');
                $pdf::Ln(6);

                $date='';
                $html='Customer Name : '.$request->customer_name;

                $pdf::Cell(95,5,$html,'',0,'L');
                $pdf::Cell(95,5,$date,'',0,'R');
                $pdf::Ln(10);

                $pdf::SetFont('helvetica', '', 8);
                if(!empty($row))
                {
                    //echo "<pre>";print_r($row);die;
                    //booking heading
                    for($i=0;$i<count($row);$i++)
                    {
                        $html = '<table border="0.5" cellpadding="1">
                            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); font-weight: bold;font-size: 10px;">
                                <th align="center" width="50">Code#</th>
                                <th align="center" width="60">Date</th>
                                <th align="center" width="70">Total Qty</th>
                                <th align="center" width="70">Remaining</th>
                                <th align="center" width="70">Consumed</th>
                                <th align="center" width="40">Rate</th>
                            </tr>';

                        $html .= '<tr>
                            <td align="center" width="50">'.($row[$i]['code']).'</td>
                            <td align="center" width="60">'.(date('d-m-Y', strtotime($row[$i]['BookingDate']))).'</td>
                            <td align="right" width="70">'.(number_format($row[$i]['totalQuantity'], 2, '.', ',')).'</td>
                            <td align="right" width="70">'.(number_format($row[$i]['remainingQuantity'], 2, '.', ',')).'</td>
                            <td align="right" width="70">'.(number_format($row[$i]['consumedQuantity'], 2, '.', ',')).'</td>
                            <td align="right" width="40">'.(number_format($row[$i]['Rate'], 2, '.', ',')).'</td>
                            </tr>';
                        $html .= '</table>';
                        $pdf::writeHTML($html, false, false, false, false, '');

                        $html = '<table border="0.5" cellpadding="1">
                            <tr style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); font-weight: bold;font-size: 8px;">
                                <th align="center" width="50"></th>
                                <th align="center" width="60">#</th>
                                <th align="center" width="140">Pad Number</th>
                                <th align="center" width="110">Qty Disbursed</th>
                            </tr>';
                        for($j=0;$j<count($row[$i]['booking_details']);$j++)
                        {
                            $html .= '<tr>
                            <td align="center" width="50"></td>
                            <td align="center" width="60">'.($j+1).'</td>
                            <td align="center" width="140">'.($row[$i]['booking_details'][$j]['PadNumber']).'</td>
                            <td align="center" width="110">'.(number_format($row[$i]['booking_details'][$j]['Quantity'], 2, '.', ',')).'</td>
                            </tr>';
                        }
                        $html .= '</table>';
                        $pdf::writeHTML($html, true, false, false, false, '');
                    }
                }
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
}
