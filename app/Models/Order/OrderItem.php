<?php

namespace App\Models\Order;

use App\Models\Stock\Item;
use App\Models\Stock\ItemStockSubBatch;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    //
    use SoftDeletes;
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function itemStock()
    {
        return $this->belongsTo(ItemStockSubBatch::class);
    }
}
