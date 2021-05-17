<?php

namespace App\Models\Stock;

use App\Models\Invoice\DispatchedProduct;
use App\Models\Invoice\InvoiceItem;
use App\Models\Invoice\InvoiceItemBatch;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ItemStock extends Model
{

    use SoftDeletes;
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function subBatches()
    {
        return $this->hasMany(ItemStockSubBatch::class);
    }


    // public function sendItemInStockForDelivery($waybill_items)
    // {
    //     foreach ($waybill_items as $waybill_item) {

    //         $warehouse_id = $waybill_item->warehouse_id;
    //         $quantity = $waybill_item->quantity;
    //         // $items_in_stock= ItemStock::where(['warehouse_id' => $warehouse_id, 'item_id' => $waybill_item->item_id])
    //         //     ->where('balance', '>', '0')->orderBy('id')->get();
    //         $item_stock_sub_batches= ItemStockSubBatch::with('itemStock')->where(['warehouse_id' => $warehouse_id, 'item_id' => $waybill_item->item_id])
    //              ->where('balance', '>', '0')->orderBy('id')->get();
    //         if ($item_stock_sub_batches->count() > 0) {
    //             foreach ($item_stock_sub_batches as $item_stock_sub_batch) :

    //                 $balance = $item_stock_sub_batch->balance;

    //                 if ($quantity <= $balance) {
    //                     $item_stock_sub_batch->reserved_for_supply -= $quantity;
    //                     $item_stock_sub_batch->in_transit +=  $quantity;
    //                     $item_stock_sub_batch->balance -=  $quantity;

    //                     if ($item_stock_sub_batch->save()) {
    //                         //// also update item_stocks table/////////
    //                         $item_stock_sub_batch->itemStock->in_transit +=  $quantity;
    //                         $item_stock_sub_batch->itemStock->balance -=  $quantity;
    //                         $item_stock_sub_batch->itemStock->save();

    //                         $dispatched_product = new DispatchedProduct();
    //                         $dispatched_product->warehouse_id = $warehouse_id;
    //                         $dispatched_product->item_stock_sub_batch_id = $item_stock_sub_batch->id;
    //                         $dispatched_product->waybill_id = $waybill_item->waybill_id;
    //                         $dispatched_product->quantity_supplied = $quantity;
    //                         $dispatched_product->status = 'on transit';
    //                         $dispatched_product->save();
    //                     }
    //                     $quantity = 0; //we have sent all items for delivery
    //                     break;
    //                 } else {

    //                     $item_stock_sub_batch->in_transit +=  $balance;
    //                     $item_stock_sub_batch->balance -=  $balance;

    //                     if ($item_stock_sub_batch->save()) {
    //                         $dispatched_product = new DispatchedProduct();
    //                         $dispatched_product->warehouse_id = $warehouse_id;
    //                         $dispatched_product->item_stock_sub_batch_id = $item_stock_sub_batch->id;
    //                         $dispatched_product->waybill_id = $waybill_item->waybill_id;
    //                         $dispatched_product->quantity_supplied = $balance;
    //                         $dispatched_product->status = 'on transit';
    //                         $dispatched_product->save();
    //                     }

    //                     $quantity -= $balance;
    //                 }
    //             endforeach;
    //         }
    //     }
    // }


}
