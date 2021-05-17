<?php

namespace App\Models\Order;

use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderHistory extends Model
{
    //
    use SoftDeletes;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
