<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerAdvanceRequest;
use App\Models\CustomerAdvance;
use App\WebRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use Illuminate\Http\Request;

class CustomerAdvanceController extends Controller
{
    private $customerAdvanceRepository;

    public function __construct(ICustomerAdvanceRepositoryInterface $customerAdvanceRepository)
    {
        $this->customerAdvanceRepository = $customerAdvanceRepository;
    }

    public function index()
    {
        return $this->customerAdvanceRepository->index();
    }

    public function all_customer_advance(Request $request)
    {
        return $this->customerAdvanceRepository->all_customer_advance($request);
    }

    public function create()
    {
        return $this->customerAdvanceRepository->create();
    }

    public function store(CustomerAdvanceRequest $customerAdvanceRequest)
    {
        return $this->customerAdvanceRepository->store($customerAdvanceRequest);
    }

    public function show($Id)
    {
        return $this->customerAdvanceRepository->getById($Id);
    }

    public function edit($Id)
    {
        return $this->customerAdvanceRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->customerAdvanceRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->customerAdvanceRepository->delete($request, $Id);
    }

    public function customer_advance_delete_post(Request $request)
    {
        return $this->customerAdvanceRepository->customer_advance_delete_post($request);
    }

    public function customer_advances_push(Request $request,$Id)
    {
        return $this->customerAdvanceRepository->customer_advances_push($request, $Id);
    }

    public function customer_advances_get_disburse($Id)
    {
        return $this->customerAdvanceRepository->customer_advances_get_disburse($Id);
    }

    public function customer_advances_save_disburse(Request $request)
    {
        return $this->customerAdvanceRepository->customer_advances_save_disburse($request);
    }

    public function CheckCustomerAdvanceReferenceExist(Request $request)
    {
        return $this->customerAdvanceRepository->CheckCustomerAdvanceReferenceExist($request);
    }

    public function cancelCustomerAdvance($id)
    {
        return $this->customerAdvanceRepository->cancelCustomerAdvance($id);
    }

    public function getCustomerAdvanceDetail($Id)
    {
        return $this->customerAdvanceRepository->getCustomerAdvanceDetail($Id);
    }
}
