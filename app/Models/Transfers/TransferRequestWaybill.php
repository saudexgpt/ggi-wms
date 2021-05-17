<?php

namespace App\Models\Transfers;

use App\Laravue\Models\User;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequestWaybill extends Model
{
    //
    use SoftDeletes;
    public function supply_warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'supply_warehouse_id', 'id');
    }
    public function request_warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'request_warehouse_id', 'id');
    }

    public function transferRequests()
    {
        return $this->belongsToMany(TransferRequest::class);
    }

    // public function trips()
    // {
    //     return $this->belongsToMany(DeliveryTrip::class);
    // }
    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatched_by', 'id');
    }
    public function waybillItems()
    {
        return $this->hasMany(TransferRequestWaybillItem::class, 'transfer_request_waybill_id', 'id');
    }
    public function dispatchProducts()
    {
        return $this->hasMany(TransferRequestDispatchedProduct::class, 'transfer_request_waybill_id', 'id');
    }
}
