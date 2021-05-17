<?php

namespace App\Models\Order;

use App\Models\Setting\Currency;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    //
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
