<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\ITaskMasterRepositoryInterface;
use Illuminate\Http\Request;

class TaskMasterController extends Controller
{
    private $taskMasterRepository;

    public function __construct(ITaskMasterRepositoryInterface $taskMasterRepository)
    {
        $this->taskMasterRepository = $taskMasterRepository;
    }

    public function index()
    {
        return $this->taskMasterRepository->index();
    }

    public function create()
    {
        return $this->taskMasterRepository->create();
    }

    public function store(Request $request)
    {
        return $this->taskMasterRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->taskMasterRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->taskMasterRepository->update($request, $Id);
    }

    public function task_master_delete_post(Request $request)
    {
        return $this->taskMasterRepository->task_master_delete_post($request);
    }
}
