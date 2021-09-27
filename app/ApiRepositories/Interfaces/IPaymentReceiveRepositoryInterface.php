<?php


namespace App\ApiRepositories\Interfaces;


use App\Http\Requests\PaymentReceiveRequest;
use Illuminate\Http\Request;

interface IPaymentReceiveRepositoryInterface
{
    public  function all();

    public  function paginate($page_no,$page_size);

    public  function insert(Request $request);

    public  function customer_payments_push($Id);

}
