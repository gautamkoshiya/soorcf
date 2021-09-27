<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IOtherStockRepositoryInterface;
use Illuminate\Http\Request;

class OtherStockController extends Controller
{
    private $otherStockRepository;

    public function __construct(IOtherStockRepositoryInterface $otherStockRepository)
    {
        $this->otherStockRepository = $otherStockRepository;
    }

    public function index()
    {
        return $this->otherStockRepository->index();
    }

    public function create()
    {
        return $this->otherStockRepository->create();
    }

    public function store(Request $request)
    {
        $this->otherStockRepository->store($request);
        return redirect()->route('other_stocks.index');
    }

    public function GetOtherStockReport()
    {
        return $this->otherStockRepository->GetOtherStockReport();
    }

    public function PrintOtherStockStatement(Request $request)
    {
        $response=$this->otherStockRepository->PrintOtherStockStatement($request);
        return response()->json($response);
    }

    public function deleteOtherStock($id)
    {
        return $this->otherStockRepository->delete($id);
    }
}
