<?php

namespace App\Http\Controllers;

use App\Models\Invoice\DispatchedProduct;
use App\Models\Invoice\InvoiceItem;
use App\Models\Invoice\InvoiceItemBatch;
use App\Models\Invoice\WaybillItem;
use App\Models\Stock\Item;
use App\Models\Stock\ItemStockSubBatch;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    /*public function solveDeliveryTripIssue()
    {
        $delivery_trips = DeliveryTrip::get();
        foreach ($delivery_trips as $delivery_trip) {
            $waybills = $delivery_trip->waybills;
            foreach ($waybills as $waybill) {
                if($delivery_trip->vehicle_id !== NULL) {
                    $this->createDispatchedWaybill($waybill->id, $delivery_trip->vehicle_id);
                }
            }
        }
        return 'Hello';
    }*/
    public function balanceInvoiceItems()
    {
        $invoice_items = InvoiceItem::where('quantity_supplied', '>', 'quantity')->get();
        foreach ($invoice_items as $invoice_item) {
            $invoice_item->quantity_supplied = $invoice_item->quantity;
            $invoice_item->save();
        }
        return 'true';
    }
    public function solveDispatchProductIssue()
    {
        $dispatch_products = DispatchedProduct::where('waybill_item_id', NULL)->get();
        //return $dispatch_products->count();
        foreach ($dispatch_products as $dispatch_product) {
            $waybill = $dispatch_product->waybill;
            $waybill_items = $waybill->waybillItems;
            foreach ($waybill_items as $waybill_item) {
                $invoice_item_batch = InvoiceItemBatch::where(['invoice_item_id' => $waybill_item->invoice_item_id, 'item_stock_sub_batch_id' => $dispatch_product->item_stock_sub_batch_id])->first();
                if ($invoice_item_batch) {
                    // if ($dispatch_product->quantity_supplied === $invoice_item_batch->invoiceItem->quantity_supplied) {
                    $dispatch_product->waybill_item_id = $waybill_item->id;
                    $dispatch_product->save();
                    //}
                }
            }
        }
        return 'saved';
    }
    public function totalDispatchedProduct(Request $request)
    {
        $item_stock_sub_batch_id = $request->item_stock_sub_batch_id;
        $total = DispatchedProduct::where('item_stock_sub_batch_id', $item_stock_sub_batch_id)->select(\DB::raw('SUM(quantity_supplied) as quantity'))->get();
        //return $dispatch_products->count();
        return $total[0]->quantity;
    }

    public function reservedProducts(Request $request)
    {
        $item_stock_sub_batch_id = $request->item_stock_sub_batch_id;
        $total = InvoiceItemBatch::where('item_stock_sub_batch_id', $item_stock_sub_batch_id)->select(\DB::raw('SUM(quantity) as quantity'))->get();
        //return $dispatch_products->count();
        return $total;
    }
    public function resetStock()
    {
        $item_stock_batches = ItemStockSubBatch::get();
        foreach ($item_stock_batches as $item_stock_batch) {
            $item_stock_batch->reserved_for_supply = 0;
            $item_stock_batch->in_transit = 0;
            $item_stock_batch->supplied = 0;
            $item_stock_batch->balance = $item_stock_batch->quantity;

            $item_stock_batch->save();
        }
        return 'true';
    }
    public function balanceStockAccount()
    {
        $item_stock_batches = ItemStockSubBatch::orderBy('id')->get();
        foreach ($item_stock_batches as $item_stock_batch) {
            // $total = DispatchedProduct::where(['item_stock_sub_batch_id' => $item_stock_batch->id])->select(\DB::raw('SUM(quantity_supplied) as quantity'))->get();

            // $delivered = DispatchedProduct::where(['item_stock_sub_batch_id' => $item_stock_batch->id, 'status' => 'delivered'])->select(\DB::raw('SUM(quantity_supplied) as quantity'))->get();

            // $transit = DispatchedProduct::where(['item_stock_sub_batch_id' => $item_stock_batch->id, 'status' => 'on transit'])->select(\DB::raw('SUM(quantity_supplied) as quantity'))->get();

            $reserved = InvoiceItemBatch::where('item_stock_sub_batch_id', $item_stock_batch->id)->select(\DB::raw('SUM(quantity) as quantity'))->get();

            $reserved_for_supply  = 0;
            if ($reserved[0]->quantity != null) {
                $reserved_for_supply = $reserved[0]->quantity;
            }
            $item_stock_batch->reserved_for_supply = $reserved_for_supply;
            // $item_stock_batch->in_transit = $in_transit;
            // $item_stock_batch->supplied = $supplied;
            // $item_stock_batch->balance = $item_stock_batch->quantity - $balance;

            $item_stock_batch->save();
        }
        return 'Done';
    }
    public function balanceStockAccountPerProduct()
    {
        $products = Item::get();
        foreach ($products as $product) {
            $item_stock_batches = ItemStockSubBatch::where('item_id', $product->id)->orderBy('id')->get();
            $reserved_for_supply = $in_transit = $supplied = $balance = 0;
            if (!$item_stock_batches->isEmpty()) {
                foreach ($item_stock_batches as $item_stock_batch) {
                    $total = DispatchedProduct::where(['item_stock_sub_batch_id' => $item_stock_batch->id])->select(\DB::raw('SUM(quantity_supplied) as quantity'))->get();

                    $delivered = DispatchedProduct::where(['item_stock_sub_batch_id' => $item_stock_batch->id, 'status' => 'delivered'])->select(\DB::raw('SUM(quantity_supplied) as quantity'))->get();

                    $transit = DispatchedProduct::where(['item_stock_sub_batch_id' => $item_stock_batch->id, 'status' => 'on transit'])->select(\DB::raw('SUM(quantity_supplied) as quantity'))->get();

                    $reserved = InvoiceItemBatch::where('item_stock_sub_batch_id', $item_stock_batch->id)->select(\DB::raw('SUM(quantity) as quantity'))->get();

                    if ($reserved[0]->quantity != null) {
                        $reserved_for_supply += $reserved[0]->quantity;
                    }
                    if ($transit[0]->quantity != null) {
                        $in_transit += $transit[0]->quantity;
                    }
                    if ($delivered[0]->quantity != null) {
                        $supplied += $delivered[0]->quantity;
                    }
                    if ($total[0]->quantity != null) {
                        $balance += $total[0]->quantity;
                    }
                    // $item_stock_batch->reserved_for_supply = $reserved_for_supply;
                    // $item_stock_batch->in_transit = $in_transit;
                    // $item_stock_batch->supplied = $supplied;
                    // $item_stock_batch->balance = $item_stock_batch->quantity - $balance;

                    // $item_stock_batch->save();
                }
                foreach ($item_stock_batches as $item_stock_batch) {
                    $item_stock_batch->reserved_for_supply = $reserved_for_supply;
                    $item_stock_batch->in_transit = $in_transit;
                    $item_stock_batch->supplied = $supplied;
                    $item_stock_batch->balance = $item_stock_batch->quantity - $balance;

                    $item_stock_batch->save();
                }
            }
        }

        return 'Done';
    }
    public function stabilizeAccount()
    {
        // $item_stock_batches = ItemStockSubBatch::get();
        // foreach ($item_stock_batches as $item_stock_batch) {
        //     $dispatched_products = DispatchedProduct::where(['item_stock_sub_batch_id' => $item_stock_batch->id, 'remitted' => 0])->get();
        //     $total = 0;
        //     foreach ($dispatched_products as $dispatched_product) {
        //         $status = $dispatched_product->status;
        //         $quantity_supplied = $dispatched_product->quantity_supplied;

        //         if ($quantity_supplied <= $item_stock_batch->balance) {
        //             $item_stock_batch->balance -= $quantity_supplied;

        //             if ($status == 'on transit') {
        //                 $item_stock_batch->in_transit += $quantity_supplied;
        //             } else {
        //                 $item_stock_batch->supplied += $quantity_supplied;
        //             }
        //             $item_stock_batch->save();
        //             $dispatched_product->remitted = 1;
        //             $dispatched_product->save();
        //         }
        //     }
        //     // $out_bound = $item_stock_batch->in_transit =
        //     // $another_batch = ItemStockSubBatch::where(['item_id' => $product->id])->whereRaw('balance > '. $item_stock_batch-> )->get();
        // }
        $dispatched_products = DispatchedProduct::where(['remitted' => 0])->get();

        foreach ($dispatched_products as $dispatched_product) {
            $item_stock_batch = ItemStockSubBatch::find($dispatched_product->item_stock_sub_batch_id);
            $status = $dispatched_product->status;
            $quantity_supplied = $dispatched_product->quantity_supplied;

            if ($quantity_supplied <= $item_stock_batch->balance) {
                $item_stock_batch->balance -= $quantity_supplied;

                if ($status == 'on transit') {
                    $item_stock_batch->in_transit += $quantity_supplied;
                } else {
                    $item_stock_batch->supplied += $quantity_supplied;
                }
                $item_stock_batch->save();
                $dispatched_product->remitted = 1;
                $dispatched_product->instant_balance = $item_stock_batch->balance;
                $dispatched_product->save();
                // $quantity_supplied = 0;
            } else {
                if ($item_stock_batch->balance > 0) {
                    $quantity_supplied -= $item_stock_batch->balance;

                    if ($status == 'on transit') {
                        $item_stock_batch->in_transit += $item_stock_batch->balance;
                    } else {
                        $item_stock_batch->supplied += $item_stock_batch->balance;
                    }
                    $dispatched_product->remitted = 1;
                    $dispatched_product->instant_balance = $item_stock_batch->balance;
                    $dispatched_product->quantity_supplied = $item_stock_batch->balance;
                    $dispatched_product->save();
                    $item_stock_batch->balance = 0;
                    $item_stock_batch->save();




                    # code...

                    $next_item_stock_batches = ItemStockSubBatch::where('item_id', $item_stock_batch->item_id)->orderBy('id')->get();
                    foreach ($next_item_stock_batches as $next_item_stock_batch) {
                        if ($quantity_supplied <= $next_item_stock_batch->balance) {
                            $next_item_stock_batch->balance -= $quantity_supplied;

                            if ($status == 'on transit') {
                                $next_item_stock_batch->in_transit += $quantity_supplied;
                            } else {
                                $next_item_stock_batch->supplied += $quantity_supplied;
                            }
                            $next_item_stock_batch->save();

                            $new_dispatched_product = new DispatchedProduct();
                            $new_dispatched_product->warehouse_id = $dispatched_product->warehouse_id;
                            $new_dispatched_product->item_stock_sub_batch_id = $next_item_stock_batch->id;
                            $new_dispatched_product->waybill_id = $dispatched_product->waybill_id;
                            $new_dispatched_product->waybill_item_id = $dispatched_product->waybill_item_id;
                            $new_dispatched_product->quantity_supplied = $quantity_supplied;
                            $new_dispatched_product->status = $status;
                            $new_dispatched_product->created_at = $dispatched_product->created_at;
                            $new_dispatched_product->remitted = 1;
                            $new_dispatched_product->instant_balance = $next_item_stock_batch->balance;
                            $new_dispatched_product->save();
                        } else {
                            $quantity_supplied -= $next_item_stock_batch->balance;

                            if ($status == 'on transit') {
                                $next_item_stock_batch->in_transit += $next_item_stock_batch->balance;
                            } else {
                                $next_item_stock_batch->supplied += $next_item_stock_batch->balance;
                            }
                            $next_item_stock_batch->balance = 0;
                            $next_item_stock_batch->save();

                            $new_dispatched_product = new DispatchedProduct();
                            $new_dispatched_product->warehouse_id = $dispatched_product->warehouse_id;
                            $new_dispatched_product->item_stock_sub_batch_id = $next_item_stock_batch->id;
                            $new_dispatched_product->waybill_id = $dispatched_product->waybill_id;
                            $new_dispatched_product->waybill_item_id = $dispatched_product->waybill_item_id;
                            $new_dispatched_product->quantity_supplied =
                                $next_item_stock_batch->balance;
                            $new_dispatched_product->status = $status;
                            $new_dispatched_product->created_at = $dispatched_product->created_at;
                            $new_dispatched_product->remitted = 1;
                            $new_dispatched_product->instant_balance = $next_item_stock_batch->balance;
                            $new_dispatched_product->save();
                        }
                    }
                }
            }
        }
        return 'done';
    }
    public function splitExcessStock()
    {
        $dispatched_products = DispatchedProduct::where(['remitted' => 0])->get();
        foreach ($dispatched_products as $dispatched_product) {
            $status = $dispatched_product->status;
            $quantity_supplied = $dispatched_product->quantity_supplied;
            $next_item_stock_batches = ItemStockSubBatch::where('item_id', $dispatched_product->itemStock->item_id)->where('balance', '>', 0)->orderBy('id')->get();
            foreach ($next_item_stock_batches as $next_item_stock_batch) {

                if ($next_item_stock_batch->balance <= $quantity_supplied) {
                    $quantity_supplied -= $next_item_stock_batch->balance;
                    if ($status == 'on transit') {
                        $next_item_stock_batch->in_transit += $next_item_stock_batch->balance;
                    } else {
                        $next_item_stock_batch->supplied += $next_item_stock_batch->balance;
                    }
                    $next_item_stock_batch->balance = 0;
                    $next_item_stock_batch->save();

                    $new_dispatched_product = new DispatchedProduct();
                    $new_dispatched_product = $dispatched_product;
                    $new_dispatched_product->quantity_supplied = $quantity_supplied;
                    $new_dispatched_product->instant_balance = $next_item_stock_batch->balance;
                    $new_dispatched_product->remitted = 1;
                    $new_dispatched_product->save();
                    // $quantity_supplied = 0;
                } else {
                    // $quantity_supplied -= $next_item_stock_batch->balance;
                    if ($status == 'on transit') {
                        $next_item_stock_batch->in_transit += $quantity_supplied;
                    } else {
                        $next_item_stock_batch->supplied += $quantity_supplied;
                    }
                    $next_item_stock_batch->balance -= $quantity_supplied;
                    $next_item_stock_batch->save();
                    $new_dispatched_product = new DispatchedProduct();
                    $new_dispatched_product = $dispatched_product;
                    $new_dispatched_product->quantity_supplied = $quantity_supplied;
                    $new_dispatched_product->instant_balance = $next_item_stock_batch->balance;
                    $new_dispatched_product->remitted = 1;
                    $new_dispatched_product->save();
                }
            }
            $dispatched_product->delete();
            return 'done';
        }
    }
}
