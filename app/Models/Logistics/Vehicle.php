<?php

namespace App\Models\Logistics;

use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Vehicle extends Model
{
    use SoftDeletes;
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }
    public function vehicleDrivers()
    {
        return $this->hasMany(VehicleDriver::class);
    }
    public function expenses()
    {
        return $this->hasMany(VehicleExpense::class);
    }
    public function conditions()
    {
        return $this->hasMany(VehicleCondition::class);
    }
}
