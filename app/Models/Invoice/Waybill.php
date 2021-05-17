<?php

namespace App\Models\Invoice;

use App\Models\Stock\Item;
use App\Models\Stock\ItemStock;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Waybill extends Model
{
    //
    use SoftDeletes;
    public function invoices()
    {
        return $this->belongsToMany(Invoice::class);
    }
    public function trips()
    {
        return $this->belongsToMany(DeliveryTrip::class);
    }
    public function dispatcher()
    {
        return $this->hasOne(DispatchedWaybill::class);
    }
    public function waybillItems()
    {
        return $this->hasMany(WaybillItem::class);
    }
    public function dispatchProducts()
    {
        return $this->hasMany(DispatchedProduct::class);
    }
}
