<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface IVaultRepositoryInterface
{
    public function index();

    public function create();

    public function store(Request $request);
}
