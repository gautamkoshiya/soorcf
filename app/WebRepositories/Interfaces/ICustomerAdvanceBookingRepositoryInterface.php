<?php


namespace App\WebRepositories\Interfaces;


use Illuminate\Http\Request;

interface ICustomerAdvanceBookingRepositoryInterface
{
    public function all_bookings(Request $request);

    public function create();

    public function store(Request $request);

    public function update(Request $request, $Id);

    public function edit($Id);

    public function customer_booking_delete_post($Id);
}
