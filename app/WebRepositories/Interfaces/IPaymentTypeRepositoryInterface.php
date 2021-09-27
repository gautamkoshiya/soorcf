<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\CompanyRequest;
use App\Http\Requests\PaymentTypeRequest;
use Illuminate\Http\Request;

interface IPaymentTypeRepositoryInterface
{
    public function index();

    public function create();

    public function store(PaymentTypeRequest $paymentTypeRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();
}
