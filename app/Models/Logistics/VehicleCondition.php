<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class VehicleCondition extends Model
{
    //
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
