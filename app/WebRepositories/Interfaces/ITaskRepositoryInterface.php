<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface ITaskRepositoryInterface
{
    public function index();

    public function update(Request $request, $Id);

    public function edit($Id);

    public function task_delete_post($Id);
}
