<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IProjectRepositoryInterface;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    private $projectRepository;

    public function __construct(IProjectRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function index()
    {
        return $this->projectRepository->index();
    }

    public function create()
    {
        return $this->projectRepository->create();
    }

    public function store(Request $request)
    {
        return $this->projectRepository->store($request);
    }

    public function show($id)
    {
        return $this->projectRepository->show($id);
    }

    public function edit($id)
    {
        return $this->projectRepository->edit($id);
    }

    public function update(Request $request, $id)
    {
        return $this->projectRepository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->projectRepository->destroy($id);
    }

    public function ChangeProjectStatus($Id)
    {
        return $this->projectRepository->ChangeProjectStatus($Id);
    }
}
