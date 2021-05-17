<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\VehicleCondition;
use Illuminate\Http\Request;

class VehicleConditionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $vehicle_conditions = VehicleCondition::with('vehicle')->orderBy('id', 'DESC')->where('warehouse_id', $warehouse_id)->get();
        return response()->json(compact('vehicle_conditions'), 200);
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
        $actor = $this->getUser();
        $vehicle_condition = new VehicleCondition();
        $vehicle_condition->warehouse_id = $request->warehouse_id;
        $vehicle_condition->vehicle_id = $request->vehicle_id;
        $vehicle_condition->condition = $request->condition;
        $vehicle_condition->description = $request->description;
        $vehicle_condition->status = $request->status;
        $vehicle_condition->save();

        $title = "Updated vehicle condition";
        $description = "$actor->name ($actor->email) updated vehicle with vehicle no. " . $vehicle_condition->vehicle->plate_no . " as $vehicle_condition->condition";
        //log this activity
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($vehicle_condition);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Logistics\VehicleCondition  $vehicleExpense
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleCondition $vehicleExpense)
    {
        $vehicle_condition = $vehicleExpense->with(['vehicle'])->find($vehicleExpense->id);
        return response()->json(compact('vehicle_condition'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Logistics\VehicleCondition  $vehicleCondition
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleCondition $vehicleCondition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Logistics\VehicleCondition  $vehicleCondition
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleCondition $vehicleCondition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Logistics\VehicleCondition  $vehicleCondition
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleCondition $vehicleCondition)
    {
        //
    }
}
