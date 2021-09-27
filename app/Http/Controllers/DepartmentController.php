<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IDepartmentRepositoryInterface;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    private $departmentRepository;

    public function __construct(IDepartmentRepositoryInterface $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    public function index()
    {
        return $this->departmentRepository->index();
    }

    public function create()
    {
        return $this->departmentRepository->create();
    }

    public function store(Request $request)
    {
        return $this->departmentRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->departmentRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->departmentRepository->update($request, $Id);
    }

    public function destroy($Id)
    {
        return $this->departmentRepository->delete($Id);
    }
}
