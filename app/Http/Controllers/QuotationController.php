<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IQuotationRepositoryInterface;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    private $quotationRepository;

    public function __construct(IQuotationRepositoryInterface $quotationRepository)
    {
        $this->quotationRepository = $quotationRepository;
    }

    public function index()
    {
        return $this->quotationRepository->index();
    }

    public function all_quotation(Request $request)
    {
        return $this->quotationRepository->all_quotation($request);
    }

    public function create()
    {
        return $this->quotationRepository->create();
    }

    public function store(Request $request)
    {
        $this->quotationRepository->store($request);
    }

    public function PrintQuotation($id)
    {
        $response=$this->quotationRepository->PrintQuotation($id);
        return response()->json($response);
    }

    public function PrintQuotation1($id)
    {
        $response=$this->quotationRepository->PrintQuotation1($id);
        return response()->json($response);
    }

    public function edit($id)
    {
        return $this->quotationRepository->edit($id);
    }

    public function quotationUpdate(Request $request, $id)
    {
        return $this->quotationRepository->update($request, $id);
    }

    public function deleteQuotation($id)
    {
        return $this->quotationRepository->delete($id);
    }

    public function quotationCustomerDetails($id)
    {
        return $this->quotationRepository->quotationCustomerDetails($id);
    }
}
