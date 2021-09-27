<?php

namespace App\WebRepositories\Interfaces;


use App\Http\Requests\ExpenseDetailRequest;
use Illuminate\Http\Request;

interface IExpenseDetailsRepositoryInterface
{

    public function index();

    public function create();

    public function store(ExpenseDetailRequest $expenseDetailRequest);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);

    public function  restore($Id);

    public function trashed();

}
