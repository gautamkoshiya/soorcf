<?php

namespace App\WebRepositories\Interfaces;

use Illuminate\Http\Request;

interface IGstRepositoryInterface
{
    public function index();

    public function create();

    public function store(Request $request);

    public function update(Request $request, $Id);

    public function getById($Id);

    public function edit($Id);

    public function delete(Request $request, $Id);
}
