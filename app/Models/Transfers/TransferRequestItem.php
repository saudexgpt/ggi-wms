<?php

namespace App\Models\Transfers;

use App\Models\Stock\Item;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequestItem extends Model
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
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function batches()
    {
        return $this->hasMany(TransferRequestItemBatch::class, 'transfer_request_item_id', 'id');
    }
    public function waybillItems()
    {
        return $this->hasMany(TransferRequestWaybillItem::class, 'transfer_request_item_id', 'id');
    }
    public function updateTransferRequestItemsForTransferRequestWaybill($waybill_items)
    {
        foreach ($waybill_items as $waybill_item) {
            $invoice_item = $waybill_item->invoiceItem;
            $status = $waybill_item->waybill->status;
            if ($status != 'delivered') {
                $total_quantity_supplied = $invoice_item->quantity_supplied; // + $waybill_item->quantity;
                $invoice_item->supply_status = 'Complete';
                if ($total_quantity_supplied < $invoice_item->quantity) {
                    $invoice_item->supply_status = 'Partial';
                }
                // $invoice_item->quantity_supplied = $total_quantity_supplied;
            }
            $invoice_item->delivery_status = $status;
            $invoice_item->save();
        }
    }
}
