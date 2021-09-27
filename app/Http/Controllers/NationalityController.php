<?php

namespace App\Http\Controllers;

use App\Models\Nationality;
use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\INationalityRepositoryInterface;
use Illuminate\Http\Request;

class NationalityController extends Controller
{
    private $nationalityRepository;

    public function __construct(INationalityRepositoryInterface $nationalityRepository)
    {
        $this->nationalityRepository = $nationalityRepository;
    }

    public function index()
    {
        return $this->nationalityRepository->index();
    }

    public function create()
    {
        return $this->nationalityRepository->create();
    }

    public function store(Request $request)
    {
        return $this->nationalityRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->nationalityRepository->edit($Id);
    }

    public function update(Request $request, $Id)
    {
        return $this->nationalityRepository->update($request, $Id);
    }

    public function destroy($Id)
    {
        return $this->nationalityRepository->delete($Id);
    }
}
