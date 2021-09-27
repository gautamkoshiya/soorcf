<?php

namespace App\Http\Controllers;

use App\Models\FileManager;
use App\Http\Controllers\Controller;
use App\WebRepositories\Interfaces\IFileManagerRepositoryInterface;
use Illuminate\Http\Request;

class FileManagerController extends Controller
{
    private $fileManagerRepository;

    public function __construct(IFileManagerRepositoryInterface $fileManagerRepository)
    {
        $this->fileManagerRepository = $fileManagerRepository;
    }

    public function index()
    {
        return $this->fileManagerRepository->index();
    }

    public function create()
    {
        return $this->fileManagerRepository->create();
    }

    public function all_files(Request $request)
    {
        return $this->fileManagerRepository->all_files($request);
    }

    public function store(Request $request)
    {
        return $this->fileManagerRepository->store($request);
    }

    public function edit($Id)
    {
        return $this->fileManagerRepository->edit($Id);
    }

    public function expenseUpdate(Request $request, $Id)
    {
        return $this->fileManagerRepository->update($request, $Id);
    }

    public function expense_delete_post(Request $request)
    {
        return $this->fileManagerRepository->expense_delete_post($request);
    }

    public function file_manager_delete_post(Request $request)
    {
        return $this->fileManagerRepository->file_manager_delete_post($request);
    }

    public function trash_files()
    {
        return $this->fileManagerRepository->trash_files();
    }
}
