<?php

namespace App\Models\Stock;

use App\Models\Invoice\DispatchedProduct;
use App\Models\Invoice\InvoiceItem;
use App\Models\Invoice\InvoiceItemBatch;
use App\Laravue\Models\User;
use App\Models\Transfers\TransferRequestDispatchedProduct;
use App\Models\Transfers\TransferRequestItemBatch;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemStockSubBatch extends Model
{
    use SoftDeletes;
    //
    // protected static function booted()
    // {
    //     static::addGlobalScope('confirmed_by', function (Builder $builder) {
    //         $builder->where('confirmed_by', '!=', null);
    //     });
    // }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function expiredProducts()
    {
        return $this->hasMany(ExpiredProduct::class, 'item_stock_batch_id', 'id');
    }
    public function stocker()
    {
        return $this->belongsTo(User::class, 'stocked_by', 'id');
    }
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id');
    }


    ////////////////////////////////Methods////////////////////////////////
    public function fetchBalanceOfItemsInStock($warehouse_id, $item_id)
    {
        $initial_balance = 0;
        $item = ItemStockSubBatch::groupBy('item_id')->where(['warehouse_id' => $warehouse_id, 'item_id' => $item_id])
            ->select(\DB::raw('SUM(balance) as item_balance'))->first();

        if ($item) {
            return $item->item_balance;
        }
        return $initial_balance;
    }
    private function dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $quantity)
    {
        $dispatched_product = new DispatchedProduct();
        $dispatched_product->warehouse_id = $warehouse_id;
        $dispatched_product->item_stock_sub_batch_id = $item_stock_batch->id;
        $dispatched_product->waybill_id = $waybill_item->waybill_id;
        $dispatched_product->waybill_item_id = $waybill_item->id;
        $dispatched_product->quantity_supplied = $quantity;
        $dispatched_product->remitted = 1;
        $dispatched_product->instant_balance = $item_stock_batch->balance;
        $dispatched_product->status = 'on transit';
        $dispatched_product->save();
    }
    private function performFirstInFirstOutDelivery($warehouse_id, $waybill_item, $item_id, $quantity_to_supply)
    {
        $next_item_stock_batches = ItemStockSubBatch::where('item_id', $item_id)->where('balance', '>', 0)->orderBy('expiry_date')->get();
        foreach ($next_item_stock_batches as $next_item_stock_batch) {
            if ($quantity_to_supply <= $next_item_stock_batch->balance) {
                $next_item_stock_batch->in_transit += $quantity_to_supply;
                $next_item_stock_batch->balance -=  $quantity_to_supply;
                $next_item_stock_batch->save();

                $this->dispatchProduct($warehouse_id, $next_item_stock_batch, $waybill_item, $quantity_to_supply);

                $waybill_quantity = 0;
                break;
            } else {

                $this->dispatchProduct($warehouse_id, $next_item_stock_batch, $waybill_item, $next_item_stock_batch->balance);

                $quantity_to_supply -= $next_item_stock_batch->balance;
                $next_item_stock_batch->in_transit += $next_item_stock_batch->balance;
                $next_item_stock_batch->balance =  0;
                $next_item_stock_batch->save();
            }
        }

        return $quantity_to_supply;
    }
    private function dispatchTransferProduct($warehouse_id, $item_stock_batch, $waybill_item, $quantity)
    {
        $dispatched_product = new TransferRequestDispatchedProduct();
        $dispatched_product->supply_warehouse_id = $warehouse_id;
        $dispatched_product->item_stock_sub_batch_id = $item_stock_batch->id;
        $dispatched_product->transfer_request_waybill_id = $waybill_item->transfer_request_waybill_id;
        $dispatched_product->transfer_request_waybill_item_id = $waybill_item->id;
        $dispatched_product->quantity_supplied = $quantity;
        $dispatched_product->status = 'on transit';
        $dispatched_product->save();
    }
    private function performFirstInFirstOutTransferDelivery($warehouse_id, $waybill_item, $item_id, $quantity_to_supply)
    {
        $next_item_stock_batches = ItemStockSubBatch::where('item_id', $item_id)->where('balance', '>', 0)->orderBy('expiry_date')->get();
        foreach ($next_item_stock_batches as $next_item_stock_batch) {
            if ($quantity_to_supply <= $next_item_stock_batch->balance) {
                $next_item_stock_batch->in_transit += $quantity_to_supply;
                $next_item_stock_batch->balance -=  $quantity_to_supply;
                $next_item_stock_batch->save();

                $this->dispatchTransferProduct($warehouse_id, $next_item_stock_batch, $waybill_item, $quantity_to_supply);

                $waybill_quantity = 0;
                break;
            } else {

                $this->dispatchTransferProduct($warehouse_id, $next_item_stock_batch, $waybill_item, $next_item_stock_batch->balance);

                $quantity_to_supply -= $next_item_stock_batch->balance;
                $next_item_stock_batch->in_transit += $next_item_stock_batch->balance;
                $next_item_stock_batch->balance =  0;
                $next_item_stock_batch->save();
            }
        }

        return $quantity_to_supply;
    }
    public function sendItemInStockForDelivery($waybill_items)
    {
        foreach ($waybill_items as $waybill_item) {

            $warehouse_id = $waybill_item->warehouse_id;
            $waybill_quantity = $waybill_item->quantity;
            $invoice_item_id = $waybill_item->invoice_item_id;
            $invoice_item_batches = InvoiceItemBatch::with('itemStockBatch')->where('invoice_item_id', $invoice_item_id)->where('quantity', '>', '0')->get();
            // $items_in_stock= ItemStock::where(['warehouse_id' => $warehouse_id, 'item_id' => $waybill_item->item_id])
            //     ->where('balance', '>', '0')->orderBy('id')->get();
            // $item_stock_sub_batches = ItemStockSubBatch::where(['warehouse_id' => $warehouse_id, 'item_id' => $waybill_item->item_id])->where('balance', '>', '0')->orderBy('expiry_date')->get();
            if ($invoice_item_batches->count() > 0) {
                foreach ($invoice_item_batches as $invoice_item_batch) :

                    $for_supply = $invoice_item_batch->quantity;
                    $item_stock_batch = $invoice_item_batch->itemStockBatch;
                    $item_id = $item_stock_batch->item_id;

                    if ($waybill_quantity <= $for_supply) {
                        $invoice_item_batch->quantity -= $waybill_quantity;
                        $invoice_item_batch->save();

                        if ($item_stock_batch->balance > 0) {
                            if ($item_stock_batch->balance >= $waybill_quantity) {
                                $item_stock_batch->reserved_for_supply -= $waybill_quantity;
                                $item_stock_batch->in_transit += $waybill_quantity;
                                $item_stock_batch->balance -=  $waybill_quantity;
                                $item_stock_batch->save();

                                $this->dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $waybill_quantity);

                                $waybill_quantity = 0;
                            } else {
                                $waybill_quantity -= $item_stock_batch->balance;
                                $this->dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $item_stock_batch->balance);
                                $item_stock_batch->in_transit += $item_stock_batch->balance;
                                $item_stock_batch->balance =  0;
                                $item_stock_batch->save();
                                if ($waybill_quantity > 0) {
                                    $waybill_quantity = $this->performFirstInFirstOutDelivery($warehouse_id, $waybill_item, $item_id, $waybill_quantity);
                                }
                            }
                        } else {
                            $waybill_quantity = $this->performFirstInFirstOutDelivery($warehouse_id, $waybill_item, $item_id, $waybill_quantity);
                        }

                        // $waybill_quantity = 0; //we have sent all items for delivery
                        if ($waybill_quantity == 0) {
                            break;
                        }
                    } else {
                        $invoice_item_batch->quantity = 0;
                        $invoice_item_batch->save();

                        if ($item_stock_batch->balance > 0) {
                            if ($item_stock_batch->balance >= $for_supply) {
                                $item_stock_batch->reserved_for_supply -= $for_supply;
                                $item_stock_batch->in_transit += $for_supply;
                                $item_stock_batch->balance -=  $for_supply;
                                $item_stock_batch->save();

                                $this->dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $for_supply);
                            } else {
                                $for_supply -= $item_stock_batch->balance;
                                $this->dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $item_stock_batch->balance);
                                $item_stock_batch->in_transit += $item_stock_batch->balance;
                                $item_stock_batch->balance =  0;
                                $item_stock_batch->save();

                                $for_supply = $this->performFirstInFirstOutDelivery($warehouse_id, $waybill_item, $item_id, $for_supply);
                            }
                        } else {
                            $for_supply = $this->performFirstInFirstOutDelivery($warehouse_id, $waybill_item, $item_id, $for_supply);
                        }


                        $waybill_quantity -= $for_supply;
                    }
                endforeach;
            }
        }
    }
    public function sendTransferItemInStockForDelivery($waybill_items)
    {
        foreach ($waybill_items as $waybill_item) {

            $warehouse_id = $waybill_item->supply_warehouse_id;
            $waybill_quantity = $waybill_item->quantity;
            $invoice_item_id = $waybill_item->transfer_request_item_id;
            $invoice_item_batches = TransferRequestItemBatch::with('itemStockBatch')->where('transfer_request_item_id', $invoice_item_id)->where('quantity', '>', '0')->get();
            // $items_in_stock= ItemStock::where(['warehouse_id' => $warehouse_id, 'item_id' => $waybill_item->item_id])
            //     ->where('balance', '>', '0')->orderBy('id')->get();
            // $item_stock_sub_batches = ItemStockSubBatch::where(['warehouse_id' => $warehouse_id, 'item_id' => $waybill_item->item_id])->where('balance', '>', '0')->orderBy('expiry_date')->get();
            if ($invoice_item_batches->count() > 0) {
                foreach ($invoice_item_batches as $invoice_item_batch) :

                    $for_supply = $invoice_item_batch->quantity;
                    $item_stock_batch = $invoice_item_batch->itemStockBatch;
                    $item_id = $item_stock_batch->item_id;

                    if ($waybill_quantity <= $for_supply) {
                        $invoice_item_batch->quantity -= $waybill_quantity;
                        $invoice_item_batch->save();

                        if ($item_stock_batch->balance > 0) {
                            if ($item_stock_batch->balance >= $waybill_quantity) {
                                $item_stock_batch->reserved_for_supply -= $waybill_quantity;
                                $item_stock_batch->in_transit += $waybill_quantity;
                                $item_stock_batch->balance -=  $waybill_quantity;
                                $item_stock_batch->save();

                                $this->dispatchTransferProduct($warehouse_id, $item_stock_batch, $waybill_item, $waybill_quantity);

                                $waybill_quantity = 0;
                            } else {
                                $waybill_quantity -= $item_stock_batch->balance;
                                $this->dispatchTransferProduct($warehouse_id, $item_stock_batch, $waybill_item, $item_stock_batch->balance);
                                $item_stock_batch->in_transit += $item_stock_batch->balance;
                                $item_stock_batch->balance =  0;
                                $item_stock_batch->save();
                                if ($waybill_quantity > 0) {
                                    $waybill_quantity = $this->performFirstInFirstOutTransferDelivery($warehouse_id, $waybill_item, $item_id, $waybill_quantity);
                                }
                            }
                        } else {
                            $waybill_quantity = $this->performFirstInFirstOutTransferDelivery($warehouse_id, $waybill_item, $item_id, $waybill_quantity);
                        }

                        // $waybill_quantity = 0; //we have sent all items for delivery
                        if ($waybill_quantity == 0) {
                            break;
                        }
                    } else {
                        $invoice_item_batch->quantity = 0;
                        $invoice_item_batch->save();

                        if ($item_stock_batch->balance > 0) {
                            if ($item_stock_batch->balance >= $for_supply) {
                                $item_stock_batch->reserved_for_supply -= $for_supply;
                                $item_stock_batch->in_transit += $for_supply;
                                $item_stock_batch->balance -=  $for_supply;
                                $item_stock_batch->save();

                                $this->dispatchTransferProduct($warehouse_id, $item_stock_batch, $waybill_item, $for_supply);
                            } else {
                                $for_supply -= $item_stock_batch->balance;
                                $this->dispatchTransferProduct($warehouse_id, $item_stock_batch, $waybill_item, $item_stock_batch->balance);
                                $item_stock_batch->in_transit += $item_stock_batch->balance;
                                $item_stock_batch->balance =  0;
                                $item_stock_batch->save();

                                $for_supply = $this->performFirstInFirstOutTransferDelivery($warehouse_id, $waybill_item, $item_id, $for_supply);
                            }
                        } else {
                            $for_supply = $this->performFirstInFirstOutTransferDelivery($warehouse_id, $waybill_item, $item_id, $for_supply);
                        }


                        $waybill_quantity -= $for_supply;
                    }
                endforeach;
            }
        }
    }
    public function confirmItemInStockAsSupplied($dispatch_products)
    {
        foreach ($dispatch_products as $dispatch_product) {

            $dispatch_product->status = 'delivered';
            $dispatch_product->save();
            $item_stock_sub_batch = ItemStockSubBatch::find($dispatch_product->item_stock_sub_batch_id);

            $item_stock_sub_batch->in_transit -= $dispatch_product->quantity_supplied;
            $item_stock_sub_batch->supplied += $dispatch_product->quantity_supplied;
            $item_stock_sub_batch->save();
        }
    }
}
