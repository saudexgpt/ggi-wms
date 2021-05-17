<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Logistics\VehicleType;
use Illuminate\Http\Request;

class VehicleTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicle_types = VehicleType::get();
        return response()->json(compact('vehicle_types'), 200);
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
        $type = $request->type;
        $vehicle_type = VehicleType::where('type', $type)->first();
        if (!$vehicle_type) {
            $vehicle_type = new VehicleType();
            $vehicle_type->type = $request->type;
            $vehicle_type->load_capacity = $request->load_capacity;
            $vehicle_type->is_enabled = $request->is_enabled;
            $vehicle_type->save();

            return $this->show($vehicle_type);
        }
        $actor = $this->getUser();
        $title = "New Vehicle Type Added";
        $description = "New Vehicle Type Added by $actor->name ($actor->email)";
        //log this activity
        $this->logUserActivity($title, $description);
        return response()->json(['message', 'Duplicate Vehicle Type'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Logistics\VehicleType  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function show(VehicleType $vehicleType)
    {
        $vehicle_type = $vehicleType;
        return response()->json(compact('vehicle_type'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Logistics\VehicleType  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleType $vehicleType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Logistics\VehicleType  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleType $vehicleType)
    {
        $vehicleType->type = $request->type;
        $vehicleType->load_capacity = $request->load_capacity;
        $vehicleType->is_enabled = $request->is_enabled;
        $vehicleType->save();

        return $this->show($vehicleType);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Logistics\VehicleType  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function destroy(VehicleType $vehicleType)
    {
        $vehicleType->delete();
        return response()->json(null, 204);
    }
}
