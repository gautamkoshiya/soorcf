<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\SupplierAdvanceRequest;
use Illuminate\Http\Request;

interface ISupplierAdvanceRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function update(Request $request,$Id);

    public  function getById($Id);

    public  function supplier_advances_push($Id);

    public  function delete(Request $request,$Id);

    public  function ActivateDeactivate($Id);
}
