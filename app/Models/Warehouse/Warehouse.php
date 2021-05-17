<?php

namespace App\Models\Warehouse;

use App\Customer;
use App\Laravue\Models\User;
use App\Models\Logistics\Vehicle;
use App\Models\Stock\Item;
use App\Models\Stock\ItemStockSubBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;
    // protected $hidden = ['pivot'];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function itemStocks()
    {
        return $this->hasMany(ItemStockSubBatch::class);
    }
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
