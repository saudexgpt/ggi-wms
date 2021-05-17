<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;

class ExpiredProduct extends Model
{
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function itemStock()
    {
        return $this->belongsTo(ItemStockSubBatch::class, 'item_stock_batch_id', 'id');
    }
}
