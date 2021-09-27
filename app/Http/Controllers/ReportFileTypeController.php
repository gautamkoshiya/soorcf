<?php

namespace App\Http\Controllers;

use App\Models\ReportFileType;
use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IReportFileTypeRepositoryInterface;
use Illuminate\Http\Request;

class ReportFileTypeController extends Controller
{
    private $reportFileTypeRepository;

    public function __construct(IReportFileTypeRepositoryInterface $reportFileTypeRepository)
    {
        $this->reportFileTypeRepository = $reportFileTypeRepository;
    }
    public function index()
    {
        return $this->reportFileTypeRepository->index();
    }

    public function create()
    {
        return $this->reportFileTypeRepository->create();
    }

    public function store(Request $request)
    {
        return $this->reportFileTypeRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->reportFileTypeRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->reportFileTypeRepository->update($request, $Id);
    }

    public function destroy($Id)
    {
        return $this->reportFileTypeRepository->delete($Id);
    }
}
