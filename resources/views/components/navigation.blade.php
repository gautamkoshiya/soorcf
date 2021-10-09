<div>
    <nav class="sidebar-nav">
        <ul id="sidebarnav">
            </li>
            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-layout-grid2"></i><span class="hide-menu">Master</span></a>
                <ul aria-expanded="false" class="collapse">
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li class="border-bottom"><a href="{{ route('users.index') }}">Users list</a></li>
                    <li class="border-bottom"><a href="{{ route('employees.index') }}">Employees list</a></li>
                    <li class="border-bottom"><a href="{{ route('departments.index') }}">Departments</a></li>
                    <li class="border-bottom"><a href="{{ route('designations.index') }}">Designations</a></li>
                    <li class="border-bottom"><a href="{{ route('genders.index') }}">Gender</a></li>
                    <li class="border-bottom"><a href="{{ route('nationalities.index') }}">Nationality</a></li>
                    <li class="border-bottom"><a href="{{ route('customer_app') }}">Customer App</a></li>
                    @endif
                    <li class="border-bottom"><a href="{{ route('countries.index') }}">Countries list</a></li>
                    <li class="border-bottom"><a href="{{ route('states.index') }}">States list</a></li>
                    <li class="border-bottom"><a href="{{ route('cities.index') }}">Cities list</a></li>
                    <li class="border-bottom"><a href="{{ route('regions.index') }}">Regions list</a></li>
                    <li class="border-bottom"><a href="{{ route('GetLoginActivity') }}">Login Activity</a></li>
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li class="border-bottom"><a href="{{ route('other_stocks.index') }}">Other Stock</a></li>
                    {{--<li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Official Documentation</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('projects.index') }}">Projects</a></li>
                            <li class="border-bottom"><a href="{{ route('quotations.index') }}">Quotation</a></li>
                            <li class="border-bottom"><a href="{{ route('lpos.index') }}">LPO</a></li>
                            <li class="border-bottom"><a href="{{ route('proforma_invoices.index') }}">Proforma Invoice</a></li>
                            <li class="border-bottom"><a href="{{ route('tax_invoices.index') }}">Tax Invoice</a></li>
                            <li class="border-bottom"><a href="{{ route('delivery_notes.index') }}">Delivery Note</a></li>
                        </ul>
                    </li>--}}
                    @endif

{{--                    <li class="border-bottom"><a href="{{ route('company_types.index') }}">Company Type list</a></li>--}}
{{--                    <li class="border-bottom"><a href="{{ route('payment_types.index') }}">Payment Type list</a></li>--}}
                    @if(Session::get('role_name')=='superadmin')
                        <li class="border-bottom"><a href="{{ route('units.index') }}">Units list</a></li>
                        <li class="border-bottom"><a href="{{ route('gsts.index') }}">GST</a></li>
                        <li class="border-bottom"><a href="{{ route('task_frequencies.index') }}">Task Frequency</a></li>
                        <li class="border-bottom"><a href="{{ route('products.index') }}">Products list</a></li>
                        <li class="border-bottom"><a href="{{ route('companies.index') }}">Companies list</a></li>
                        <li class="border-bottom"><a href="{{ route('roles.index') }}">Roles list</a></li>
                        <li class="border-bottom"><a href="{{ route('banks.index') }}">Banks list</a></li>
                        <li class="border-bottom"><a href="{{ route('investor.index') }}">Investor list</a></li>
                        <li class="border-bottom"><a href="{{ route('salaries.index') }}">Salary</a></li>
                    @endif
                    <li><a href="{{ route('payment_terms.index') }}">Payment Terms list</a></li>
                </ul>
            </li>

            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
            <li > <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-clipboard"></i><span class="hide-menu">Official Documentation</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li class="border-bottom"><a href="{{ route('projects.index') }}">Projects</a></li>
                    <li class="border-bottom"><a href="{{ route('quotations.index') }}">Quotation</a></li>
                    <li class="border-bottom"><a href="{{ route('lpos.index') }}">LPO</a></li>
                    <li class="border-bottom"><a href="{{ route('proforma_invoices.index') }}">Proforma Invoice</a></li>
                    <li class="border-bottom"><a href="{{ route('tax_invoices.index') }}">Tax Invoice</a></li>
                    <li class="border-bottom"><a href="{{ route('purchase_invoices.index') }}">Purchase Invoice</a></li>
                    <li class="border-bottom"><a href="{{ route('delivery_notes.index') }}">Delivery Note</a></li>
                </ul>
            </li>
            @endif

            <li > <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-user"></i><span class="hide-menu">Contacts</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Customers</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('customers.create') }}">Add New Customer</a></li>
                            <li class="border-bottom"><a href="{{ route('customers.index') }}">Manage Customers</a></li>
                            <li class="border-bottom"><a href="{{ route('customer_prices.index') }}">Manage Prices</a></li>
                            <li class="border-bottom"><a href="{{ route('customer_advance_bookings.index') }}">Advance Booking</a></li>
                            <li><a href="{{ route('GetCustomerAcquisitionAnalysis') }}">Customer Acquisition Analysis</a></li>
                        </ul>
                    </li>
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Suppliers</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('suppliers.create') }}">Add New Supplier</a></li>
                            <li class="border-bottom"><a href="{{ route('suppliers.index') }}">Manage Suppliers</a></li>
                        </ul>
                    </li>
                    @endif

                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                        <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Financer</span>
                            </a>
                            <ul aria-expanded="false" class="collapse">
                                <li><a href="{{ route('financer.index') }}">Manage Financer</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </li>
            @if(Session::get('company_id') != 4 && Session::get('company_id') != 5 && Session::get('company_id') != 8)
            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-car"></i><span class="hide-menu">Vehicles</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li class="border-bottom"><a href="{{ route('vehicles.create') }}">Add new Vehicle</a></li>
                    <li class="border-bottom"><a href="{{ route('vehicles.index') }}">Manage Vehicles</a></li>
                    <li><a href="{{ route('getVehicleList') }}">Print Vehicles List</a></li>
                </ul>
            </li>
            @endif

            <li>
                <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i>
                    <span class="hide-menu">Utilities</span>
                </a>
                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom">
                        <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-slice"></i>
                            <span class="hide-menu">Tasks</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                                <li class="border-bottom"><a href="{{ route('tasks.index') }}">My Tasks</a></li>
                                <li class="border-bottom"><a href="{{ route('review_task') }}">Review Tasks</a></li>
                                <li><a href="{{ route('task_masters.index') }}">Manage Task</a></li>
                            @endif
                        </ul>
                    </li>
                    <li>
                        <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-folder"></i>
                            <span class="hide-menu">File Manager</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                                <li class="border-bottom"><a href="{{ route('file_managers.index') }}">File Manager</a></li>
                                <li class="border-bottom"><a href="{{ route('report_file_types.index') }}">Report File Types</a></li>
                                <li><a href="{{ route('trash_files') }}">Trash Files</a></li>
                            @endif
                        </ul>
                    </li>
                </ul>
            </li>

            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-drivers-license"></i><span class="hide-menu">Drivers</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a href="{{ route('drivers.create') }}">Add new Driver</a></li>
                    <li><a href="{{ route('drivers.index') }}">Manage Drivers</a></li>
                </ul>
            </li>

            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
            <li>
                <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-shopping-basket"></i><span class="hide-menu">Purchase</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a href="{{ route('purchases.create') }}">Add Purchase</a></li>
                    <li><a href="{{ route('purchases.index') }}">Manage Purchase</a></li>
                </ul>
            </li>
            @endif

            <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-cart-plus"></i><span class="hide-menu">Sales</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li class="border-bottom"><a href="{{ route('sales.create') }}">Add Sales</a></li>
                    <li class="border-bottom"><a href="{{ route('get_today_sale') }}">Today Sales</a></li>
                    <li class="border-bottom"><a href="{{ route('get_sale_of_date') }}">Sales of Date</a></li>
                    <li class="border-bottom"><a href="{{ route('sales.index') }}">Manage All Sales</a></li>
                    <li class="border-bottom"><a href="{{ URL('getSalesQuantityChart') }}">Sales Performance</a></li>
                    <li class="border-bottom"><a href="{{ URL('getSalesQuantityChartCustomer') }}">Customer Sales Performance</a></li>
                    <li class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Top Customers</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ URL('getTopTenCustomerByAmount') }}">By Amount</a></li>
                            <li><a href="{{ URL('getTopTenCustomerByQty') }}">By Qty</a></li>
                        </ul>
                    </li>
                </ul>
            </li>

            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-money"></i><span class="hide-menu">Expenses</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a href="{{ route('expenses.create') }}">Add Expenses</a></li>
                    <li class="border-bottom"><a href="{{ route('expenses.index') }}">Manage Expenses</a></li>
                    <li><a href="{{ route('expense_categories.index') }}">Expenses Categories</a></li>
                </ul>
            </li>
            @endif

            @if(Session::get('company_id') != 4 && Session::get('company_id') != 5 && Session::get('company_id') != 8)
            <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-sort-numeric-asc"></i><span class="hide-menu">Meter Readings</span></a>
                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a href="{{ route('meter_readers.index') }}">Add Meter</a></li>
                    <li  class="border-bottom"><a href="{{ route('meter_readings.create') }}">Add Meter Reading</a></li>
                    <li><a href="{{ route('meter_readings.index') }}">Manage Meter Records</a></li>
                </ul>
            </li>
            @endif

            <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-sort-numeric-asc"></i><span class="hide-menu">Advances</span></a>
                <ul aria-expanded="false" class="collapse">
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li  class="border-bottom"><a href="{{ route('supplier_advances.index') }}">Supplier Advances</a></li>
                    @endif
                    <li><a href="{{ route('customer_advances.index') }}">Customer Advances</a></li>
                </ul>
            </li>

            <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fa fa-bar-chart"></i><span class="hide-menu">Accounts</span></a>
                <ul aria-expanded="false" class="collapse">
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li class="border-bottom">
                        <a href="{{ route('supplier_payments.index') }}">Supplier Payment</a>
                    </li>
                    @endif
                    <li class="border-bottom">
                        <a href="{{ route('payment_receives.index') }}">Customer Receive</a>
                    </li>
                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li class="border-bottom">
                        <a href="{{ route('deposits.index') }}">Deposits</a>
                    </li>
                    <li class="border-bottom">
                        <a href="{{ route('withdrawals.index') }}">Withdrawal</a>
                    </li>
                    <li class="border-bottom">
                        <a href="{{ route('bank_to_banks.index') }}">Bank To Bank</a>
                    </li>
                    <li class="border-bottom"><a href="{{ route('inward_loans.index') }}">Inward Loan</a></li>
                    @endif
                    <li><a href="{{ route('outward_loans.index') }}">OutWard Loan</a></li>
                </ul>
            </li>

            <li>
                <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-files"></i><span class="hide-menu">Reports</span>
                </a>

                <ul aria-expanded="false" class="collapse">
                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-align-center"></i><span class="hide-menu">Summaries</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('GetCustomerStatement') }}">Receivable Summary</a></li>
{{--                            <li class="border-bottom"><a href="{{ route('GetReceivedAdvancesSummary') }}">Customer Advances Summary</a></li>--}}
                            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                            <li class="border-bottom"><a href="{{ route('GetSupplierStatement') }}">Payable Summary</a></li>
{{--                            <li class="border-bottom"><a href="{{ route('GetPaidAdvancesSummary') }}">Supplier Advance Summary</a></li>--}}
                            <li class="border-bottom"><a href="{{ route('GetSalesQuantitySummary') }}">Sales quantity Summary</a></li>
                            <li class="border-bottom"><a href="{{ route('GetPurchaseQuantitySummary') }}">Purchase quantity Summary</a></li>
                            <li class="border-bottom"><a href="{{ route('GetDailyCashSummary') }}">Daily Cash Summary</a></li>
                            <li class="border-bottom"><a href="{{ route('GetInwardLoanSummary') }}">Inward Loan Summary</a></li>
                            <li class="border-bottom"><a href="{{ route('GetOutwardLoanSummary') }}">Outward Loan Summary</a></li>
                            <li class="border-bottom"><a href="{{ route('GetReceivableSummaryAnalysis') }}">Receivable Summary Analysis</a></li>
                            <li class="border-bottom"><a href="{{ route('GetEmployeeReceivable') }}">Employee Receivable</a></li>
                            <li><a href="{{ route('GetEmployeeLabourList') }}">Labour List</a></li>
                            @endif
                        </ul>
                    </li>

                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-list"></i><span class="hide-menu">Statements</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('GetDetailCustomerStatement') }}">Customer Statement</a></li>
                            @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                            <li class="border-bottom"><a href="{{ route('GetDetailSupplierStatement') }}">Supplier Statement</a></li>
                            <li class="border-bottom"><a href="{{ route('GetInwardLoanStatement') }}">Inward Loan Statement</a></li>
                            <li><a href="{{ route('GetOutwardLoanStatement') }}">Outward Loan Statement</a></li>
                            @endif
                        </ul>
                    </li>

                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-arrow-right"></i><span class="hide-menu">Sales Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li class="border-bottom"><a href="{{ route('SalesReport') }}">Sales Date-To-Date</a></li>
                            <li class="border-bottom"><a href="{{ route('SalesReportByVehicle') }}">By Vehicle Date-to-Date</a></li>
                            <li class="border-bottom"><a href="{{ route('SalesReportByCustomer') }}">By Customer Date-to-Date</a></li>
                            <li><a href="{{ route('SalesReportByShift') }}">By Shift</a></li>
                        </ul>
                    </li>

                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-arrow-left"></i><span class="hide-menu">Purchase Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li  class="border-bottom"><a href="{{ route('PurchaseReport') }}">Purchase Date-To-Date</a></li>
                        </ul>
                        <li  class="border-bottom"><a href="{{ route('GetInventoryReport') }}"><i class="ti-layers-alt"></i> Inventory Report</a></li>
                    </li>
                    <li  class="border-bottom">
                        <a href="{{ route('getAdvanceBookingReport') }}" aria-expanded="false"><i class="ti-bookmark-alt"></i><span class="hide-menu">Advance Booking Report</span>
                        </a>
                    </li>
                    <li  class="border-bottom"><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-money"></i><span class="hide-menu">Expense Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li  class="border-bottom"><a href="{{ route('ExpenseReport') }}">Expense Date-To-Date</a></li>
                            <li  class="border-bottom"><a href="{{ route('GetExpenseAnalysis') }}">Expense Analysis</a></li>
                            <li  class="border-bottom"><a href="{{ route('GetExpenseAnalysisByCategory') }}">Expense Analysis By Category</a></li>
                            <li  class="border-bottom"><a href="{{ route('GetExpenseAnalysisByEmployee') }}">Expense Analysis By Employee</a></li>
                            <li  class="border-bottom"><a href="{{ route('GetExpenseAnalysisBySupplier') }}">Expense Analysis By Supplier</a></li>
                        </ul>
                    </li>
                    @endif

                    @if(Session::get('role_name')=='superadmin' || Session::get('role_name')=='admin')
                    <li><a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-key"></i><span class="hide-menu">Accounts Reports</span>
                        </a>
                        <ul aria-expanded="false" class="collapse">
                            <li  class="border-bottom">
                                <a href="{{ route('CashReport') }}">Cash Book</a>
                            </li>
                            <li  class="border-bottom">
                                <a href="{{ route('GetPaymentLedger') }}">Payment Ledger</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('Profit_loss') }}">Profit Loss Statement</a>
                            </li>
                            @if(Session::get('company_id') != 4 && Session::get('company_id') != 5 && Session::get('company_id') != 8)
                            <li class="border-bottom">
                                <a href="{{ route('Garage_value') }}">Garage Value</a>
                            </li>
                            @endif
                           {{-- <li class="border-bottom">
                                <a href="{{ route('GeneralLedger') }}">General Ledger</a>
                            </li>
                            <li class="border-bottom">
                                <a href="#">Cash flow</a>
                            </li>
                            <li class="border-bottom">
                                <a href="#">Trial balance</a>
                            </li>--}}
                            @if(Session::get('role_name')=='superadmin')
                            <li class="border-bottom">
                                <a href="{{ route('BankReport') }}">Bank Book</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('GetTaxInvoiceReport') }}">TaxInvoice Report (IN-VAT)</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('GetPurchaseInvoiceReport') }}">PurchaseInvoice Report (OUT-VAT)</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('ExpenseVatReport') }}">Expense (OUT-VAT)</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('GetActivityReport') }}">Activity</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('investor_transactions.index') }}">Investor Journal</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('employee_transactions.index') }}">Employee Journal</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('vaults.index') }}">Vault</a>
                            </li>
                            <li class="border-bottom">
                                <a href="{{ route('GetYearlyProfitAndLoss') }}">Yearly P&L</a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif
                </ul>
            </li>
            <li class="nav-small-cap"></li>
        </ul>
    </nav>
</div>
