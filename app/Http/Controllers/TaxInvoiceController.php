<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\ITaxInvoiceRepositoryInterface;
use Illuminate\Http\Request;

class TaxInvoiceController extends Controller
{
    private $invoiceRepository;

    public function __construct(ITaxInvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function index()
    {
        return $this->invoiceRepository->index();
    }

    public function all_tax_invoice(Request $request)
    {
        return $this->invoiceRepository->all_tax_invoice($request);
    }

    public function create()
    {
        return $this->invoiceRepository->create();
    }

    public function store(Request $request)
    {
        $this->invoiceRepository->store($request);
    }

    public function SaveTaxInvoiceDetails(Request $request)
    {
        $this->invoiceRepository->SaveTaxInvoiceDetails($request);
    }

    public function PrintTaxInvoice($id)
    {
        $response=$this->invoiceRepository->PrintTaxInvoice($id);
        return response()->json($response);
    }

    public function GetTaxInvoiceDetails($id)
    {
        return $this->invoiceRepository->GetTaxInvoiceDetails($id);
    }

    public function edit($id)
    {
        return $this->invoiceRepository->edit($id);
    }

    public function TaxInvoiceUpdate(Request $request, $id)
    {
        return $this->invoiceRepository->update($request, $id);
    }

    public function deleteTaxInvoice($id)
    {
        return $this->invoiceRepository->delete($id);
    }

    public function getInvoiceNumberByProject($id)
    {
        return $this->invoiceRepository->getInvoiceNumberByProject($id);
    }

    public function GetTaxInvoiceReport()
    {
        return $this->invoiceRepository->GetTaxInvoiceReport();
    }

    public function PrintTaxInvoiceReport(Request $request)
    {
        $response=$this->invoiceRepository->PrintTaxInvoiceReport($request);
        return response()->json($response);
    }
}
