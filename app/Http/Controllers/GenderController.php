<?php

namespace App\Http\Controllers;

use App\Models\Gender;
use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IGenderRepositoryInterface;
use Illuminate\Http\Request;

class GenderController extends Controller
{
    private $genderRepository;

    public function __construct(IGenderRepositoryInterface $genderRepository)
    {
        $this->genderRepository = $genderRepository;
    }

    public function index()
    {
        return $this->genderRepository->index();
    }

    public function create()
    {
        return $this->genderRepository->create();
    }

    public function store(Request $request)
    {
        return $this->genderRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->genderRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->genderRepository->update($request, $Id);
    }

    public function destroy($Id)
    {
        return $this->genderRepository->delete($Id);
    }
}
