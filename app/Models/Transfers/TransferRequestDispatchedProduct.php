<?php

namespace App\Models\Transfers;

use App\Models\Stock\ItemStockSubBatch;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequestDispatchedProduct extends Model
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
    public function transferWaybill()
    {
        return $this->belongsTo(TransferRequestWaybill::class, 'transfer_request_waybill_id', 'id');
    }
    public function transferWaybillItem()
    {
        return $this->belongsTo(TransferRequestWaybillItem::class, 'transfer_request_waybill_item_id', 'id');
    }
    public function itemStock()
    {
        return $this->belongsTo(ItemStockSubBatch::class, 'item_stock_sub_batch_id', 'id');
    }
}
