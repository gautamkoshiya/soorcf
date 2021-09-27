<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IPurchaseInvoiceRepositoryInterface;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    private $purchaseInvoiceRepository;

    public function __construct(IPurchaseInvoiceRepositoryInterface $purchaseInvoiceRepository)
    {
        $this->purchaseInvoiceRepository = $purchaseInvoiceRepository;
    }

    public function index()
    {
        return $this->purchaseInvoiceRepository->index();
    }

    public function all_purchase_invoice(Request $request)
    {
        return $this->purchaseInvoiceRepository->all_purchase_invoice($request);
    }

    public function create()
    {
        return $this->purchaseInvoiceRepository->create();
    }

    public function store(Request $request)
    {
        $this->purchaseInvoiceRepository->store($request);
    }

    public function SaveTaxInvoiceDetails(Request $request)
    {
        $this->purchaseInvoiceRepository->SaveTaxInvoiceDetails($request);
    }

    public function GetTaxInvoiceDetails($id)
    {
        return $this->purchaseInvoiceRepository->GetTaxInvoiceDetails($id);
    }

    public function edit($id)
    {
        return $this->purchaseInvoiceRepository->edit($id);
    }

    public function PurchaseInvoiceUpdate(Request $request, $id)
    {
        return $this->purchaseInvoiceRepository->update($request, $id);
    }

    public function deletePurchaseInvoice($id)
    {
        return $this->purchaseInvoiceRepository->delete($id);
    }

    public function GetPurchaseInvoiceReport()
    {
        return $this->purchaseInvoiceRepository->GetPurchaseInvoiceReport();
    }

    public function PrintPurchaseInvoiceReport(Request $request)
    {
        $response=$this->purchaseInvoiceRepository->PrintPurchaseInvoiceReport($request);
        return response()->json($response);
    }
}
