<?php

namespace App\Models\Invoice;

use App\Models\Stock\ItemStock;
use App\Models\Stock\ItemStockSubBatch;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DispatchedProduct extends Model
{
    //
    use SoftDeletes;
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }
    public function waybillItem()
    {
        return $this->belongsTo(WaybillItem::class);
    }
    public function itemStock()
    {
        return $this->belongsTo(ItemStockSubBatch::class, 'item_stock_sub_batch_id', 'id');
    }
}
