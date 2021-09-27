<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\InvestorTransactionRepository;
use Illuminate\Http\Request;

class InvestorTransactionController extends Controller
{
    private $investorTransactionRepository;

    public function __construct(InvestorTransactionRepository $investorTransactionRepository)
    {
        $this->investorTransactionRepository = $investorTransactionRepository;
    }

    public function index()
    {
        return $this->investorTransactionRepository->index();
    }

    public function create()
    {
        return $this->investorTransactionRepository->create();
    }

    public function store(Request $request)
    {
        return $this->investorTransactionRepository->store($request);
    }

    public function investor_transaction_delete_post(Request $request)
    {
        return $this->investorTransactionRepository->investor_transaction_delete_post($request);
    }

    public function InvestorReportByCompany()
    {
        return $this->investorTransactionRepository->InvestorReportByCompany();
    }

    public function PrintInvestorReportByCompany(Request $request)
    {
        return $this->investorTransactionRepository->PrintInvestorReportByCompany($request);
    }
}
