<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Sale;
use App\WebRepositories\Interfaces\IMeterReadingRepositoryInterface;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    private $meterReadingRepository;

    public function __construct(IMeterReadingRepositoryInterface $meterReadingRepository)
    {
        $this->meterReadingRepository = $meterReadingRepository;
    }

    public function index()
    {
        return $this->meterReadingRepository->index();
    }

    public function create()
    {
        return $this->meterReadingRepository->create();
    }

    public function store(Request $request)
    {
        return $this->meterReadingRepository->store($request);
    }

    public function show($Id)
    {
        //
    }

    public function edit($Id)
    {
        return $this->meterReadingRepository->edit($Id);
    }


    public function meterReadingUpdate(Request $request, $Id)
    {
        return $this->meterReadingRepository->update($request, $Id);
    }


    public function destroy(MeterReading $meterReading)
    {
        //
    }

    public function cancel_meter_reading($id)
    {
        return $this->meterReadingRepository->delete($id);
    }

    public function meter_reading_delete_post(Request $request)
    {
        return $this->meterReadingRepository->meter_reading_delete_post($request);
    }

    public function getMeterReadingDetail($Id)
    {
        return $this->meterReadingRepository->getMeterReadingDetail($Id);
    }

    public function all_meter(Request $request)
    {
        return $this->meterReadingRepository->all_meter($request);
    }
}
