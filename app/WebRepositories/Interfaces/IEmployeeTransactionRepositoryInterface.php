<?php

namespace App\WebRepositories\Interfaces;

use Illuminate\Http\Request;

interface IEmployeeTransactionRepositoryInterface
{
    public function index();

    public function create();

    public function store(Request $request);

    public function getById($Id);
}
