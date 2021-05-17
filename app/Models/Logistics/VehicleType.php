<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
