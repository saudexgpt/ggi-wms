<?php

namespace App\Models\Transfers;

use App\Models\Stock\Item;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequestWaybillItem extends Model
{
    use SoftDeletes;
    public function supply_warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'supply_warehouse_id', 'id');
    }
    public function invoice()
    {
        return $this->belongsTo(TransferRequest::class, 'transfer_request_id', 'id');
    }
    public function waybill()
    {
        return $this->belongsTo(TransferRequestWaybill::class, 'transfer_request_waybill_id', 'id');
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function invoiceItem()
    {
        return $this->belongsTo(TransferRequestItem::class, 'transfer_request_item_id', 'id');
    }

    public function dispatchProduct()
    {
        return $this->hasOne(TransferRequestDispatchedProduct::class, 'transfer_request_waybill_item_id', 'transfer_request_waybill_item_id');
    }
    public function createTransferRequestWaybillItems($waybill_id, $warehouse_id, $transfer_request_item)
    {
        // foreach ($transfer_request_items as $transfer_request_item) {
        $waybill_item = new TransferRequestWaybillItem();
        $waybill_item->supply_warehouse_id = $warehouse_id;
        $waybill_item->transfer_request_id = $transfer_request_item->transfer_request_id;
        $waybill_item->transfer_request_waybill_id = $waybill_id;
        $waybill_item->item_id = $transfer_request_item->item_id;
        $waybill_item->transfer_request_item_id = $transfer_request_item->id;
        $waybill_item->quantity = $transfer_request_item->quantity_for_supply;
        $waybill_item->type = $transfer_request_item->type;
        $waybill_item->save();
        // }
    }
}
