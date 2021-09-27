<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\CustomerAdvanceRequest;
use Illuminate\Http\Request;

interface ICustomerAdvanceRepositoryInterface
{
    public function index();

    public function create();

    public function store(CustomerAdvanceRequest $customerAdvanceRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function customer_advances_push(Request $request, $Id);
}
