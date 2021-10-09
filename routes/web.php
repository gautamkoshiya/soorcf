<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::prefix('admin')->middleware(['auth'])->group(function () {
Route::middleware(['auth'])->group(function () {
route::resource('companies','CompanyController');
route::get('/','AdminController@index');

Route::get('/admin','AdminController@index')->name('admin');
route::get('/register','AdminController@register');

route::get('/UserChangePassword','UserController@UserChangePassword')->name('UserChangePassword');
route::PUT('UserUpdatePassword/{id}','UserController@UserUpdatePassword')->name('UserUpdatePassword');
route::get('UpdateCompanySession/{id}','UserController@UpdateCompanySession')->name('UpdateCompanySession');
route::get('GetDashboardData/{id}','AdminController@GetDashboardData')->name('GetDashboardData');

route::resource('customers','CustomerController');
route::post('customer_delete_post', 'CustomerController@customer_delete_post' );
route::get('customer_app', 'CustomerController@customer_app' )->name('customer_app');
route::POST('/CheckCustomerExist','CustomerController@CheckCustomerExist');
route::get('ChangeCustomerStatus/{id}','CustomerController@ChangeCustomerStatus');
route::get('ChangeCustomerAppStatus/{id}','CustomerController@ChangeCustomerAppStatus')->name('ChangeCustomerAppStatus');
route::get('ChangeCustomerAppDataEdit/{id}','CustomerController@ChangeCustomerAppDataEdit')->name('ChangeCustomerAppDataEdit');
route::PUT('ChangeCustomerAppData/{id}','CustomerController@ChangeCustomerAppData')->name('ChangeCustomerAppData');
route::get('GetCustomerAcquisitionAnalysis','CustomerController@GetCustomerAcquisitionAnalysis')->name('GetCustomerAcquisitionAnalysis');
route::post('ViewCustomerAcquisitionAnalysis','CustomerController@ViewCustomerAcquisitionAnalysis')->name('ViewCustomerAcquisitionAnalysis');
route::resource('financer','FinancerController');
route::get('customerDetails/{id}','CustomerController@customerDetails');
route::get('salesCustomerDetails/{id}','CustomerController@salesCustomerDetails');
route::get('getLedgerCustomers','CustomerController@getLedgerCustomers');

route::resource('company_types','CompanyTypeController');
route::resource('payment_types','PaymentTypeController');
route::resource('payment_terms','PaymentTermController');

route::resource('suppliers','SupplierController');
route::post('supplier_delete_post', 'SupplierController@supplier_delete_post' );
route::get('getLedgerSuppliers','SupplierController@getLedgerSuppliers');

route::resource('projects','ProjectController');
route::get('ChangeProjectStatus/{id}','ProjectController@ChangeProjectStatus');

route::resource('customer_advances','CustomerAdvanceController');
route::get('customer_advances_push/{Id}','CustomerAdvanceController@customer_advances_push');
route::get('customer_advances_get_disburse/{Id}','CustomerAdvanceController@customer_advances_get_disburse')->name('customer_advances_get_disburse');
route::POST('customer_advances_save_disburse','CustomerAdvanceController@customer_advances_save_disburse')->name('customer_advances_save_disburse');
route::POST('/CheckCustomerAdvanceReferenceExist','CustomerAdvanceController@CheckCustomerAdvanceReferenceExist');
route::get('getCustomerAdvanceDetail/{Id}','CustomerAdvanceController@getCustomerAdvanceDetail');
route::get('cancelCustomerAdvance/{Id}','CustomerAdvanceController@cancelCustomerAdvance');
route::post('customer_advance_delete_post', 'CustomerAdvanceController@customer_advance_delete_post' )->name('customer_advance_delete_post');
Route::post('all_customer_advance', 'CustomerAdvanceController@all_customer_advance' )->name('all_customer_advance');

route::resource('supplier_advances','SupplierAdvanceController');
route::get('supplier_advances_push/{Id}','SupplierAdvanceController@supplier_advances_push');
route::get('supplier_advances_get_disburse/{Id}','SupplierAdvanceController@supplier_advances_get_disburse')->name('supplier_advances_get_disburse');
route::POST('supplier_advances_save_disburse','SupplierAdvanceController@supplier_advances_save_disburse')->name('supplier_advances_save_disburse');
route::POST('/CheckSupplierAdvanceReferenceExist','SupplierAdvanceController@CheckSupplierAdvanceReferenceExist');
route::get('getSupplierAdvanceDetail/{Id}','SupplierAdvanceController@getSupplierAdvanceDetail');
route::get('cancelSupplierAdvance/{Id}','SupplierAdvanceController@cancelSupplierAdvance');
route::post('supplier_advance_delete_post', 'SupplierAdvanceController@supplier_advance_delete_post' )->name('supplier_advance_delete_post');
Route::post('all_supplier_advance', 'SupplierAdvanceController@all_supplier_advance' )->name('all_supplier_advance');

route::resource('vehicles','VehicleController');
route::get('getVehicleList','VehicleController@getVehicleList')->name('getVehicleList');
route::post('PrintVehicleList','VehicleController@PrintVehicleList')->name('PrintVehicleList');
route::get('ChangeVehicleStatus/{id}','VehicleController@ChangeVehicleStatus');
route::post('vehicle_delete_post', 'VehicleController@vehicle_delete_post' );

route::POST('/CheckVehicleExist','VehicleController@CheckVehicleExist');
route::resource('drivers','DriverController');
route::resource('users','UserController');
route::resource('roles','RoleController');
route::resource('banks','BankController');
route::resource('deposits','DepositController');
route::get('Deposit_delete/{Id}','DepositController@Deposit_delete');
route::post('deposit_delete_post', 'DepositController@deposit_delete_post');
route::resource('withdrawals','WithdrawalController');
route::get('Withdrawal_delete/{Id}','WithdrawalController@Withdrawal_delete');
route::post('withdrawal_delete_post', 'WithdrawalController@withdrawal_delete_post' );
route::resource('bank_to_banks','BankToBankController');
route::get('Bank_to_banks_delete/{Id}','BankToBankController@Bank_to_banks_delete');
route::get('getBankAccountDetail/{id}','BankController@getBankAccountDetail');
route::resource('countries','CountryController');
route::resource('states','StateController');
route::resource('cities','CityController');

route::resource('regions','RegionController');
route::get('locationDetails/{id}','RegionController@locationDetails');

route::resource('units','UnitController');
route::resource('gsts','GstController');
route::resource('report_file_types','ReportFileTypeController');
route::resource('products','ProductController');
route::get('productsDetails/{Id}','ProductController@productDetails');

route::resource('designations','DesignationController');
route::resource('departments','DepartmentController');
route::resource('genders','GenderController');
route::resource('nationalities','NationalityController');

////////// file manager //////////////////////////////
route::resource('file_managers','FileManagerController');
Route::post('all_files', 'FileManagerController@all_files' )->name('all_files');
route::post('file_manager_delete_post', 'FileManagerController@file_manager_delete_post' );
route::get('trash_files', 'FileManagerController@trash_files')->name('trash_files');

route::resource('task_frequencies','TaskFrequencyController');

////////// task master //////////////////////////////
route::resource('task_masters','TaskMasterController');
route::post('task_master_delete_post', 'TaskMasterController@task_master_delete_post' );

////////// task //////////////////////////////////////
route::resource('tasks','TaskController');
route::get('ChangeTaskStatus/{id}','TaskController@ChangeTaskStatus');
Route::get('review_task', 'TaskController@review_task' )->name('review_task');
Route::post('get_review_task', 'TaskController@get_review_task' )->name('get_review_task');
route::post('task_delete_post', 'TaskController@task_delete_post');

////////// purchase section //////////////////////////
route::resource('purchases','PurchaseController');
route::get('purchasePrint/{id}','PurchaseController@print');
route::get('purchase_delete/{Id}','PurchaseController@purchase_delete');
route::get('getPurchasePaymentDetail/{Id}','PurchaseController@getPurchasePaymentDetail');
route::get('getAveragePurchasePrice/{Id}','PurchaseController@getAveragePurchasePrice');
route::get('supplierDetails/{id}','SupplierController@supplierDetails');
route::get('getRemainingQtyOfOpenLpo/{id}','SupplierController@getRemainingQtyOfOpenLpo');
Route::post('purchaseUpdate/{Id}','PurchaseController@purchaseUpdate');
route::post('purchase_delete_post', 'PurchaseController@purchase_delete_post' );
route::POST('/CheckPurchasePadExist','PurchaseController@CheckPurchasePadExist');
Route::post('all_purchase', 'PurchaseController@all_purchase' )->name('all_purchase');

//////////////expense /////////////////
route::resource('expenses','ExpenseController');
Route::post('all_expenses', 'ExpenseController@all_expenses' )->name('all_expenses');
route::resource('expense_categories','ExpenseCategoryController');
route::post('expense_category_delete_post', 'ExpenseCategoryController@expense_category_delete_post' );
route::post('expenseUpdate/{id}','ExpenseController@expenseUpdate');
route::POST('/CheckExpenseReferenceExist','ExpenseController@CheckExpenseReferenceExist');

route::get('getExpenseDetail/{Id}','ExpenseController@getExpenseDetail');
route::get('Expense_delete/{Id}','ExpenseController@Expense_delete');
route::post('expense_delete_post', 'ExpenseController@expense_delete_post' );

route::resource('employees','EmployeeController');
route::get('deleteEmployee/{id}','EmployeeController@deleteEmployee');
route::get('ChangeEmployeeStatus/{id}','EmployeeController@ChangeEmployeeStatus');
route::get('getEmployeeDetail/{Id}','EmployeeController@getEmployeeDetail');

////////////// sales /////////////////////
route::resource('sales','SaleController');
route::get('sales_delete/{Id}','SaleController@sales_delete');
route::post('salesUpdate/{Id}','SaleController@salesUpdate');
Route::post('all_sales', 'SaleController@all_sales' )->name('all_sales');
Route::post('all_sales_service', 'SaleController@all_sales_service' )->name('all_sales_service');
Route::post('store_sale_service', 'SaleController@store_sale_service' )->name('store_sale_service');
route::get('get_data','SaleController@get_data')->name('get_data');
route::get('get_today_sale','SaleController@get_today_sale')->name('get_today_sale');
route::get('get_sale_of_date','SaleController@get_sale_of_date')->name('get_sale_of_date');
route::post('view_sale_of_date', 'SaleController@view_sale_of_date' )->name('view_sale_of_date');
route::get('view_result_sale_of_date', 'SaleController@view_result_sale_of_date' )->name('view_result_sale_of_date');
route::get('getCustomerVehicleDetails/{$Id}','CustomerController@getCustomerVehicle');
route::get('getSalesByDate/{id}','SaleController@salesByDateDetails');
route::resource('customer_prices','CustomerPriceController');
route::POST('/CheckPadExist','SaleController@CheckPadExist');
route::POST('/CheckVehicleStatus','SaleController@CheckVehicleStatus');
route::get('getSalesPaymentDetail/{Id}','SaleController@getSalesPaymentDetail');
route::get('getSalesQuantityChart','SaleController@getSalesQuantityChart');
route::post('printSalesQuantityChart','SaleController@printSalesQuantityChart');
route::get('getSalesQuantityChartCustomer','SaleController@getSalesQuantityChartCustomer');
route::post('printSalesQuantityChartCustomer','SaleController@printSalesQuantityChartCustomer');
route::get('getTopTenCustomerByAmount','CustomerController@getTopTenCustomerByAmount');
route::get('getTopTenCustomerByQty','CustomerController@getTopTenCustomerByQty');
route::post('printTopTenCustomerByAmount','CustomerController@printTopTenCustomerByAmount');
route::post('printTopTenCustomerByQty','CustomerController@printTopTenCustomerByQty');
route::post('sales_delete_post', 'SaleController@sales_delete_post' )->name('sales_delete_post');
route::get('edit_sale_service/{Id}','SaleController@edit_sale_service')->name('edit_sale_service');
route::post('salesServiceUpdate/{Id}','SaleController@salesServiceUpdate')->name('salesServiceUpdate');

////////////// quotation /////////////////////
route::resource('quotations','QuotationController');
route::get('quotationCustomerDetails/{id}','QuotationController@quotationCustomerDetails');
route::get('deleteQuotation/{id}','QuotationController@deleteQuotation');
route::post('quotationUpdate/{Id}','QuotationController@quotationUpdate');
route::get('PrintQuotation/{id}','QuotationController@PrintQuotation')->name('PrintQuotation');
route::get('PrintQuotation1/{id}','QuotationController@PrintQuotation1')->name('PrintQuotation1');
Route::post('all_quotation', 'QuotationController@all_quotation' )->name('all_quotation');

////////////// Customer Advance Booking /////////////////////
route::resource('customer_advance_bookings','CustomerAdvanceBookingController');
route::get('getBookingDetail/{Id}','CustomerAdvanceBookingController@getBookingDetail');
route::post('all_bookings','CustomerAdvanceBookingController@all_bookings')->name('all_bookings');
route::post('CustomerBookingUpdate/{Id}','CustomerAdvanceBookingController@CustomerBookingUpdate');
route::get('CustomerBookingOverfilledDetails/{id}','CustomerAdvanceBookingController@CustomerBookingOverfilledDetails');
route::get('getAdvanceBookingReport','CustomerAdvanceBookingController@getAdvanceBookingReport')->name('getAdvanceBookingReport');
route::post('PrintAdvanceBookingReport','CustomerAdvanceBookingController@PrintAdvanceBookingReport')->name('PrintAdvanceBookingReport');

////////////// Other Stock /////////////////////
route::resource('other_stocks','OtherStockController');
route::get('deleteOtherStock/{id}','OtherStockController@deleteOtherStock');
route::get('GetOtherStockReport','OtherStockController@GetOtherStockReport')->name('GetOtherStockReport');
route::post('PrintOtherStockStatement','OtherStockController@PrintOtherStockStatement')->name('PrintOtherStockStatement');

////////////// LPO /////////////////////
route::resource('lpos','LpoController');
route::get('deleteLpo/{id}','LpoController@deleteLpo');
route::post('lpoUpdate/{Id}','LpoController@lpoUpdate');
route::get('PrintLpo/{id}','LpoController@PrintLpo')->name('PrintLpo');
route::get('lpoSupplierDetails/{id}','LpoController@lpoSupplierDetails');
Route::post('all_lpo', 'LpoController@all_lpo' )->name('all_lpo');

////////////// Tax Invoice /////////////////////
route::resource('tax_invoices','TaxInvoiceController');
route::get('deleteTaxInvoice/{id}','TaxInvoiceController@deleteTaxInvoice');
route::get('GetTaxInvoiceDetails/{id}','TaxInvoiceController@GetTaxInvoiceDetails');
route::post('SaveTaxInvoiceDetails','TaxInvoiceController@SaveTaxInvoiceDetails');
route::post('TaxInvoiceUpdate/{Id}','TaxInvoiceController@TaxInvoiceUpdate');
route::get('PrintTaxInvoice/{id}','TaxInvoiceController@PrintTaxInvoice')->name('PrintTaxInvoice');
route::get('getInvoiceNumberByProject/{id}','TaxInvoiceController@getInvoiceNumberByProject');
Route::post('all_tax_invoice', 'TaxInvoiceController@all_tax_invoice' )->name('all_tax_invoice');
route::get('GetTaxInvoiceReport','TaxInvoiceController@GetTaxInvoiceReport')->name('GetTaxInvoiceReport');
route::post('PrintTaxInvoiceReport','TaxInvoiceController@PrintTaxInvoiceReport')->name('PrintTaxInvoiceReport');

////////////// Purchase Invoice /////////////////////
route::resource('purchase_invoices','PurchaseInvoiceController');
route::get('deletePurchaseInvoice/{id}','PurchaseInvoiceController@deletePurchaseInvoice');
route::get('GetPurchaseInvoiceDetails/{id}','PurchaseInvoiceController@GetPurchaseInvoiceDetails');
route::post('SavePurchaseInvoiceDetails','PurchaseInvoiceController@SavePurchaseInvoiceDetails');
route::post('PurchaseInvoiceUpdate/{Id}','PurchaseInvoiceController@PurchaseInvoiceUpdate');
route::get('PrintPurchaseInvoice/{id}','PurchaseInvoiceController@PrintPurchaseInvoice')->name('PrintPurchaseInvoice');
Route::post('all_purchase_invoice', 'PurchaseInvoiceController@all_purchase_invoice' )->name('all_purchase_invoice');
route::get('GetPurchaseInvoiceReport','PurchaseInvoiceController@GetPurchaseInvoiceReport')->name('GetPurchaseInvoiceReport');
route::post('PrintPurchaseInvoiceReport','PurchaseInvoiceController@PrintPurchaseInvoiceReport')->name('PrintPurchaseInvoiceReport');

////////////// Delivery Note /////////////////////
route::resource('delivery_notes','DeliveryNoteController');
route::get('deleteDeliveryNote/{id}','DeliveryNoteController@deleteDeliveryNote');
route::post('DeliveryNoteUpdate/{Id}','DeliveryNoteController@DeliveryNoteUpdate');
route::get('PrintDeliveryNote/{id}','DeliveryNoteController@PrintDeliveryNote')->name('PrintDeliveryNote');
Route::post('all_delivery_note', 'DeliveryNoteController@all_delivery_note' )->name('all_delivery_note');

////////////// Proforma Invoice /////////////////////
route::resource('proforma_invoices','ProformaInvoiceController');
route::get('deleteProformaInvoice/{id}','ProformaInvoiceController@deleteProformaInvoice');
route::post('ProformaInvoiceUpdate/{Id}','ProformaInvoiceController@ProformaInvoiceUpdate');
route::get('PrintProformaInvoice/{id}','ProformaInvoiceController@PrintProformaInvoice')->name('PrintProformaInvoice');
Route::post('all_proforma', 'ProformaInvoiceController@all_proforma' )->name('all_proforma');

//////////////// meterReading ///////////////
route::resource('meter_readers','MeterReaderController');
route::resource('meter_readings','MeterReadingController');
route::post('meterReadingUpdate/{Id}','MeterReadingController@meterReadingUpdate');
route::get('getMeterReadingDetail/{Id}','MeterReadingController@getMeterReadingDetail');
route::get('cancel_meter_reading/{Id}','MeterReadingController@cancel_meter_reading');
route::post('meter_reading_delete_post', 'MeterReadingController@meter_reading_delete_post' );
Route::post('all_meter', 'MeterReadingController@all_meter' )->name('all_meter');

/////// loan ///////////////
route::resource('loans','LoanController');
route::get('customerRemaining/{Id}','LoanController@customerRemaining');
route::get('employeeRemaining/{Id}','LoanController@employeeRemaining');

/////// loan ///////////////
route::resource('investor','InvestorController');
route::get('getInvestorForCompany/{Id}','InvestorController@getInvestorForCompany')->name('getInvestorForCompany');
route::resource('investor_transactions','InvestorTransactionController');
route::post('investor_transaction_delete_post', 'InvestorTransactionController@investor_transaction_delete_post');
route::get('InvestorReportByCompany','InvestorTransactionController@InvestorReportByCompany')->name('InvestorReportByCompany');
route::post('PrintInvestorReportByCompany','InvestorTransactionController@PrintInvestorReportByCompany')->name('PrintInvestorReportByCompany');

///////// vault /////////
route::resource('vaults','VaultController');
route::post('vault_delete_post', 'VaultController@vault_delete_post');
route::get('VaultReportByCompany','VaultController@VaultReportByCompany')->name('VaultReportByCompany');
route::post('PrintVaultReportByCompany','VaultController@PrintVaultReportByCompany')->name('PrintVaultReportByCompany');
route::get('getClosingVault/{Id}','VaultController@getClosingVault');
route::resource('investor_transactions','InvestorTransactionController');
route::post('investor_transaction_delete_post', 'InvestorTransactionController@investor_transaction_delete_post');

///////// salary /////////
route::resource('salaries','SalaryController');
route::get('getCompanyEmployee/{Id}','SalaryController@getCompanyEmployee');
route::get('printSalary/{Id}','SalaryController@printSalary');
route::resource('employee_transactions','EmployeeTransaction');
route::POST('/CheckAccountTransactionReferenceExist','EmployeeTransaction@CheckAccountTransactionReferenceExist');
route::post('employee_transaction_delete_post', 'EmployeeTransaction@employee_transaction_delete_post');
route::get('EmployeeAccountStatement','EmployeeTransaction@EmployeeAccountStatement')->name('EmployeeAccountStatement');
route::post('PrintEmployeeAccountStatement','EmployeeTransaction@PrintEmployeeAccountStatement')->name('PrintEmployeeAccountStatement');
route::get('GetEmployeeReceivable','EmployeeTransaction@GetEmployeeReceivable')->name('GetEmployeeReceivable');
route::get('PrintEmployeeReceivable','EmployeeTransaction@PrintEmployeeReceivable')->name('PrintEmployeeReceivable');
route::get('GetEmployeeLabourList','EmployeeTransaction@GetEmployeeLabourList')->name('GetEmployeeLabourList');
route::post('PrintEmployeeLabourList','EmployeeTransaction@PrintEmployeeLabourList')->name('PrintEmployeeLabourList');

route::resource('inward_loans','InwardLoanController');
route::PUT('inward_loan_push/{Id}','InwardLoanController@inward_loan_push');
route::get('inward_loan_payment/{Id}','InwardLoanController@inward_loan_payment');
route::PUT('inward_loan_save_payment/{Id}','InwardLoanController@inward_loan_save_payment');
route::post('inward_loan_delete_post', 'InwardLoanController@inward_loan_delete_post');

route::resource('outward_loans','OutwardLoanController');
route::PUT('outward_loan_push/{Id}','OutwardLoanController@outward_loan_push');
route::get('outward_loan_payment/{Id}','OutwardLoanController@outward_loan_payment');
route::PUT('outward_loan_save_payment/{Id}','OutwardLoanController@outward_loan_save_payment');
route::post('outward_loan_delete_post', 'InwardLoanController@outward_loan_delete_post');

route::resource('payment_receives','PaymentReceiveController');
route::post('payment_receivesUpdate','PaymentReceiveController@payment_receivesUpdate');
route::get('customer_payments_push/{Id}','PaymentReceiveController@customer_payments_push');
route::get('customerSaleDetails/{Id}','SaleController@customerSaleDetails');
route::get('getCustomerPaymentDetail/{Id}','PaymentReceiveController@getCustomerPaymentDetail');
route::get('printCustomerPaymentDetail/{Id}','PaymentReceiveController@printCustomerPaymentDetail');
route::get('cancelCustomerPayment/{Id}','PaymentReceiveController@cancelCustomerPayment');
route::POST('/CheckCustomerPaymentReferenceExist','PaymentReceiveController@CheckCustomerPaymentReferenceExist');
Route::post('all_payment_receives', 'PaymentReceiveController@all_payment_receives' )->name('all_payment_receives');
route::post('payment_receives_delete_post', 'PaymentReceiveController@payment_receives_delete_post' )->name('payment_receives_delete_post');

route::resource('supplier_payments','SupplierPaymentController');
route::get('supplier_payment_push/{Id}','SupplierPaymentController@supplier_payments_push');
route::get('supplierSaleDetails/{Id}','PurchaseController@supplierSaleDetails');
route::get('getSupplierPaymentDetail/{Id}','SupplierPaymentController@getSupplierPaymentDetail');
route::get('cancelSupplierPayment/{Id}','SupplierPaymentController@cancelSupplierPayment');
route::POST('/CheckSupplierPaymentReferenceExist','SupplierPaymentController@CheckSupplierPaymentReferenceExist');
route::post('supplier_payment_delete_post', 'SupplierPaymentController@supplier_payment_delete_post' )->name('supplier_payment_delete_post');
Route::post('all_supplier_payment', 'SupplierPaymentController@all_supplier_payment' )->name('all_supplier_payment');

////////reports////////////
route::get('GetCustomerStatement','ReportController@GetCustomerStatement')->name('GetCustomerStatement');
route::get('PrintCustomerStatement','ReportController@PrintCustomerStatement')->name('PrintCustomerStatement');
route::post('PrintCustomerStatementForDate','ReportController@PrintCustomerStatementForDate')->name('PrintCustomerStatementForDate');

route::get('GetReceivableSummaryAnalysis','ReportController@GetReceivableSummaryAnalysis')->name('GetReceivableSummaryAnalysis');
route::post('ViewReceivableSummaryAnalysis','ReportController@ViewReceivableSummaryAnalysis')->name('ViewReceivableSummaryAnalysis');
route::post('PrintReceivableSummaryAnalysisByCustomer','ReportController@PrintReceivableSummaryAnalysisByCustomer')->name('PrintReceivableSummaryAnalysisByCustomer');

route::get('GetExpenseAnalysis','ReportController@GetExpenseAnalysis')->name('GetExpenseAnalysis');
route::post('ViewExpenseAnalysis','ReportController@ViewExpenseAnalysis')->name('ViewExpenseAnalysis');
route::post('PrintExpenseAnalysisByDate','ReportController@PrintExpenseAnalysisByDate')->name('PrintExpenseAnalysisByDate');

route::get('GetExpenseAnalysisByCategory','ReportController@GetExpenseAnalysisByCategory')->name('GetExpenseAnalysisByCategory');
route::post('ViewExpenseAnalysisByCategory','ReportController@ViewExpenseAnalysisByCategory')->name('ViewExpenseAnalysisByCategory');

route::get('GetExpenseAnalysisByEmployee','ReportController@GetExpenseAnalysisByEmployee')->name('GetExpenseAnalysisByEmployee');
route::post('ViewExpenseAnalysisByEmployee','ReportController@ViewExpenseAnalysisByEmployee')->name('ViewExpenseAnalysisByEmployee');

route::get('GetExpenseAnalysisBySupplier','ReportController@GetExpenseAnalysisBySupplier')->name('GetExpenseAnalysisBySupplier');
route::post('ViewExpenseAnalysisBySupplier','ReportController@ViewExpenseAnalysisBySupplier')->name('ViewExpenseAnalysisBySupplier');

route::get('GetSupplierStatement','ReportController@GetSupplierStatement')->name('GetSupplierStatement');
route::get('PrintSupplierStatement','ReportController@PrintSupplierStatement')->name('PrintSupplierStatement');
route::post('PrintSupplierStatementForDate','ReportController@PrintSupplierStatementForDate')->name('PrintSupplierStatementForDate');

route::get('GetPaidAdvancesSummary','ReportController@GetPaidAdvancesSummary')->name('GetPaidAdvancesSummary');
route::get('PrintPaidAdvancesSummary','ReportController@PrintPaidAdvancesSummary')->name('PrintPaidAdvancesSummary');

route::get('GetReceivedAdvancesSummary','ReportController@GetReceivedAdvancesSummary')->name('GetReceivedAdvancesSummary');
route::get('PrintReceivedAdvancesSummary','ReportController@PrintReceivedAdvancesSummary')->name('PrintReceivedAdvancesSummary');

route::get('GetDetailCustomerStatement','ReportController@GetDetailCustomerStatement')->name('GetDetailCustomerStatement');
route::post('PrintDetailCustomerStatement','ReportController@PrintDetailCustomerStatement')->name('PrintDetailCustomerStatement');
route::post('PrintDailyCustomerStatement','ReportController@PrintDailyCustomerStatement')->name('PrintDailyCustomerStatement');
route::post('ViewDetailCustomerStatement','ReportController@ViewDetailCustomerStatement')->name('ViewDetailCustomerStatement');

route::get('GetDetailSupplierStatement','ReportController@GetDetailSupplierStatement')->name('GetDetailSupplierStatement');
route::post('PrintDetailSupplierStatement','ReportController@PrintDetailSupplierStatement')->name('PrintDetailSupplierStatement');
route::post('ViewDetailSupplierStatement','ReportController@ViewDetailSupplierStatement')->name('ViewDetailSupplierStatement');

route::get('SalesReport','ReportController@SalesReport')->name('SalesReport');
route::post('PrintSalesReport','ReportController@PrintSalesReport')->name('PrintSalesReport');

route::get('SalesReportByVehicle','ReportController@SalesReportByVehicle')->name('SalesReportByVehicle');
route::post('PrintSalesReportByVehicle','ReportController@PrintSalesReportByVehicle')->name('PrintSalesReportByVehicle');

route::get('SalesReportByCustomer','ReportController@SalesReportByCustomer')->name('SalesReportByCustomer');
route::post('PrintSalesReportByCustomer','ReportController@PrintSalesReportByCustomer')->name('PrintSalesReportByCustomer');

route::get('SalesReportByShift','ReportController@SalesReportByShift')->name('SalesReportByShift');
route::post('PrintSalesReportByShift','ReportController@PrintSalesReportByShift')->name('PrintSalesReportByShift');

route::get('PurchaseReport','ReportController@PurchaseReport')->name('PurchaseReport');
route::post('PrintPurchaseReport','ReportController@PrintPurchaseReport')->name('PrintPurchaseReport');

route::get('ExpenseReport','ReportController@ExpenseReport')->name('ExpenseReport');
route::post('PrintExpenseReport','ReportController@PrintExpenseReport')->name('PrintExpenseReport');
route::post('PrintCashExpenseReport','ReportController@PrintCashExpenseReport')->name('PrintCashExpenseReport');
route::get('ExpenseVatReport','ReportController@ExpenseVatReport')->name('ExpenseVatReport');
route::post('PrintVATExpenseReport','ReportController@PrintVATExpenseReport')->name('PrintVATExpenseReport');
route::post('PrintLandscapeExpenseReport','ReportController@PrintLandscapeExpenseReport')->name('PrintLandscapeExpenseReport');

route::get('CashReport','ReportController@CashReport')->name('CashReport');
route::post('PrintCashReport','ReportController@PrintCashReport')->name('PrintCashReport');
route::post('PrintExpenseCashReport','ReportController@PrintExpenseCashReport')->name('PrintExpenseCashReport');
route::post('PrintCashLogReport','ReportController@PrintCashLogReport')->name('PrintCashLogReport');
route::post('ViewCashReport','ReportController@ViewCashReport')->name('ViewCashReport');

route::get('BankReport','ReportController@BankReport')->name('BankReport');
route::post('PrintBankReport','ReportController@PrintBankReport')->name('PrintBankReport');
route::post('ViewBankReport','ReportController@ViewBankReport')->name('ViewBankReport');

route::get('GeneralLedger','ReportController@GeneralLedger')->name('GeneralLedger');
route::post('PrintGeneralLedger','ReportController@PrintGeneralLedger')->name('PrintGeneralLedger');

route::get('Profit_loss','ReportController@Profit_loss')->name('Profit_loss');
route::post('PrintProfit_loss','ReportController@PrintProfit_loss')->name('PrintProfit_loss');
route::post('PrintProfit_loss_by_date','ReportController@PrintProfit_loss_by_date')->name('PrintProfit_loss_by_date');

route::get('Garage_value','ReportController@Garage_value')->name('Garage_value');
route::post('PrintGarage_value','ReportController@PrintGarage_value')->name('PrintGarage_value');

route::get('GetSalesQuantitySummary','ReportController@GetSalesQuantitySummary')->name('GetSalesQuantitySummary');
route::post('PrintSalesQuantitySummary','ReportController@PrintSalesQuantitySummary')->name('PrintSalesQuantitySummary');

route::get('GetPurchaseQuantitySummary','ReportController@GetPurchaseQuantitySummary')->name('GetPurchaseQuantitySummary');
route::post('PrintPurchaseQuantitySummary','ReportController@PrintPurchaseQuantitySummary')->name('PrintPurchaseQuantitySummary');

route::get('GetDailyCashSummary','ReportController@GetDailyCashSummary')->name('GetDailyCashSummary');
route::post('PrintDailyCashSummary','ReportController@PrintDailyCashSummary')->name('PrintDailyCashSummary');

route::get('GetInwardLoanStatement','ReportController@GetInwardLoanStatement')->name('GetInwardLoanStatement');
route::post('PrintInwardLoanStatement','ReportController@PrintInwardLoanStatement')->name('PrintInwardLoanStatement');

route::get('GetOutwardLoanStatement','ReportController@GetOutwardLoanStatement')->name('GetOutwardLoanStatement');
route::post('PrintOutwardLoanStatement','ReportController@PrintOutwardLoanStatement')->name('PrintOutwardLoanStatement');

route::get('GetLoginActivity','ReportController@GetLoginActivity')->name('GetLoginActivity');
route::post('PrintDailyCashSummary','ReportController@PrintDailyCashSummary')->name('PrintDailyCashSummary');

route::get('GetLoginReport','ReportController@GetLoginReport')->name('GetLoginReport');
route::post('PrintLoginReport','ReportController@PrintLoginReport')->name('PrintLoginReport');

route::get('GetActivityReport','ReportController@GetActivityReport')->name('GetActivityReport');
route::post('PrintActivityReport','ReportController@PrintActivityReport')->name('PrintActivityReport');

route::get('GetInwardLoanSummary','ReportController@GetInwardLoanSummary')->name('GetInwardLoanSummary');
route::get('PrintInwardLoanSummary','ReportController@PrintInwardLoanSummary')->name('PrintInwardLoanSummary');

route::get('GetOutwardLoanSummary','ReportController@GetOutwardLoanSummary')->name('GetOutwardLoanSummary');
route::get('PrintOutwardLoanSummary','ReportController@PrintOutwardLoanSummary')->name('PrintOutwardLoanSummary');

route::get('GetPaymentLedger','ReportController@GetPaymentLedger')->name('GetPaymentLedger');
route::post('PrintPaymentLedger','ReportController@PrintPaymentLedger')->name('PrintPaymentLedger');

route::get('GetYearlyProfitAndLoss','ReportController@GetYearlyProfitAndLoss')->name('GetYearlyProfitAndLoss');
route::post('PrintYearlyProfitAndLoss','ReportController@PrintYearlyProfitAndLoss')->name('PrintYearlyProfitAndLoss');

route::get('GetInventoryReport','ReportController@GetInventoryReport')->name('GetInventoryReport');
route::post('PrintInventoryReport','ReportController@PrintInventoryReport')->name('PrintInventoryReport');
});

route::view('welcome','welcome');

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);
