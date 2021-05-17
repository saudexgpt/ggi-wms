<?php

namespace App\Models\Transfers;

use App\Models\Stock\ItemStockSubBatch;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequestItemBatch extends Model
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
    public function transferRequest()
    {
        return $this->belongsTo(TransferRequest::class, 'transfer_request_id', 'id');
    }
    public function transferRequestItem()
    {
        return $this->belongsTo(TransferRequestItem::class);
    }
    public function itemStockBatch()
    {
        return $this->belongsTo(ItemStockSubBatch::class, 'item_stock_sub_batch_id', 'id');
    }
}
