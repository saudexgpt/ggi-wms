<?php

namespace App\Models\Invoice;

use App\Models\Logistics\Vehicle;
use Illuminate\Database\Eloquent\Model;

class DeliveryTrip extends Model
{
    //
    public function cost()
    {
        return $this->hasOne(DeliveryTripExpense::class);
    }

    public function waybills()
    {
        return $this->belongsToMany(Waybill::class);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
