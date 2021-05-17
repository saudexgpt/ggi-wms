<?php

namespace App\Models\Invoice;

use App\Models\Stock\ItemStockSubBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItemBatch extends Model
{
    //
    use SoftDeletes;
    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }
    public function itemStockBatch()
    {
        return $this->belongsTo(ItemStockSubBatch::class, 'item_stock_sub_batch_id', 'id');
    }
}
