<?php

namespace App\WebRepositories\Interfaces;

use Illuminate\Http\Request;

interface IDesignationRepositoryInterface
{
    public function index();

    public function create();

    public function store(Request $request);

    public function update(Request $request, $id);

    public function edit($id);

    public function delete($id);
}
