<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use App\Models\Sale;
use App\WebRepositories\Interfaces\ISaleRepositoryInterface;
use Carbon\Traits\Date;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    private $saleRepository;

    public function __construct(ISaleRepositoryInterface $saleRepository)
    {
       $this->saleRepository = $saleRepository;
    }

    public function CheckPadExist(Request $request)
    {
        return $this->saleRepository->CheckPadExist($request);
    }

    public function CheckVehicleStatus(Request $request)
    {
        return $this->saleRepository->CheckVehicleStatus($request);
    }

    public function index()
    {
        return $this->saleRepository->index();
    }

    public function get_today_sale()
    {
        return $this->saleRepository->get_today_sale();
    }

    public function get_sale_of_date()
    {
        return $this->saleRepository->get_sale_of_date();
    }

    public function view_sale_of_date(Request $request)
    {
        return $this->saleRepository->view_sale_of_date($request);
    }

    public function view_result_sale_of_date(Request $request)
    {
        return $this->saleRepository->view_result_sale_of_date($request);
    }

    public function getSalesPaymentDetail($Id)
    {
        return $this->saleRepository->getSalesPaymentDetail($Id);
    }

    public function create()
    {
        return $this->saleRepository->create();
    }

    public function store(Request $request)
    {
        $this->saleRepository->store($request);
    }

    public function store_sale_service(Request $request)
    {
        $this->saleRepository->store_sale_service($request);
    }

    public function show(Sale $sale)
    {
        //
    }

    public function edit($Id)
    {
        return $this->saleRepository->edit($Id);
    }

    public function edit_sale_service($Id)
    {
        return $this->saleRepository->edit_sale_service($Id);
    }

    public function salesUpdate(Request $request, $Id)
    {
        return $this->saleRepository->update($request, $Id);
    }

    public function salesServiceUpdate(Request $request, $Id)
    {
        return $this->saleRepository->salesServiceUpdate($request, $Id);
    }

    public function all_sales(Request $request)
    {
        return $this->saleRepository->all_sales($request);
    }

    public function all_sales_service(Request $request)
    {
        return $this->saleRepository->all_sales_service($request);
    }

    public function sales_delete($id)
    {
        return $this->saleRepository->delete($id);
    }

    public function get_data(Request $request)
    {
        return $this->saleRepository->get_data($request);
    }

    public function customerSaleDetails($Id)
    {
        $sales = Sale::select('id','customer_id','SaleDate','subTotal','grandTotal','paidBalance','remainingBalance')->with(['sale_details'=>function($q){$q->select('id','sale_id','vehicle_id','createdDate','PadNumber',);},'sale_details.vehicle'=>function($q){$q->select('id','registrationNumber');}])->where(['customer_id'=>$Id,'IsPaid'=> false,])->get();
        //$credit_sum=AccountTransaction::where('customer_id',$Id)->whereNull('updateDescription')->sum('Credit');
        //$debit_sum=AccountTransaction::where('customer_id',$Id)->whereNull('updateDescription')->sum('Debit');
        $diff=CustomerAdvance::where('customer_id',$Id)->where('isActive',1)->where('isPushed',1)->whereNull('deleted_at')->sum('remainingBalance');
        //$diff=$debit_sum-$credit_sum;
        $diff=round($diff, 2);
        return response()->json(['sales'=>$sales,'account_closing'=>$diff]);
    }

    public function sales_delete_post(Request $request)
    {
        return $this->saleRepository->sales_delete_post($request);
    }

    public function getSalesQuantityChart()
    {
        return $this->saleRepository->getSalesQuantityChart();
    }

    public function printSalesQuantityChart(Request $request)
    {
        return $this->saleRepository->printSalesQuantityChart($request);
    }

    public function getSalesQuantityChartCustomer()
    {
        return $this->saleRepository->getSalesQuantityChartCustomer();
    }

    public function printSalesQuantityChartCustomer(Request $request)
    {
        return $this->saleRepository->printSalesQuantityChartCustomer($request);
    }

    public function salesByDateDetails($id)
    {
        // $customers = Customer::with('vehicles')->find($id);
        $salesData = Sale::with('sale_details')->where('company_id','=',session('company_id'))->where('SaleDate', $id)->where('isActive','=',1)->whereNull('deleted_at')->get();
        if ($salesData != null)
        {
            $salesByDate['total'] = 0;
            $all_pads=array();
            foreach ($salesData as $data){
                $salesByDate['total'] += $data->sale_details[0]->Quantity;
                $all_pads[]=$data->sale_details[0]->PadNumber;
            }
            $salesByDate['total'] = round($salesByDate['total'],2);
            $filtered = array_diff($all_pads, array(null, 0));
            $max = max($filtered);
            $min = min($filtered);
            //$salesByDate['sale_details'] = $salesData->first()->sale_details->sum('Quantity');
            //$salesByDate['sale_details'] = $salesData->first()->sale_details->sum('Quantity');
            $salesByDate['firstPad'] = $min;
            $salesByDate['lastPad'] = $max;
        }
        else
        {
            $salesByDate['sale_details'] = 0;
            $salesByDate['firstPad'] = 0;
            $salesByDate['lastPad'] = 0;
        }

//        $salesByDate['totalSale'] = Sale::with('sale_details')->where('SaleDate', $id)->get()->sum('grandTotal');
//        $salesByDate['firstPad'] = Sale::with('sale_details')->where('SaleDate', $id)->get()->first()->sale_details->first()->PadNumber;
//        $salesByDate['lastPad'] = Sale::with('sale_details')->where('SaleDate', $id)->get()->last()->sale_details->last()->PadNumber;
//        $salesByDate1['firstPadSale'] = Sale::with('sale_details')->where('SaleDate', $id)->first();
//        $salesByDate1['firstPadSale2'] = $salesByDate1['firstPadSale']->sale_details1->first();
//        $salesByDate1['firstPad'] = $salesByDate1['firstPadSale2']->PadNumber;

        //$salesByDate['lastPadSale'] = $salesByDate->last();
//        $salesByDate['lastPadSaleDetail'] = $salesByDate->last()->sale_details->last();
//        $salesByDate['lastPad'] = $salesByDate->last()->sale_details->last()->PadNumber;
//        $salesByDate['firstPad'] = $salesByDate->first()->sale_details->first()->PadNumber;
        //$salesByDate['sumOfSale'] = $salesByDate->sum('grandTotal');
        return response()->json($salesByDate);
    }
}
