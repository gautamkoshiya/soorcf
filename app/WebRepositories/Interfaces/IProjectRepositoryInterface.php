<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface IProjectRepositoryInterface
{
    public function index();

    public function create();

    public function store(Request $request);

    public function show($Id);

    public function edit($Id);

    public function update(Request $request, $Id);

    public function destroy($Id);
}
