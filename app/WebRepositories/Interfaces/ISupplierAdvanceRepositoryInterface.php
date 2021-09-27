<?php


namespace App\WebRepositories\Interfaces;


use App\Http\Requests\SupplierAdvanceRequest;
use Illuminate\Http\Request;

interface ISupplierAdvanceRepositoryInterface
{

    public function index();

    public function create();

    public function store(SupplierAdvanceRequest $supplierAdvanceRequest);

    public function update(Request $request, $Id);

    public function edit($Id);

    public function supplier_advances_push(Request $request, $Id);

}
