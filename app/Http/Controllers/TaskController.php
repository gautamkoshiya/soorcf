<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\ITaskRepositoryInterface;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private $taskRepository;

    public function __construct(ITaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function index()
    {
        return $this->taskRepository->index();
    }

    public function ChangeTaskStatus($Id)
    {
        return $this->taskRepository->ChangeTaskStatus($Id);
    }

    public function review_task()
    {
        return $this->taskRepository->review_task();
    }

    public function get_review_task(Request $request)
    {
        return $this->taskRepository->get_review_task($request);
    }

    public function task_delete_post(Request $request)
    {
        return $this->taskRepository->task_delete_post($request);
    }

    public function edit($Id)
    {
        return $this->taskRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->taskRepository->update($request, $Id);
    }
}
