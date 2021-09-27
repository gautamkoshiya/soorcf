<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IVaultRepositoryInterface;
use Illuminate\Http\Request;

class VaultController extends Controller
{
    private $vaultRepository;

    public function __construct(IVaultRepositoryInterface $vaultRepository)
    {
        $this->vaultRepository = $vaultRepository;
    }

    public function index()
    {
        return $this->vaultRepository->index();
    }

    public function create()
    {
        return $this->vaultRepository->create();
    }

    public function store(Request $request)
    {
        return $this->vaultRepository->store($request);
    }

    public function vault_delete_post(Request $request)
    {
        return $this->vaultRepository->vault_delete_post($request);
    }

    public function VaultReportByCompany()
    {
        return $this->vaultRepository->VaultReportByCompany();
    }

    public function getClosingVault($id)
    {
        return $this->vaultRepository->getClosingVault($id);
    }

    public function PrintVaultReportByCompany(Request $request)
    {
        return $this->vaultRepository->PrintVaultReportByCompany($request);
    }
}
