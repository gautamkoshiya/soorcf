<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface ITaskMasterRepositoryInterface
{
    public function index();

    public function create();

    public function store(Request $request);

    public function update(Request $request, $Id);

    public function edit($Id);

    public function task_master_delete_post($Id);
}
