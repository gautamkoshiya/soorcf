<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\InvestorRepository;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    private $investorRepository;

    public function __construct(InvestorRepository $investorRepository)
    {
        $this->investorRepository = $investorRepository;
    }

    public function index()
    {
        return $this->investorRepository->index();
    }

    public function create()
    {
        return $this->investorRepository->create();
    }

    public function store(Request $request)
    {
        return $this->investorRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->investorRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->investorRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->investorRepository->delete($request, $Id);
    }

    public function getInvestorForCompany($Id)
    {
        return $this->investorRepository->getInvestorForCompany($Id);
    }
}
