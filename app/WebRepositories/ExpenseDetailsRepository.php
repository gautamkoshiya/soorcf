<?php

namespace App\WebRepositories;


use App\Http\Requests\ExpenseDetailRequest;
use App\WebRepositories\Interfaces\IExpenseDetailsRepositoryInterface;
use Illuminate\Http\Request;

class ExpenseDetailsRepository implements IExpenseDetailsRepositoryInterface
{

    public function index()
    {
        // TODO: Implement index() method.
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function store(ExpenseDetailRequest $expenseDetailRequest)
    {
        // TODO: Implement store() method.
    }

    public function update(Request $request, $Id)
    {
        // TODO: Implement update() method.
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        // TODO: Implement edit() method.
    }

    public function delete(Request $request, $Id)
    {
        // TODO: Implement delete() method.
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }
}
