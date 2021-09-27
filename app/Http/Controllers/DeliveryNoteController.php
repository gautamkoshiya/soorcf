<?php

namespace App\Http\Controllers;

use App\WebRepositories\Interfaces\IDeliveryNoteRepositoryInterface;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    private $deliveryNoteRepository;

    public function __construct(IDeliveryNoteRepositoryInterface $deliveryNoteRepository)
    {
        $this->deliveryNoteRepository = $deliveryNoteRepository;
    }

    public function index()
    {
        return $this->deliveryNoteRepository->index();
    }

    public function all_delivery_note(Request $request)
    {
        return $this->deliveryNoteRepository->all_delivery_note($request);
    }

    public function create()
    {
        return $this->deliveryNoteRepository->create();
    }

    public function store(Request $request)
    {
        $this->deliveryNoteRepository->store($request);
    }

    public function PrintDeliveryNote($id)
    {
        $response=$this->deliveryNoteRepository->PrintDeliveryNote($id);
        return response()->json($response);
    }

    public function edit($id)
    {
        return $this->deliveryNoteRepository->edit($id);
    }

    public function DeliveryNoteUpdate(Request $request, $id)
    {
        return $this->deliveryNoteRepository->update($request, $id);
    }

    public function deleteDeliveryNote($id)
    {
        return $this->deliveryNoteRepository->delete($id);
    }
}
