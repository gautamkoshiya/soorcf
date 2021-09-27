<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\ILpoRepositoryInterface;
use Illuminate\Http\Request;

class LpoController extends Controller
{
    private $lpoRepository;

    public function __construct(ILpoRepositoryInterface $lpoRepository)
    {
        $this->lpoRepository = $lpoRepository;
    }

    public function index()
    {
        return $this->lpoRepository->index();
    }

    public function all_lpo(Request $request)
    {
        return $this->lpoRepository->all_lpo($request);
    }

    public function create()
    {
        return $this->lpoRepository->create();
    }

    public function store(Request $request)
    {
        $this->lpoRepository->store($request);
    }

    public function PrintLpo($id)
    {
        $response=$this->lpoRepository->PrintLpo($id);
        return response()->json($response);
    }

    public function edit($id)
    {
        return $this->lpoRepository->edit($id);
    }

    public function lpoUpdate(Request $request, $id)
    {
        return $this->lpoRepository->update($request, $id);
    }

    public function deleteLpo($id)
    {
        return $this->lpoRepository->delete($id);
    }

    public function lpoSupplierDetails($id)
    {
        return $this->lpoRepository->lpoSupplierDetails($id);
    }
}
