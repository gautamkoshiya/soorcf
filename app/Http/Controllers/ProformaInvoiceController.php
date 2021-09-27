<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IProformaInvoiceRepositoryInterface;
use Illuminate\Http\Request;

class ProformaInvoiceController extends Controller
{
    private $proformaInvoiceRepository;

    public function __construct(IProformaInvoiceRepositoryInterface $proformaInvoiceRepository)
    {
        $this->proformaInvoiceRepository = $proformaInvoiceRepository;
    }

    public function index()
    {
        return $this->proformaInvoiceRepository->index();
    }

    public function all_proforma(Request $request)
    {
        return $this->proformaInvoiceRepository->all_proforma($request);
    }

    public function create()
    {
        return $this->proformaInvoiceRepository->create();
    }

    public function store(Request $request)
    {
        $this->proformaInvoiceRepository->store($request);
    }

    public function PrintProformaInvoice($id)
    {
        $response=$this->proformaInvoiceRepository->PrintProformaInvoice($id);
        return response()->json($response);
    }

    public function edit($id)
    {
        return $this->proformaInvoiceRepository->edit($id);
    }

    public function ProformaInvoiceUpdate(Request $request, $id)
    {
        return $this->proformaInvoiceRepository->update($request, $id);
    }

    public function deleteProformaInvoice($id)
    {
        return $this->proformaInvoiceRepository->delete($id);
    }
}
