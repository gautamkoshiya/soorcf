<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface IInvestorTransactionRepositoryInterface
{
    public function index();

    public function create();

    public function store(Request $request);

    public function getById($Id);

}
