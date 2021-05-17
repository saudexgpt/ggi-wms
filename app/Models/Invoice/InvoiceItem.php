<?php

namespace App\Models\Invoice;

use App\Models\Stock\Item;
use App\Models\Stock\ItemStock;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    //
    use SoftDeletes;
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function itemStock()
    {
        return $this->belongsTo(ItemStock::class);
    }

    public function batches()
    {
        return $this->hasMany(InvoiceItemBatch::class);
    }
    public function waybillItems()
    {
        return $this->hasMany(WaybillItem::class);
    }

    public function updateInvoiceItemsForWaybill($waybill_items)
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
