<?php

namespace App\Models\Logistics;

use App\Driver;
use Illuminate\Database\Eloquent\Model;
class VehicleDriver extends Model
{
    //
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
