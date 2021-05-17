<?php

namespace App\Models\Invoice;

use App\Models\Logistics\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class DispatchedWaybill extends Model
{
    //
    use SoftDeletes;
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }
}
