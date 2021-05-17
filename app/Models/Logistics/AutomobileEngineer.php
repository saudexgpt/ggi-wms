<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class AutomobileEngineer extends Model
{
    //
    public function servicedVehicles()
    {
        return $this->hasMany(VehicleExpense::class);
    }
}
