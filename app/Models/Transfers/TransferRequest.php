<?php

namespace App\Models\Transfers;

use App\Laravue\Models\User;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequest extends Model
{
    use SoftDeletes;
    public function supplyWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'supply_warehouse_id', 'id');
    }
    public function requestWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'request_warehouse_id', 'id');
    }
    public function requestBy()
    {
        return $this->belongsTo(User::class, 'request_by', 'id');
    }
    public function transferWaybillItems()
    {
        return $this->hasMany(TransferRequestWaybillItem::class);
    }
    public function transferRequestItems()
    {
        return $this->hasMany(TransferRequestItem::class);
    }
    public function histories()
    {
        return $this->hasMany(TransferRequestHistory::class);
    }
    // public function transferWaybills()
    // {
    //     return $this->belongsToMany(Waybill::class);
    // }
}
