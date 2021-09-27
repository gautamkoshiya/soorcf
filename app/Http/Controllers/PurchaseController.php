<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IPurchaseRepositoryInterface;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    private $purchaseRepository;

    public function __construct(IPurchaseRepositoryInterface $purchaseRepository)
    {
        $this->purchaseRepository = $purchaseRepository;
    }

    public function index()
    {
        return $this->purchaseRepository->index();
    }

    public function all_purchase(Request $request)
    {
        return $this->purchaseRepository->all_purchase($request);
    }

    public function create()
    {
        return $this->purchaseRepository->create();
    }

    public function store(PurchaseRequest $purchaseRequest)
    {
        return $this->purchaseRepository->store($purchaseRequest);
    }

    public function show(Purchase $purchase)
    {
        //
    }

    public function edit($Id)
    {
        return $this->purchaseRepository->edit($Id);
    }

    public function print($id)
    {
        return $this->purchaseRepository->print($id);
    }

    public function purchaseUpdate(Request $request, $Id)
    {
        return $this->purchaseRepository->update($request, $Id);
    }

    public function getPurchasePaymentDetail($Id)
    {
        return $this->purchaseRepository->getPurchasePaymentDetail($Id);
    }

    public function getAveragePurchasePrice($Id)
    {
        return $this->purchaseRepository->getAveragePurchasePrice($Id);
    }

    public function purchase_delete_post(Request $request)
    {
        return $this->purchaseRepository->purchase_delete_post($request);
    }

    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    public function destroy(Purchase $purchase)
    {
        //
    }

    public function supplierSaleDetails($Id)
    {
        return $this->purchaseRepository->supplierSaleDetails($Id);
    }

    public function purchase_delete($id)
    {
        return $this->purchaseRepository->delete($id);
    }

    public function CheckPurchasePadExist(Request $request)
    {
        return $this->purchaseRepository->CheckPurchasePadExist($request);
    }
}
