<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface IOtherStockRepositoryInterface
{
    public function index();

    public function create();

    public function store(Request $request);

    public function delete($Id);
}
