<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IDesignationRepositoryInterface;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    private $designationRepository;

    public function __construct(IDesignationRepositoryInterface $designationRepository)
    {
        $this->designationRepository = $designationRepository;
    }

    public function index()
    {
        return $this->designationRepository->index();
    }

    public function create()
    {
        return $this->designationRepository->create();
    }

    public function store(Request $request)
    {
        return $this->designationRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->designationRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->designationRepository->update($request, $Id);
    }

    public function destroy($Id)
    {
        return $this->designationRepository->delete($Id);
    }
}
