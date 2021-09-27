<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IReportRepositoryInterface;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $reportRepository;

    public function __construct(IReportRepositoryInterface $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    public function GetCustomerStatement()
    {
        return $this->reportRepository->GetCustomerStatement();
    }

    public function PrintCustomerStatement()
    {
        return $this->reportRepository->PrintCustomerStatement();
    }

    public function PrintCustomerStatementForDate(Request $request)
    {
        return $this->reportRepository->PrintCustomerStatementForDate($request);
    }

    public function GetPaidAdvancesSummary()
    {
        return $this->reportRepository->GetPaidAdvancesSummary();
    }

    public function PrintPaidAdvancesSummary()
    {
        return $this->reportRepository->PrintPaidAdvancesSummary();
    }

    public function PrintReceivedAdvancesSummary()
    {
        return $this->reportRepository->PrintReceivedAdvancesSummary();
    }

    public function GetReceivedAdvancesSummary()
    {
        return $this->reportRepository->GetReceivedAdvancesSummary();
    }

    public function GetDetailCustomerStatement()
    {
        return $this->reportRepository->GetDetailCustomerStatement();
    }

    public function PrintDetailCustomerStatement(Request $request)
    {
        return $this->reportRepository->PrintDetailCustomerStatement($request);
    }

    public function PrintDailyCustomerStatement(Request $request)
    {
        return $this->reportRepository->PrintDailyCustomerStatement($request);
    }

    public function ViewDetailCustomerStatement(Request $request)
    {
        return $this->reportRepository->ViewDetailCustomerStatement($request);
    }

    public function GetSupplierStatement()
    {
        return $this->reportRepository->GetSupplierStatement();
    }

    public function PrintSupplierStatement()
    {
        return $this->reportRepository->PrintSupplierStatement();
    }

    public function PrintSupplierStatementForDate(Request $request)
    {
        return $this->reportRepository->PrintSupplierStatementForDate($request);
    }

    public function GetDetailSupplierStatement()
    {
        return $this->reportRepository->GetDetailSupplierStatement();
    }

    public function PrintDetailSupplierStatement(Request $request)
    {
        return $this->reportRepository->PrintDetailSupplierStatement($request);
    }

    public function ViewDetailSupplierStatement(Request $request)
    {
        return $this->reportRepository->ViewDetailSupplierStatement($request);
    }

    public function SalesReport()
    {
        return $this->reportRepository->SalesReport();
    }

    public function PrintSalesReport(Request $request)
    {
        return $this->reportRepository->PrintSalesReport($request);
    }

    public function SalesReportByVehicle()
    {
        return $this->reportRepository->SalesReportByVehicle();
    }

    public function PrintSalesReportByVehicle(Request $request)
    {
        return $this->reportRepository->PrintSalesReportByVehicle($request);
    }

    public function SalesReportByCustomer()
    {
        return $this->reportRepository->SalesReportByCustomer();
    }

    public function PrintSalesReportByCustomer(Request $request)
    {
        return $this->reportRepository->PrintSalesReportByCustomer($request);
    }

    public function SalesReportByShift()
    {
        return $this->reportRepository->SalesReportByShift();
    }

    public function PrintSalesReportByShift(Request $request)
    {
        return $this->reportRepository->PrintSalesReportByShift($request);
    }

    public function PurchaseReport()
    {
        return $this->reportRepository->PurchaseReport();
    }

    public function PrintPurchaseReport(Request $request)
    {
        return $this->reportRepository->PrintPurchaseReport($request);
    }

    public function ExpenseReport()
    {
        return $this->reportRepository->ExpenseReport();
    }

    public function ExpenseVatReport()
    {
        return $this->reportRepository->ExpenseVatReport();
    }

    public function PrintExpenseReport(Request $request)
    {
        return $this->reportRepository->PrintExpenseReport($request);
    }

    public function PrintCashExpenseReport(Request $request)
    {
        return $this->reportRepository->PrintCashExpenseReport($request);
    }

    public function PrintVATExpenseReport(Request $request)
    {
        return $this->reportRepository->PrintVATExpenseReport($request);
    }

    public function PrintLandscapeExpenseReport(Request $request)
    {
        return $this->reportRepository->PrintLandscapeExpenseReport($request);
    }

    public function CashReport()
    {
        return $this->reportRepository->CashReport();
    }

    public function PrintCashReport(Request $request)
    {
        return $this->reportRepository->PrintCashReport($request);
    }

    public function PrintExpenseCashReport(Request $request)
    {
        return $this->reportRepository->PrintExpenseCashReport($request);
    }

    public function PrintCashLogReport(Request $request)
    {
        return $this->reportRepository->PrintCashLogReport($request);
    }

    public function ViewCashReport(Request $request)
    {
        return $this->reportRepository->ViewCashReport($request);
    }

    public function BankReport()
    {
        return $this->reportRepository->BankReport();
    }

    public function GetReceivableSummaryAnalysis()
    {
        return $this->reportRepository->GetReceivableSummaryAnalysis();
    }

    public function ViewReceivableSummaryAnalysis(Request $request)
    {
        return $this->reportRepository->ViewReceivableSummaryAnalysis($request);
    }

    public function PrintReceivableSummaryAnalysisByCustomer(Request $request)
    {
        return $this->reportRepository->PrintReceivableSummaryAnalysisByCustomer($request);
    }

    public function GetExpenseAnalysis()
    {
        return $this->reportRepository->GetExpenseAnalysis();
    }

    public function ViewExpenseAnalysis(Request $request)
    {
        return $this->reportRepository->ViewExpenseAnalysis($request);
    }

    public function PrintBankReport(Request $request)
    {
        return $this->reportRepository->PrintBankReport($request);
    }

    public function ViewBankReport(Request $request)
    {
        return $this->reportRepository->ViewBankReport($request);
    }

    public function GeneralLedger()
    {
        return $this->reportRepository->GeneralLedger();
    }

    public function PrintGeneralLedger(Request $request)
    {
        return $this->reportRepository->PrintGeneralLedger($request);
    }

    public function Profit_loss()
    {
        return $this->reportRepository->Profit_loss();
    }

    public function PrintProfit_loss(Request $request)
    {
        return $this->reportRepository->PrintProfit_loss($request);
    }

    public function PrintProfit_loss_by_date(Request $request)
    {
        return $this->reportRepository->PrintProfit_loss_by_date($request);
    }

    public function Garage_value()
    {
        return $this->reportRepository->Garage_value();
    }

    public function PrintGarage_value(Request $request)
    {
        return $this->reportRepository->PrintGarage_value($request);
    }

    public function GetExpenseAnalysisByCategory()
    {
        return $this->reportRepository->GetExpenseAnalysisByCategory();
    }

    public function ViewExpenseAnalysisByCategory(Request $request)
    {
        return $this->reportRepository->ViewExpenseAnalysisByCategory($request);
    }

    public function GetExpenseAnalysisByEmployee()
    {
        return $this->reportRepository->GetExpenseAnalysisByEmployee();
    }

    public function ViewExpenseAnalysisByEmployee(Request $request)
    {
        return $this->reportRepository->ViewExpenseAnalysisByEmployee($request);
    }

    public function GetExpenseAnalysisBySupplier()
    {
        return $this->reportRepository->GetExpenseAnalysisBySupplier();
    }

    public function ViewExpenseAnalysisBySupplier(Request $request)
    {
        return $this->reportRepository->ViewExpenseAnalysisBySupplier($request);
    }

    public function GetSalesQuantitySummary()
    {
        return $this->reportRepository->GetSalesQuantitySummary();
    }

    public function PrintSalesQuantitySummary(Request $request)
    {
        return $this->reportRepository->PrintSalesQuantitySummary($request);
    }

    public function GetPurchaseQuantitySummary()
    {
        return $this->reportRepository->GetPurchaseQuantitySummary();
    }

    public function PrintPurchaseQuantitySummary(Request $request)
    {
        return $this->reportRepository->PrintPurchaseQuantitySummary($request);
    }

    public function GetInwardLoanStatement()
    {
        return $this->reportRepository->GetInwardLoanStatement();
    }

    public function PrintInwardLoanStatement(Request $request)
    {
        return $this->reportRepository->PrintInwardLoanStatement($request);
    }

    public function GetOutwardLoanStatement()
    {
        return $this->reportRepository->GetOutwardLoanStatement();
    }

    public function PrintOutwardLoanStatement(Request $request)
    {
        return $this->reportRepository->PrintOutwardLoanStatement($request);
    }

    public function GetDailyCashSummary()
    {
        return $this->reportRepository->GetDailyCashSummary();
    }

    public function PrintDailyCashSummary(Request $request)
    {
        return $this->reportRepository->PrintDailyCashSummary($request);
    }

    public function GetLoginActivity()
    {
        return $this->reportRepository->GetLoginActivity();
    }

    public function GetActivityReport()
    {
        return $this->reportRepository->GetActivityReport();
    }

    public function PrintActivityReport(Request $request)
    {
        return $this->reportRepository->PrintActivityReport($request);
    }

    public function GetLoginReport()
    {
        return $this->reportRepository->GetLoginReport();
    }

    public function PrintLoginReport(Request $request)
    {
        return $this->reportRepository->PrintLoginReport($request);
    }

    public function GetInwardLoanSummary()
    {
        return $this->reportRepository->GetInwardLoanSummary();
    }

    public function PrintInwardLoanSummary(Request $request)
    {
        return $this->reportRepository->PrintInwardLoanSummary($request);
    }

    public function GetOutwardLoanSummary()
    {
        return $this->reportRepository->GetOutwardLoanSummary();
    }

    public function PrintOutwardLoanSummary(Request $request)
    {
        return $this->reportRepository->PrintOutwardLoanSummary($request);
    }

    public function GetPaymentLedger()
    {
        return $this->reportRepository->GetPaymentLedger();
    }

    public function PrintPaymentLedger(Request $request)
    {
        return $this->reportRepository->PrintPaymentLedger($request);
    }

    public function GetYearlyProfitAndLoss()
    {
        return $this->reportRepository->GetYearlyProfitAndLoss();
    }

    public function PrintYearlyProfitAndLoss(Request $request)
    {
        return $this->reportRepository->PrintYearlyProfitAndLoss($request);
    }

    public function GetInventoryReport()
    {
        return $this->reportRepository->GetInventoryReport();
    }

    public function PrintInventoryReport(Request $request)
    {
        return $this->reportRepository->PrintInventoryReport($request);
    }

    public function PrintExpenseAnalysisByDate(Request $request)
    {
        return $this->reportRepository->PrintExpenseAnalysisByDate($request);
    }
}
