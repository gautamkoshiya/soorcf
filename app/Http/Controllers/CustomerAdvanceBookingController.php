<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomerAdvanceBooking;
use App\WebRepositories\Interfaces\ICustomerAdvanceBookingRepositoryInterface;
use Illuminate\Http\Request;

class CustomerAdvanceBookingController extends Controller
{
    private $customerAdvanceBookingRepository;

    public function __construct(ICustomerAdvanceBookingRepositoryInterface $customerAdvanceBookingRepository)
    {
        $this->customerAdvanceBookingRepository = $customerAdvanceBookingRepository;
    }

    public function index()
    {
        return $this->customerAdvanceBookingRepository->index();
    }

    public function all_bookings(Request $request)
    {
        return $this->customerAdvanceBookingRepository->all_bookings($request);
    }

    public function create()
    {
        return $this->customerAdvanceBookingRepository->create();
    }

    public function store(Request $request)
    {
        $this->customerAdvanceBookingRepository->store($request);
    }

    public function edit($id)
    {
        return $this->customerAdvanceBookingRepository->edit($id);
    }

    public function CustomerBookingUpdate(Request $request, $id)
    {
        return $this->customerAdvanceBookingRepository->update($request, $id);
    }

    public function customer_booking_delete_post(Request $request)
    {
        return $this->customerAdvanceBookingRepository->customer_booking_delete_post($request);
    }

    public function getBookingDetail($id)
    {
        return $this->customerAdvanceBookingRepository->getBookingDetail($id);
    }

    public function CustomerBookingOverfilledDetails($id)
    {
        return $this->customerAdvanceBookingRepository->CustomerBookingOverfilledDetails($id);
    }

    public function getAdvanceBookingReport()
    {
        return $this->customerAdvanceBookingRepository->getAdvanceBookingReport();
    }

    public function PrintAdvanceBookingReport(Request $request)
    {
        return $this->customerAdvanceBookingRepository->PrintAdvanceBookingReport($request);
    }
}
