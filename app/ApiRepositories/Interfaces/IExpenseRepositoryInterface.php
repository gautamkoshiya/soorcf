<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\ExpenseRequest;
use Illuminate\Http\Request;

interface IExpenseRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(Request $request,$Id);

    public  function getById($Id);

    public  function delete(Request $request,$Id);

    public  function ExpenseDocumentsUpload(Request $request);

    public  function print($Id);

    public  function ActivateDeactivate($Id);
}
