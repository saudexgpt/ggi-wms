<?php

namespace App\Http\Controllers\Logistics;

use App\Driver;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DriversController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $drivers = Driver::with(['user', 'vehicleDriver.vehicle'])->get();

        return response()->json(compact('drivers'), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function show(Driver $driver)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function edit(Driver $driver)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Driver $driver)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Driver $driver)
    {
        $user = $this->getUser();
        //log this activity first
        $title = "Driver information deleted";
        $description = $driver->user->name . "'s profile deleted by " . $user->name;
        $roles = ['assistant admin', 'warehouse manager'];
        $this->logUserActivity($title, $description, $roles);

        // delete the driver's info
        $driver->delete();
        return response()->json([], 204);
    }
}
