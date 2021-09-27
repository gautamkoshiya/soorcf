<?php

namespace App\Http\Controllers;

use App\Models\SupplierPayment;
use App\WebRepositories\Interfaces\ISupplierPaymentRepositoryInterface;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    private $supplierPaymentRepository;
    public function __construct(ISupplierPaymentRepositoryInterface $supplierPaymentRepository)
    {
        $this->supplierPaymentRepository = $supplierPaymentRepository;
    }

    public function index()
    {
        return $this->supplierPaymentRepository->index();
    }

    public function create()
    {
        return $this->supplierPaymentRepository->create();
    }

    public function store(Request $request)
    {
        return $this->supplierPaymentRepository->store($request);
    }

    public function show($Id)
    {
        return $this->supplierPaymentRepository->getById($Id);
    }

    public function getSupplierPaymentDetail($Id)
    {
        return $this->supplierPaymentRepository->getSupplierPaymentDetail($Id);
    }

    public function edit($Id)
    {
        return $this->supplierPaymentRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->supplierPaymentRepository->update($request, $Id);
    }

    public function destroy(SupplierPayment $supplierPayment)
    {
        //
    }

    public function supplier_payment_delete_post(Request $request)
    {
        return $this->supplierPaymentRepository->supplier_payment_delete_post($request);
    }

    public function supplier_payments_push($Id)
    {
        return $this->supplierPaymentRepository->supplier_payments_push($Id);
    }

    public function CheckSupplierPaymentReferenceExist(Request $request)
    {
        return $this->supplierPaymentRepository->CheckSupplierPaymentReferenceExist($request);
    }

    public function cancelSupplierPayment($id)
    {
        return $this->supplierPaymentRepository->cancelSupplierPayment($id);
    }

    public function all_supplier_payment(Request $request)
    {
        return $this->supplierPaymentRepository->all_supplier_payment($request);
    }
}
