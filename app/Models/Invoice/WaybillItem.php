<?php

namespace App\Models\Invoice;

use App\Models\Stock\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaybillItem extends Model
{
    use SoftDeletes;
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function dispatchProduct()
    {
        return $this->hasOne(DispatchedProduct::class);
    }
    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function createWaybillItems($waybill_id, $warehouse_id, $invoice_item)
    {
        // foreach ($invoice_items as $invoice_item) {
        $waybill_item = new WaybillItem();
        $waybill_item->warehouse_id = $warehouse_id;
        $waybill_item->invoice_id = $invoice_item->invoice_id;
        $waybill_item->waybill_id = $waybill_id;
        $waybill_item->item_id = $invoice_item->item_id;
        $waybill_item->invoice_item_id = $invoice_item->id;
        $waybill_item->quantity = $invoice_item->quantity_for_supply;
        $waybill_item->type = $invoice_item->type;
        $waybill_item->save();
        // }
    }
}
