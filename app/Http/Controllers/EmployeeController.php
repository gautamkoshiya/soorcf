<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRquest;
use App\Models\Employee;
use App\WebRepositories\Interfaces\IEmployeeRepositoryInterface;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private $employeeRepository;

    public function __construct(IEmployeeRepositoryInterface $employeeRepository)
    {
       $this->employeeRepository = $employeeRepository;
    }

    public function index()
    {
        return $this->employeeRepository->index();
    }

    public function create()
    {
        return $this->employeeRepository->create();
    }

    public function store(EmployeeRquest $employeeRequest)
    {
        return $this->employeeRepository->store($employeeRequest);
    }

    public function show(Employee $employee)
    {
        //
    }

    public function edit($Id)
    {
        return $this->employeeRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->employeeRepository->update($request, $Id);
    }

    public function deleteEmployee($Id)
    {
        return $this->employeeRepository->delete($Id);
    }

    public function ChangeEmployeeStatus($Id)
    {
        return $this->employeeRepository->ChangeEmployeeStatus($Id);
    }

    public function getEmployeeDetail($Id)
    {
        return $this->employeeRepository->getEmployeeDetail($Id);
    }
}
