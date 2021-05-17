<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class VehicleExpenseDetail extends Model
{
    //
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function vehicleExpense()
    {
        return $this->belongsTo(VehicleExpense::class);
    }
}
