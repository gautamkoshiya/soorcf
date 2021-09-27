<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IEmployeeTransactionRepositoryInterface;
use Illuminate\Http\Request;

class EmployeeTransaction extends Controller
{
    private $employeeTransactionRepository;

    public function __construct(IEmployeeTransactionRepositoryInterface $employeeTransactionRepository)
    {
        $this->employeeTransactionRepository = $employeeTransactionRepository;
    }

    public function index()
    {
        return $this->employeeTransactionRepository->index();
    }

    public function create()
    {
        return $this->employeeTransactionRepository->create();
    }

    public function store(Request $request)
    {
        return $this->employeeTransactionRepository->store($request);
    }

    public function employee_transaction_delete_post(Request $request)
    {
        return $this->employeeTransactionRepository->employee_transaction_delete_post($request);
    }

    public function InvestorReportByCompany()
    {
        return $this->employeeTransactionRepository->InvestorReportByCompany();
    }

    public function PrintInvestorReportByCompany(Request $request)
    {
        return $this->employeeTransactionRepository->PrintInvestorReportByCompany($request);
    }

    public function CheckAccountTransactionReferenceExist(Request $request)
    {
        return $this->employeeTransactionRepository->CheckAccountTransactionReferenceExist($request);
    }

    public function EmployeeAccountStatement()
    {
        return $this->employeeTransactionRepository->EmployeeAccountStatement();
    }

    public function PrintEmployeeAccountStatement(Request $request)
    {
        return $this->employeeTransactionRepository->PrintEmployeeAccountStatement($request);
    }

    public function GetEmployeeReceivable()
    {
        return $this->employeeTransactionRepository->GetEmployeeReceivable();
    }

    public function PrintEmployeeReceivable()
    {
        return $this->employeeTransactionRepository->PrintEmployeeReceivable();
    }

    public function GetEmployeeLabourList()
    {
        return $this->employeeTransactionRepository->GetEmployeeLabourList();
    }

    public function PrintEmployeeLabourList(Request $request)
    {
        return $this->employeeTransactionRepository->PrintEmployeeLabourList($request);
    }
}
