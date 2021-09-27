<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\WebRepositories\Interfaces\ICustomerRepositoryInterface;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private $customerRepository;

    public function __construct(ICustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function index()
    {
        return $this->customerRepository->index();
    }

    public function customer_app()
    {
        return $this->customerRepository->customer_app();
    }

    public function create()
    {
        return $this->customerRepository->create();
    }

    public function store(CustomerRequest $customerRequest)
    {
        return $this->customerRepository->store($customerRequest);
    }

    public function show(Customer $customer)
    {
        //
    }

    public function edit($Id)
    {
        return $this->customerRepository->edit($Id);
    }

    public function ChangeCustomerAppDataEdit($Id)
    {
        return $this->customerRepository->ChangeCustomerAppDataEdit($Id);
    }

    public function ChangeCustomerStatus($Id)
    {
        return $this->customerRepository->ChangeCustomerStatus($Id);
    }

    public function ChangeCustomerAppStatus($Id)
    {
        return $this->customerRepository->ChangeCustomerAppStatus($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->customerRepository->update($request, $Id);
    }

    public function ChangeCustomerAppData(Request $request, $Id)
    {
        return $this->customerRepository->ChangeCustomerAppData($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->customerRepository->delete($request, $Id);
    }

    public function customer_delete_post(Request $request)
    {
        return $this->customerRepository->customer_delete_post($request);
    }

    public function customerDetails($id)
    {
        return $this->customerRepository->customerDetails($id);
    }

    public function salesCustomerDetails($id)
    {
        return $this->customerRepository->salesCustomerDetails($id);
    }

    public function GetCustomerAcquisitionAnalysis()
    {
        return $this->customerRepository->GetCustomerAcquisitionAnalysis();
    }

    public function ViewCustomerAcquisitionAnalysis(Request $request)
    {
        return $this->customerRepository->ViewCustomerAcquisitionAnalysis($request);
    }

    public function CheckCustomerExist(Request $request)
    {
        return $this->customerRepository->CheckCustomerExist($request);
    }

    public function getLedgerCustomers()
    {
        return $this->customerRepository->getLedgerCustomers();
    }

    public function getTopTenCustomerByAmount()
    {
        return $this->customerRepository->getTopTenCustomerByAmount();
    }

    public function getTopTenCustomerByQty()
    {
        return $this->customerRepository->getTopTenCustomerByQty();
    }

    public function printTopTenCustomerByAmount(Request $request)
    {
        return $this->customerRepository->printTopTenCustomerByAmount($request);
    }

    public function printTopTenCustomerByQty(Request $request)
    {
        return $this->customerRepository->printTopTenCustomerByQty($request);
    }
}
