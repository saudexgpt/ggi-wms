<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\Vehicle;
use App\Models\Logistics\VehicleDriver;
use Illuminate\Http\Request;

class VehiclesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $vehicles = Vehicle::with(['warehouse', 'vehicleType', 'vehicleDrivers.driver.user', 'expenses' => function ($q) {
            $q->orderBy('id', 'DESC');
        }, 'conditions' => function ($q) {
            $q->orderBy('id', 'DESC');
        }])->where('warehouse_id', $warehouse_id)->get();
        return response()->json(compact('vehicles'), 200);
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
        $user = $this->getUser();
        $plate_no = $request->plate_no;
        $vehicle = Vehicle::where('plate_no', $plate_no)->first();
        if (!$vehicle) {
            $vehicle = new Vehicle();
            $vehicle->warehouse_id = $request->warehouse_id;
            $vehicle->vehicle_type_id = $request->vehicle_type_id;
            $vehicle->plate_no = $request->plate_no;
            $vehicle->vin = $request->vin;
            $vehicle->brand = $request->brand;
            $vehicle->model = $request->model;
            $vehicle->initial_mileage = $request->initial_mileage;
            $vehicle->purchase_date = date('Y-m-d H:i:s', strtotime($request->purchase_date));
            $vehicle->notes = $request->notes;
            $vehicle->engine_type = $request->engine_type;
            $vehicle->save();

            // log this action
            $title = "New Vehicle Added";
            $description = "Vehicle with plate number: " . $vehicle->plate_no .
                " was added by " . $user->name . "($user->email)";;
            $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
            $this->logUserActivity($title, $description, $roles);
            return $this->show($vehicle);
        }
        return response()->json(['message', 'Duplicate Plate No.'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Logistics\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle = $vehicle->with(['warehouse', 'vehicleType', 'vehicleDrivers.driver.user', 'expenses' => function ($q) {
            $q->orderBy('id', 'DESC');
        }, 'conditions' => function ($q) {
            $q->orderBy('id', 'DESC');
        }])->find($vehicle->id);
        return response()->json(compact('vehicle'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Logistics\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function edit(Vehicle $vehicle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Logistics\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $user = $this->getUser();
        $vehicle->warehouse_id = $request->warehouse_id;
        $vehicle->vehicle_type_id = $request->vehicle_type_id;
        $vehicle->plate_no = $request->plate_no;
        $vehicle->vin = $request->vin;
        $vehicle->brand = $request->brand;
        $vehicle->model = $request->model;
        $vehicle->initial_mileage = $request->initial_mileage;
        $vehicle->purchase_date = date('Y-m-d H:i:s', strtotime($request->purchase_date));
        $vehicle->notes = $request->notes;
        $vehicle->engine_type = $request->engine_type;
        $vehicle->save();

        // log this action
        $title = "Vehicle details updated";
        $description = "Details of vehicle with plate number: " . $vehicle->plate_no . " was modified by " . $user->name . "($user->email)";
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($vehicle);
    }
    public function vehicleDrivers()
    {
        $vehicle_drivers = VehicleDriver::with(['vehicle', 'driver'])->get();
        return response()->json(compact('vehicle_drivers'), 200);
    }

    public function assignDriver(Request $request)
    {
        $driver_id = $request->driver_id;
        $vehicle_id = $request->vehicle_id;
        $type = $request->type;
        $vehicle_driver = VehicleDriver::where('driver_id', $driver_id)->first();
        if ($vehicle_driver) {
            $vehicle_driver->delete(); // delete vehicle driver
        }
        $vehicle_driver = new VehicleDriver();
        $vehicle_driver->driver_id = $driver_id;
        $vehicle_driver->vehicle_id = $vehicle_id;
        $vehicle_driver->type = $type;
        $vehicle_driver->save();

        $vehicles = Vehicle::with(['warehouse', 'vehicleType', 'vehicleDrivers.driver.user', 'expenses' => function ($q) {
            $q->orderBy('id', 'DESC');
        }, 'conditions' => function ($q) {
            $q->orderBy('id', 'DESC');
        }])->where('warehouse_id', $vehicle_driver->vehicle->warehouse_id)->get();

        // log this action
        $title = "Vehicle assigned";
        $description = "Vehicle with plate number: " . $vehicle_driver->vehicle->plate_no . " has been assigned to " . $vehicle_driver->driver->user->name . "(" . $vehicle_driver->driver->user->phone . ")";
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        // $vehicle_drivers = VehicleDriver::with(['vehicle', 'driver.user'])->where('vehicle_id', $vehicle_id)->get();
        return response()->json(compact('vehicles'), 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Logistics\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehicle $vehicle)
    {
        //
        $vehicle->delete();
        return response()->json(null, 204);
    }
}
