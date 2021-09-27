<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\ISalaryRepositoryInterface;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    private $salaryRepository;

    public function __construct(ISalaryRepositoryInterface $salaryRepository)
    {
        $this->salaryRepository = $salaryRepository;
    }

    public function index()
    {
        return $this->salaryRepository->index();
    }

    public function create()
    {
        return $this->salaryRepository->create();
    }

    public function store(Request $request)
    {
        return $this->salaryRepository->store($request);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function getCompanyEmployee($id)
    {
        return $this->salaryRepository->getCompanyEmployee($id);
    }

    public function printSalary($id)
    {
        return $this->salaryRepository->printSalary($id);
    }
}
