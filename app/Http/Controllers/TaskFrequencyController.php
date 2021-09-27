<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\ITaskFrequencyRepositoryInterface;
use Illuminate\Http\Request;

class TaskFrequencyController extends Controller
{
    private $taskFrequencyRepository;

    public function __construct(ITaskFrequencyRepositoryInterface $taskFrequencyRepository)
    {
        $this->taskFrequencyRepository = $taskFrequencyRepository;
    }

    public function index()
    {
        return $this->taskFrequencyRepository->index();
    }

    public function create()
    {
        return $this->taskFrequencyRepository->create();
    }

    public function store(Request $request)
    {
        return $this->taskFrequencyRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->taskFrequencyRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->taskFrequencyRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->taskFrequencyRepository->delete($request, $Id);
    }
}
