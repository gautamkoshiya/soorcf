<?php

namespace App\Http\Controllers;

use App\Models\gst;
use App\WebRepositories\Interfaces\IGstRepositoryInterface;
use Illuminate\Http\Request;

class GstController extends Controller
{
    private $gstRepository;

    public function __construct(IGstRepositoryInterface $gstRepository)
    {
        $this->gstRepository = $gstRepository;
    }

    public function index()
    {
        return $this->gstRepository->index();
    }

    public function create()
    {
        return $this->gstRepository->create();
    }

    public function store(Request $request)
    {
        return $this->gstRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->gstRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->gstRepository->update($request, $Id);
    }

    public function destroy(Request $request, $Id)
    {
        return $this->gstRepository->delete($request, $Id);
    }
}
