<?php

namespace App\Http\Controllers\Invoice;

use App\Customer;
use App\Driver;
use App\Http\Controllers\Controller;
use App\Models\Invoice\DeliveryTrip;
use App\Models\Invoice\DeliveryTripExpense;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceHistory;
use App\Models\Invoice\InvoicePayment;
use App\Models\Invoice\InvoiceStatus;
use App\Models\Invoice\Waybill;
use App\Models\Invoice\DispatchedInvoice;
use App\Models\Invoice\DispatchedProduct;
use App\Models\Invoice\DispatchedWaybill;
use App\Models\Invoice\InvoiceItem;
use App\Models\Invoice\InvoiceItemBatch;
use App\Models\Invoice\WaybillItem;
use App\Models\Logistics\Vehicle;
use App\Models\Stock\Item;
use App\Models\Stock\ItemStock;
use App\Models\Stock\ItemStockSubBatch;
use App\Models\Warehouse\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class InvoicesController extends Controller
{
    const ITEM_PER_PAGE = 10;
    // public function clearPartialInvoices()
    // {
    //     $invoice_items = InvoiceItem::where(['delivery_status' => 'delivered', 'supply_status' => 'Partial'])->whereRaw('quantity = quantity_supplied')->get();
    //     foreach ($invoice_items as $invoice_item) {
    //         $invoice_item->supply_status = 'Complete';
    //         $invoice_item->save();
    //     }

    //     $invoices = Invoice::where('status', 'partially supplied')->get();
    //     foreach ($invoices as  $invoice) {

    //         $incomplete_invoice_item = $invoice->invoiceItems()->where('supply_status', '=', 'Partial')->first();
    //         if (!$incomplete_invoice_item) {
    //             $invoice->status = 'delivered';
    //             $invoice->full_waybill_generated = '1';
    //             $invoice->save();
    //         }
    //     }
    //     return 'done';
    // }
    // public function checkInvoiceItemsWithoutWaybill()
    // {
    //     $invoice_items = InvoiceItem::has('waybillItems', '<', 1)->with('batches')->where('quantity_supplied', '>', 0)->get();

    //     foreach ($invoice_items as $invoice_item) {
    //         $invoice_item->batches()->delete();
    //         $invoice_item->quantity_supplied = 0;
    //         $invoice_item->save();
    //     }
    // }
    // public function correctDispatchProductDate()
    // {
    //     set_time_limit(0);
    //     $waybills = Waybill::where('status', '!=', 'pending')->get();
    //     foreach ($waybills as $waybill) {
    //         $dispatch_products = $waybill->dispatchProducts;
    //         foreach ($dispatch_products as $dispatch_product) {
    //             $dispatch_product->created_at = $waybill->created_at;
    //             $dispatch_product->save();
    //         }
    //     }
    //     return 'true';
    // }
    // public function stabilizeInvoiceItems()
    // {
    //     set_time_limit(0);
    //     $invoice_items = InvoiceItem::where('remitted', 0)->where('quantity_supplied', '>', 0)->get();
    //     foreach ($invoice_items as $invoice_item) {
    //         $this->createInvoiceItemBatches($invoice_item, [], $invoice_item->quantity_supplied);
    //         $invoice_item->remitted = 1;
    //         $invoice_item->save();
    //     }
    //     return 'true';
    // }

    // public function deliverProducts()
    // {
    //     set_time_limit(0);
    //     $waybills = Waybill::where('remitted', 0)->where('status', '!=', 'pending')->get();
    //     foreach ($waybills as $waybill) {
    //         $waybillItems = $waybill->waybillItems()->where('remitted', 0)->get();

    //         $status = $waybill->status;
    //         if ($status != 'delivered') {
    //             $status = 'on transit';
    //         }
    //         $this->sendItemInStockForDelivery($waybillItems, $status);
    //         $waybill->remitted = 1;
    //         $waybill->save();
    //     }
    //     return 'true';
    // }

    // private function dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $quantity, $status)
    // {
    //     $dispatched_product = new DispatchedProduct();
    //     $dispatched_product->warehouse_id = $warehouse_id;
    //     $dispatched_product->item_stock_sub_batch_id = $item_stock_batch->id;
    //     $dispatched_product->waybill_id = $waybill_item->waybill_id;
    //     $dispatched_product->waybill_item_id = $waybill_item->id;
    //     $dispatched_product->quantity_supplied = $quantity;
    //     $dispatched_product->remitted = 1;
    //     $dispatched_product->instant_balance = $item_stock_batch->balance;
    //     $dispatched_product->status = $status;
    //     $dispatched_product->save();
    // }
    // public function sendItemInStockForDelivery($waybill_items, $status = 'on transit')
    // {
    //     foreach ($waybill_items as $waybill_item) {

    //         $warehouse_id = $waybill_item->warehouse_id;
    //         $waybill_quantity = $waybill_item->quantity;
    //         $invoice_item_id = $waybill_item->invoice_item_id;
    //         $invoice_item_batches = InvoiceItemBatch::with('itemStockBatch')->where('invoice_item_id', $invoice_item_id)->where('quantity', '>', '0')->get();
    //         // $items_in_stock= ItemStock::where(['warehouse_id' => $warehouse_id, 'item_id' => $waybill_item->item_id])
    //         //     ->where('balance', '>', '0')->orderBy('id')->get();
    //         // $item_stock_sub_batches = ItemStockSubBatch::with('itemStock')->where(['warehouse_id' => $warehouse_id, 'item_id' => $waybill_item->item_id])
    //         //     ->where('balance', '>', '0')->orderBy('id')->get();
    //         if ($invoice_item_batches->count() > 0) {
    //             foreach ($invoice_item_batches as $invoice_item_batch) :

    //                 $for_supply = $invoice_item_batch->quantity;
    //                 $item_stock_batch = $invoice_item_batch->itemStockBatch;
    //                 $item_id = $item_stock_batch->item_id;

    //                 if ($waybill_quantity <= $for_supply) {
    //                     $invoice_item_batch->quantity -= $waybill_quantity;
    //                     $invoice_item_batch->save();

    //                     if ($item_stock_batch->balance > 0) {
    //                         if ($item_stock_batch->balance >= $waybill_quantity) {
    //                             $item_stock_batch->reserved_for_supply -= $waybill_quantity;
    //                             if ($status == 'on transit') {
    //                                 $item_stock_batch->in_transit += $waybill_quantity;
    //                             } else {
    //                                 $item_stock_batch->supplied += $waybill_quantity;
    //                             }

    //                             $item_stock_batch->balance -=  $waybill_quantity;
    //                             $item_stock_batch->save();

    //                             $this->dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $waybill_quantity, $status);

    //                             $waybill_quantity = 0;
    //                         } else {
    //                             $waybill_quantity -= $item_stock_batch->balance;
    //                             $this->dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $item_stock_batch->balance, $status);
    //                             $item_stock_batch->reserved_for_supply -=
    //                                 $item_stock_batch->balance;
    //                             if ($status == 'on transit') {
    //                                 $item_stock_batch->in_transit += $item_stock_batch->balance;
    //                             } else {
    //                                 $item_stock_batch->supplied += $item_stock_batch->balance;
    //                             }
    //                             $item_stock_batch->balance =  0;
    //                             $item_stock_batch->save();

    //                             $next_item_stock_batches = ItemStockSubBatch::where('item_id', $item_id)->where('balance', '>', 0)->orderBy('id')->get();
    //                             foreach ($next_item_stock_batches as $next_item_stock_batch) {
    //                                 if ($waybill_quantity <= $next_item_stock_batch->balance) {

    //                                     if ($status == 'on transit') {
    //                                         $next_item_stock_batch->in_transit += $waybill_quantity;
    //                                     } else {
    //                                         $next_item_stock_batch->supplied += $waybill_quantity;
    //                                     }
    //                                     $next_item_stock_batch->balance -=  $waybill_quantity;
    //                                     $next_item_stock_batch->save();

    //                                     $this->dispatchProduct($warehouse_id, $next_item_stock_batch, $waybill_item, $waybill_quantity, $status);

    //                                     $waybill_quantity = 0;
    //                                     break;
    //                                 } else {
    //                                     if ($status == 'on transit') {
    //                                         $next_item_stock_batch->in_transit +=
    //                                             $next_item_stock_batch->balance;
    //                                     } else {
    //                                         $next_item_stock_batch->supplied +=
    //                                             $next_item_stock_batch->balance;
    //                                     }
    //                                     $this->dispatchProduct($warehouse_id, $next_item_stock_batch, $waybill_item, $next_item_stock_batch->balance, $status);

    //                                     $waybill_quantity -= $next_item_stock_batch->balance;
    //                                     $next_item_stock_batch->balance =  0;
    //                                     $next_item_stock_batch->save();
    //                                 }
    //                             }
    //                         }
    //                     } else {
    //                         $next_item_stock_batches = ItemStockSubBatch::where('item_id', $item_id)->where('balance', '>', 0)->orderBy('id')->get();
    //                         foreach ($next_item_stock_batches as $next_item_stock_batch) {
    //                             if ($waybill_quantity <= $next_item_stock_batch->balance) {
    //                                 if ($status == 'on transit') {
    //                                     $next_item_stock_batch->in_transit += $waybill_quantity;
    //                                 } else {
    //                                     $next_item_stock_batch->supplied += $waybill_quantity;
    //                                 }
    //                                 $next_item_stock_batch->balance -=  $waybill_quantity;
    //                                 $next_item_stock_batch->save();

    //                                 $this->dispatchProduct($warehouse_id, $next_item_stock_batch, $waybill_item, $waybill_quantity, $status);

    //                                 $waybill_quantity = 0;
    //                                 break;
    //                             } else {

    //                                 if ($status == 'on transit') {
    //                                     $next_item_stock_batch->in_transit += $next_item_stock_batch->balance;
    //                                 } else {
    //                                     $next_item_stock_batch->supplied += $next_item_stock_batch->balance;
    //                                 }
    //                                 $this->dispatchProduct($warehouse_id, $next_item_stock_batch, $waybill_item, $next_item_stock_batch->balance, $status);

    //                                 $waybill_quantity -= $next_item_stock_batch->balance;
    //                                 $next_item_stock_batch->balance =  0;
    //                                 $next_item_stock_batch->save();
    //                             }
    //                         }
    //                     }
    //                     //// also update item_stocks table/////////
    //                     // $invoice_item_batch->itemStockBatch->itemStock->in_transit +=  $waybill_quantity;
    //                     // $invoice_item_batch->itemStockBatch->itemStock->balance -=  $waybill_quantity;
    //                     // $invoice_item_batch->itemStockBatch->itemStock->save();



    //                     $waybill_quantity = 0; //we have sent all items for delivery
    //                     break;
    //                 } else {
    //                     $invoice_item_batch->quantity = 0;
    //                     $invoice_item_batch->save();

    //                     if ($item_stock_batch->balance > 0) {
    //                         if ($item_stock_batch->balance >= $for_supply) {
    //                             $item_stock_batch->reserved_for_supply -= $for_supply;
    //                             if ($status == 'on transit') {
    //                                 $item_stock_batch->in_transit += $for_supply;
    //                             } else {
    //                                 $item_stock_batch->supplied += $for_supply;
    //                             }

    //                             $item_stock_batch->balance -=  $for_supply;
    //                             $item_stock_batch->save();

    //                             $this->dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $for_supply, $status);
    //                         } else {
    //                             $for_supply -= $item_stock_batch->balance;
    //                             $this->dispatchProduct($warehouse_id, $item_stock_batch, $waybill_item, $item_stock_batch->balance, $status);
    //                             $item_stock_batch->reserved_for_supply -=
    //                                 $item_stock_batch->balance;
    //                             if ($status == 'on transit') {
    //                                 $item_stock_batch->in_transit += $item_stock_batch->balance;
    //                             } else {
    //                                 $item_stock_batch->supplied += $item_stock_batch->balance;
    //                             }

    //                             $item_stock_batch->balance =  0;
    //                             $item_stock_batch->save();

    //                             $next_item_stock_batches2 = ItemStockSubBatch::where('item_id', $item_id)->where('balance', '>', 0)->orderBy('id')->get();
    //                             foreach ($next_item_stock_batches2 as $next_item_stock_batch2) {
    //                                 if ($for_supply <= $next_item_stock_batch2->balance) {
    //                                     if ($status == 'on transit') {
    //                                         $next_item_stock_batch2->in_transit += $for_supply;
    //                                     } else {
    //                                         $next_item_stock_batch2->supplied += $for_supply;
    //                                     }

    //                                     $next_item_stock_batch2->balance -=  $for_supply;
    //                                     $next_item_stock_batch2->save();
    //                                     $this->dispatchProduct($warehouse_id, $next_item_stock_batch2, $waybill_item, $for_supply, $status);
    //                                     //$for_supply = 0;
    //                                     break;
    //                                 } else {
    //                                     if ($status == 'on transit') {
    //                                         $next_item_stock_batch2->in_transit += $next_item_stock_batch2->balance;
    //                                     } else {
    //                                         $next_item_stock_batch2->supplied += $next_item_stock_batch2->balance;
    //                                     }

    //                                     $this->dispatchProduct($warehouse_id, $next_item_stock_batch2, $waybill_item, $next_item_stock_batch2->balance, $status);
    //                                     $for_supply -= $next_item_stock_batch2->balance;

    //                                     $next_item_stock_batch2->balance =  0;
    //                                     $next_item_stock_batch2->save();
    //                                 }
    //                             }
    //                         }
    //                     } else {
    //                         $next_item_stock_batches2 = ItemStockSubBatch::where('item_id', $item_id)->where('balance', '>', 0)->orderBy('id')->get();
    //                         foreach ($next_item_stock_batches2 as $next_item_stock_batch2) {
    //                             if ($for_supply <= $next_item_stock_batch2->balance) {
    //                                 if ($status == 'on transit') {
    //                                     $next_item_stock_batch2->in_transit += $for_supply;
    //                                 } else {
    //                                     $next_item_stock_batch2->supplied += $for_supply;
    //                                 }
    //                                 $next_item_stock_batch2->balance -=  $for_supply;
    //                                 $next_item_stock_batch2->save();
    //                                 $this->dispatchProduct($warehouse_id, $next_item_stock_batch2, $waybill_item, $for_supply, $status);
    //                                 //$for_supply = 0;
    //                                 break;
    //                             } else {

    //                                 if ($status == 'on transit') {
    //                                     $next_item_stock_batch2->in_transit += $next_item_stock_batch2->balance;
    //                                 } else {
    //                                     $next_item_stock_batch2->supplied += $next_item_stock_batch2->balance;
    //                                 }
    //                                 $this->dispatchProduct($warehouse_id, $next_item_stock_batch2, $waybill_item, $next_item_stock_batch2->balance, $status);
    //                                 $for_supply -= $next_item_stock_batch2->balance;

    //                                 $next_item_stock_batch2->balance =  0;
    //                                 $next_item_stock_batch2->save();
    //                             }
    //                         }
    //                     }


    //                     $waybill_quantity -= $for_supply;
    //                 }
    //             endforeach;
    //         }
    //         $waybill_item->remitted = 1;
    //         $waybill_item->save();
    //     }
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $searchParams = $request->all();
        $invoiceQuery = Invoice::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        if (!empty($keyword)) {
            $invoiceQuery->where(function ($q) use ($keyword) {
                $q->where('invoice_number', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('invoice_date', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('amount', 'LIKE', '%' . $keyword . '%');
                $q->orWhere('created_at', 'LIKE', '%' . $keyword . '%');
                $q->orWhereHas('customer', function ($q) use ($keyword) {
                    $q->whereHas('user', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', '%' . $keyword . '%');
                    });
                });
            });
        }
        $user = $this->getUser();
        $warehouse_id = $request->warehouse_id;
        $invoices = [];
        if (isset($request->status) && $request->status != '') {
            ////// query by status //////////////
            $status = $request->status;
            $invoices = $invoiceQuery->with(['warehouse', 'waybillItems', 'customer.user', 'customer.type', 'confirmer', 'invoiceItems.item', 'histories' => function ($q) {
                $q->orderBy('id', 'DESC');
            }])->where(['warehouse_id' => $warehouse_id, 'status' => $status])->orderBy('updated_at', 'DESC')->paginate($limit);
        }
        if (isset($request->from, $request->to, $request->status) && $request->from != '' && $request->from != '' && $request->status != '') {
            $date_from = date('Y-m-d', strtotime($request->from)) . ' 00:00:00';
            $date_to = date('Y-m-d', strtotime($request->to)) . ' 23:59:59';
            $status = $request->status;
            $panel = $request->panel;
            $invoices = $invoiceQuery->with(['warehouse', 'waybillItems', 'customer.user', 'customer.type', 'confirmer',  'invoiceItems.item', 'histories' => function ($q) {
                $q->orderBy('id', 'DESC');
            }])->where(['warehouse_id' => $warehouse_id, 'status' => $status])->where('created_at', '>=', $date_from)->where('created_at', '<=', $date_to)->orderBy('updated_at', 'DESC')->paginate($limit);
        }
        return response()->json(compact('invoices'));
    }
    public function unDeliveredInvoices(Request $request)
    {
        //
        $user = $this->getUser();
        $warehouse_id = $request->warehouse_id;
        $waybill_no = $this->nextReceiptNo('waybill');
        /*$invoices = Invoice::get();
        foreach ($invoices as $invoice) {
            $customer = Customer::find($invoice->customer_id)->first();
            if (!$customer) {
                $invoice->delete();
            }
        }*/
        // $invoices = Invoice::with(['invoiceItems', 'invoiceItems.item'])->where('warehouse_id', $warehouse_id)->where('status', '!=', 'delivered')->get();
        $invoices = Invoice::with(['customer.user', 'confirmer', 'invoiceItems' => function ($q) {
            $q->where('supply_status', '!=', 'Complete');
        }, 'invoiceItems.item.stocks' => function ($p) use ($warehouse_id) {
            $p->whereRaw('balance - reserved_for_supply > 0')->where('warehouse_id', $warehouse_id)->where('confirmed_by', '!=', null);
        }])->where('warehouse_id', $warehouse_id)->where('full_waybill_generated', '0')->where('confirmed_by', '!=', null)->orderBy('id', 'DESC')->get();
        return response()->json(compact('invoices', 'waybill_no'), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignInvoiceToWarehouse(Request $request, Invoice $invoice)
    {

        $warehouse_id = $request->warehouse_id;
        $warehouse = Warehouse::find($warehouse_id);
        $invoice->warehouse_id = $warehouse_id;
        $invoice->save();
        //log this activity
        $title = "Invoice Assigned";
        $description = "Assigned invoice ($invoice->invoice_number) to " . $warehouse->name;
        $roles = ['warehouse manager'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($invoice);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = $this->getUser();
        $invoice_items = json_decode(json_encode($request->invoice_items));
        $dupicate_invoice = Invoice::where('invoice_number', $request->invoice_number)->first();
        if ($dupicate_invoice) {
            return response()->json(['message' => 'Duplicate Invoice'], 500);
        }
        $invoice = new Invoice();
        $invoice->warehouse_id        = $request->warehouse_id;
        $invoice->customer_id         = $request->customer_id;
        $invoice->subtotal            = $request->subtotal;
        $invoice->discount            = $request->discount;
        $invoice->amount              = $request->amount;
        $invoice->invoice_number      = $this->nextReceiptNo('invoice'); // $request->invoice_number; // $this->nextInvoiceNo();
        $invoice->status              = $request->status;
        $invoice->notes              = $request->notes;
        $invoice->invoice_date        = date('Y-m-d H:i:s', strtotime($request->invoice_date));
        $invoice->save();
        $title = "New invoice generated";
        $description = "New $invoice->status invoice ($invoice->invoice_number) was generated by $user->name ($user->email)";
        //log this action to invoice history
        $this->createInvoiceHistory($invoice, $title, $description);
        //create items invoiceed for
        $this->createInvoiceItems($invoice, $invoice_items);
        //////update next invoice number/////
        $this->incrementReceiptNo('invoice');

        //log this activity
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor', 'stock officer'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($invoice);
    }

    public function bulkUpload(Request $request)
    {
        // return $request;
        $user = $this->getUser();
        $bulk_invoices = json_decode(json_encode($request->bulk_invoices));
        $warehouse_id = $request->warehouse_id;
        $error = [];
        $header_error = [];
        foreach ($bulk_invoices as $bulk_invoice) {
            $customer_id = $bulk_invoice->customer_id;
            $invoice_number = $bulk_invoice->invoice_number;
            $invoice_date = $bulk_invoice->invoice_date;
            $dupicate_invoice = Invoice::where('invoice_number', $invoice_number)->first();
            if (!$dupicate_invoice) {
                // We want to make sure all headers are set and what we need
                $invoice_item_headers =  json_decode(json_encode($bulk_invoice->bulk_invoices_header));
                if (!in_array('Description of Goods', $invoice_item_headers)) {
                    $header_error[] = "'Description of Goods' is needed as an header";
                }
                if (!in_array('Quantity', $invoice_item_headers)) {
                    $header_error[] = "'Quantity' is needed as an header";
                }
                if (!in_array('Rate', $invoice_item_headers)) {
                    $header_error[] = "'Rate' is needed as an header";
                }
                if (!in_array('per', $invoice_item_headers)) {
                    $header_error[] = "'per' is needed as an header";
                }
                if (!in_array('Amount', $invoice_item_headers)) {
                    $header_error[] = "'Amount' is needed as an header";
                }
                if (empty($header_error)) {
                    $invoice_items =  json_decode(json_encode($bulk_invoice->bulk_invoices_data));
                    $amount = 0;
                    $discount = 0;
                    foreach ($invoice_items as $item) {
                        $amount_label = 'Amount';
                        $amount += $item->$amount_label;
                    }
                    $invoice = new Invoice();
                    $invoice->warehouse_id        = $warehouse_id;
                    $invoice->customer_id         = $customer_id;
                    $invoice->subtotal            = $amount;
                    $invoice->discount            = $discount;
                    $invoice->amount              = $amount;
                    $invoice->invoice_number      = $invoice_number; // $this->nextInvoiceNo();
                    $invoice->status              = 'pending';
                    $invoice->notes              = 'BEING GOODS SOLD TO THE ABOVE CUSTOMER';
                    $invoice->invoice_date        = date('Y-m-d H:i:s', strtotime($invoice_date));
                    $invoice->save();
                    if ($invoice->save()) {
                        $title = "New invoice generated";
                        $description = "New $invoice->status invoice ($invoice->invoice_number) was generated by $user->name ($user->email)";
                        //log this action to invoice history
                        $this->createInvoiceHistory($invoice, $title, $description);
                        foreach ($invoice_items as $item) {
                            $description_label = 'Description of Goods';
                            $quantity_label = 'Quantity';
                            $rate_label = 'Rate';
                            $amount_label = 'Amount';
                            $type_label = 'per';

                            $product = Item::where('name', $item->$description_label)->first();
                            if ($product) {
                                $invoice_item = new InvoiceItem();
                                $invoice_item->warehouse_id = $warehouse_id;
                                $invoice_item->invoice_id = $invoice->id;
                                $invoice_item->item_id = $product->id;
                                $invoice_item->quantity = $item->$quantity_label;
                                $invoice_item->quantity_per_carton = $product->quantity_per_carton;
                                $invoice_item->no_of_cartons = $invoice_item->quantity / $invoice_item->quantity_per_carton;
                                $invoice_item->type = $item->$type_label;
                                $invoice_item->rate = $item->$rate_label;
                                $invoice_item->amount = $item->$amount_label;
                                $invoice_item->save();
                            } else {
                                $error[] = $item->$description_label . ' is not found in the database. This might be a spelling issue. Contact the admin';
                            }
                        }
                    }
                } else {
                    // Terminate if a header is not set
                    return response()->json(['message' => 'error', 'error' => $header_error], 200);
                }
            } else {
                $error[] = 'Invoice number: ' . $invoice_number . ' already exists.';
            }
        }
        $message = 'success';
        if (!empty($error)) {
            $message = 'error';
        } else {
        }
        return response()->json(['message' => $message, 'error' => $error], 200);
    }
    private function createInvoiceHistory($invoice, $title, $description)
    {
        $invoice_history = new InvoiceHistory();
        $invoice_history->invoice_id = $invoice->id;
        $invoice_history->title = $title;
        $invoice_history->description = $description;
        $invoice_history->save();
    }

    private function createInvoiceItems($invoice, $invoice_items)
    {
        foreach ($invoice_items as $item) {
            // $batches = $item->batches;
            $invoice_item = new InvoiceItem();
            $invoice_item->warehouse_id = $invoice->warehouse_id;
            $invoice_item->invoice_id = $invoice->id;
            $invoice_item->item_id = $item->item_id;
            $invoice_item->quantity = $item->quantity;
            $invoice_item->quantity_per_carton = $item->quantity_per_carton;
            $invoice_item->no_of_cartons = $item->quantity / $item->quantity_per_carton; //$item->no_of_cartons;
            $invoice_item->type = $item->type;
            $invoice_item->rate = $item->rate;
            $invoice_item->amount = $item->amount;
            $invoice_item->save();
        }
    }
    private function createInvoiceItemBatches($invoice_item, $batches, $quantity)
    {

        //$quantity = $invoice_item->quantity;
        // $quantity = $invoice_item->quantity_supplied;
        // If a specific batch was set when raising the invoice, we set it here
        if (!empty($batches)) {
            foreach ($batches as $batch) {
                $item_sub_batch = ItemStockSubBatch::find($batch);
                $real_balance = $item_sub_batch->balance - $item_sub_batch->reserved_for_supply;
                if ($quantity <= $real_balance) {
                    $invoice_item_batch = new InvoiceItemBatch();
                    $invoice_item_batch->invoice_id = $invoice_item->invoice_id;
                    $invoice_item_batch->invoice_item_id = $invoice_item->id;
                    $invoice_item_batch->item_stock_sub_batch_id = $batch;
                    $invoice_item_batch->to_supply = $quantity;
                    $invoice_item_batch->quantity = $quantity;
                    $invoice_item_batch->save();
                    $item_sub_batch->reserved_for_supply += $quantity;
                    $item_sub_batch->save();
                    $quantity = 0;
                    break;
                } else {
                    $invoice_item_batch = new InvoiceItemBatch();
                    $invoice_item_batch->invoice_id = $invoice_item->invoice_id;
                    $invoice_item_batch->invoice_item_id = $invoice_item->id;
                    $invoice_item_batch->item_stock_sub_batch_id = $batch;
                    $invoice_item_batch->to_supply = $real_balance;
                    $invoice_item_batch->quantity = $real_balance;
                    $invoice_item_batch->save();
                    $item_sub_batch->reserved_for_supply += $real_balance;
                    $item_sub_batch->save();
                    $quantity -= $real_balance;
                }
            }
        }

        if ($quantity > 0) {
            // If a specific batch was NOT set when raising the invoice, we make it automatic here using FIFO (First In First Out) principle
            $batches_of_items_in_stock = ItemStockSubBatch::where(['warehouse_id' => $invoice_item->invoice->warehouse_id, 'item_id' => $invoice_item->item_id])->whereRaw('balance - reserved_for_supply > 0')->orderBy('expiry_date')->get();

            foreach ($batches_of_items_in_stock as $item_sub_batch) {
                $real_balance = $item_sub_batch->balance - $item_sub_batch->reserved_for_supply;
                if ($quantity <= $real_balance) {
                    $invoice_item_batch = new InvoiceItemBatch();
                    $invoice_item_batch->invoice_id = $invoice_item->invoice_id;
                    $invoice_item_batch->invoice_item_id = $invoice_item->id;
                    $invoice_item_batch->item_stock_sub_batch_id = $item_sub_batch->id;
                    $invoice_item_batch->to_supply = $quantity;
                    $invoice_item_batch->quantity = $quantity;
                    $invoice_item_batch->save();
                    $item_sub_batch->reserved_for_supply += $quantity;
                    $item_sub_batch->save();
                    $quantity = 0;
                    break;
                } else {
                    $invoice_item_batch = new InvoiceItemBatch();
                    $invoice_item_batch->invoice_id = $invoice_item->invoice_id;
                    $invoice_item_batch->invoice_item_id = $invoice_item->id;
                    $invoice_item_batch->item_stock_sub_batch_id = $item_sub_batch->id;
                    $invoice_item_batch->to_supply = $real_balance;
                    $invoice_item_batch->quantity = $real_balance;
                    $invoice_item_batch->save();
                    $item_sub_batch->reserved_for_supply += $real_balance;
                    $item_sub_batch->save();
                    $quantity -= $real_balance;
                }
            }
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
        $user = $this->getUser();
        $invoice_items = json_decode(json_encode($request->invoice_items));
        $invoice = Invoice::find($request->id);
        $old_invoice_number = $invoice->invoice_number;
        $invoice->invoice_number      = $request->invoice_number;
        $invoice->customer_id      = $request->customer_id;
        $invoice->invoice_date      = date('Y-m-d H:i:s', strtotime($request->invoice_date));
        $invoice->subtotal            = $request->subtotal;
        $invoice->discount            = $request->discount;
        $invoice->amount              = $request->amount;
        $invoice->notes              = $request->notes;
        $invoice->save();
        $extra_info = "";
        if ($old_invoice_number !== $invoice->invoice_number) {
            $extra_info = $old_invoice_number . ' was changed to ' . $invoice->invoice_number;
        }
        if ($invoice->waybillItems()->count() < 1) {
            // delete existing invoice items
            $invoice->invoiceItems()->delete();
            //create new set of invoice items
            $this->createInvoiceItems($invoice, $invoice_items);
        }

        $title = "Invoice modified";
        $description = "invoice ($old_invoice_number) was updated by $user->name ($user->email) " . $extra_info;
        //log this action to invoice history
        $this->createInvoiceHistory($invoice, $title, $description);
        //create items invoiceed for

        //////update next invoice number/////
        // $this->incrementInvoiceNo();

        //log this activity
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($invoice);
    }

    private function updateInvoiceItems($invoice_items)
    {
        foreach ($invoice_items as $item) {
            // $batches = $item->batches;
            $invoice_item = InvoiceItem::find($item->id);
            // keep the old quantity
            // $old_quantity = $invoice_item->quantity;

            $invoice_item->quantity = $item->quantity;
            $invoice_item->no_of_cartons = $item->no_of_cartons;
            $invoice_item->item_id = $item->item_id;
            $invoice_item->type = $item->type;
            $invoice_item->rate = $item->rate;
            $invoice_item->amount = $item->amount;
            $invoice_item->save();

            // $batches = $invoice_item->batches;
            // $this->removeOldInvoiceItemBatchesAndCreateNewOne($invoice_item, $batches, $old_quantity);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        //
        $invoice =  $invoice->with(['warehouse', 'waybillItems', 'customer.user', 'customer.type', 'confirmer', 'invoiceItems.item', 'histories' => function ($q) {
            $q->orderBy('id', 'DESC');
        }])->find($invoice->id);
        return response()->json(compact('invoice'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function changeInvoiceStaus(Request $request, Invoice $invoice)
    {
        //
        $user = $this->getUser();
        $status = $request->status;

        $invoice->status = $status;
        $invoice->save();
        $title = "Invoice status updated";
        $description = "Invoice ($invoice->invoice_number) status changed to " . strtoupper($invoice->status) . " by $user->name ($user->email)";
        //log this action to invoice history
        $this->createInvoiceHistory($invoice, $title, $description);

        // log this activity
        $roles = ['assistant admin', 'warehouse manager'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($invoice);
    }
    // this fetches all generated waybills
    public function waybills(Request $request)
    {
        $searchParams = $request->all();
        $waybillQuery = Waybill::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        $warehouse_id = $request->warehouse_id;
        $waybills = [];

        if (isset($request->status) && $request->status != '') {
            $status = $request->status;

            ////// query by status //////////////
            $waybillQuery->with(['invoices.customer.user', 'invoices.invoiceItems.item.stocks', 'waybillItems.invoice.customer.user', 'waybillItems.item', 'waybillItems.invoiceItem.batches.itemStockBatch', 'dispatcher.vehicle.vehicleDrivers.driver.user', 'trips', 'dispatchProducts'])->where(['warehouse_id' => $warehouse_id, 'status' => $status]);
            if (!empty($keyword)) {
                $waybillQuery->where(function ($q) use ($keyword) {
                    $q->where('waybill_no', 'LIKE', '%' . $keyword . '%');
                    $q->orWhere('dispatch_company', 'LIKE', '%' . $keyword . '%');
                    $q->orWhereHas('invoices', function ($q) use ($keyword) {
                        $q->where('invoice_number', 'LIKE', '%' . $keyword . '%');
                    });
                });
            }
            $waybills = $waybillQuery->orderBy('id', 'DESC')->paginate($limit);
        }
        // if (isset($request->from, $request->to, $request->status) && $request->from != '' && $request->from != '' && $request->status != '') {
        //     $date_from = date('Y-m-d', strtotime($request->from)) . ' 00:00:00';
        //     $date_to = date('Y-m-d', strtotime($request->to)) . ' 23:59:59';
        //     $status = $request->status;
        //     $panel = $request->panel;
        //     $invoices = Waybill::with(['dispatcher.vehicle.vehicleDrivers.driver.user', 'waybillItems.invoice.customer.user', 'waybillItems.item'])->where(['warehouse_id' => $warehouse_id, 'status' => $status])->where('created_at', '>=', $date_from)->where('created_at', '<=', $date_to)->get();
        // }
        return response()->json(compact('waybills'));
    }
    // this fetches available drivers so that dispatching orders could be assigned to them
    public function fetchAvailableVehicles(Request $request)
    {
        //
        $warehouse_id = $request->warehouse_id;
        $available_vehicles = Vehicle::with('vehicleDrivers.driver.user')->where(['warehouse_id' => $warehouse_id, 'availabilty' => 'available'])->get();
        // $available_vehicles = [];
        // foreach ($vehicles as $vehicle) {
        //     $dispatched_waybill = DispatchedWaybill::where('vehicle_id', $vehicle->id)->orderBy('id', 'Desc')->first();
        //     if ($dispatched_waybill) {
        //         if ($dispatched_waybill->waybill->status === 'delivered') {
        //             $available_vehicles[] = $vehicle;
        //         }
        //     } else {
        //         $available_vehicles[] = $vehicle;
        //     }
        // }
        return response()->json(compact('available_vehicles'), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generateWaybill(Request $request)
    {
        //
        $user = $this->getUser();
        $invoice_ids = $request->invoice_ids;
        // $invoice = Invoice::find($request->invoice_id);
        $warehouse_id = $request->warehouse_id;
        $warehouse_id = $request->warehouse_id;
        $message = '';
        $invoice_items = json_decode(json_encode($request->invoice_items));

        $waybill_no = $this->nextReceiptNo('waybill');
        $waybill = new Waybill();
        $waybill->warehouse_id = $warehouse_id;
        // $waybill->invoice_id = $request->invoice_id;
        $waybill->waybill_no = $waybill_no;
        $waybill->status = $request->status;
        $waybill->save();

        $this->incrementReceiptNo('waybill');

        $waybill->invoices()->sync($invoice_ids);
        // create way bill items
        $waybill_item_obj = new WaybillItem();
        // $waybill_item_obj->createWaybillItems($waybill->id, $warehouse_id, $invoice_items);
        // $waybill = Waybill::where('waybill_no', $request->waybill_no)->first();
        // if ($waybill) {
        //     return response()->json(['message' => 'Dupicate Waybill No. Refresh the page please!!!'], 500);
        // }
        // check if there are items in stock for this waybil to be generated
        $partial_waybill_generated = [];
        foreach ($invoice_items as $invoice_item) {
            $batches = $invoice_item->batches;

            $invoice_item_update = InvoiceItem::find($invoice_item->id);
            $original_quantity = $invoice_item_update->quantity;
            $quantity_supplied = $invoice_item_update->quantity_supplied;
            $for_supply = (int) $invoice_item->quantity_for_supply;
            if ($for_supply > 0) {
                if ($original_quantity > $quantity_supplied) {
                    if (($original_quantity - $quantity_supplied)  >= $for_supply) {
                        $invoice_item_update->quantity_supplied += $for_supply;
                        $invoice_item_update->save();
                        $this->createInvoiceItemBatches($invoice_item_update, $batches, $for_supply);

                        $waybill_item_obj->createWaybillItems($waybill->id, $warehouse_id, $invoice_item);
                    }
                }
            }

            if ($original_quantity > $invoice_item_update->quantity_supplied) {
                $partial_waybill_generated[] = $invoice_item->invoice_id;
            }

            // $item_in_stock_obj = new ItemStockSubBatch();
            // $item_balance = $item_in_stock_obj->fetchBalanceOfItemsInStock($warehouse_id, $invoice_item->item_id);

            // $quantity_for_supply = $invoice_item->quantity_for_supply;
            // // check whether the balance is up to what was raised in the invoice
            // if ($quantity_for_supply > $item_balance) {
            //     $message .= $invoice_item->item->name . ' remains only ' . $item_balance . ' ' . $invoice_item->item->package_type . ' in stock.<br>';
            // }
        }

        $invoice_nos = [];
        foreach ($invoice_ids as $invoice_id) {
            $invoice = Invoice::find($invoice_id);
            if (!in_array($invoice_id, $partial_waybill_generated)) {
                $invoice->full_waybill_generated = '1';
                $invoice->save();
            }
            $title = "Waybill Generated";
            $description = "Waybill ($waybill->waybill_no) generated for invoice ($invoice->invoice_number) by $user->name ($user->email)";
            //log this action to invoice history
            $this->createInvoiceHistory($invoice, $title, $description);

            //log this activity
            $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor', 'stock officer'];
            $this->logUserActivity($title, $description, $roles);
        }
        // }
        // $this->createDispatchedWaybill($waybill, $request);

        return response()->json(compact('waybill'), 200);
    }

    public function updateWaybill(Request $request, $waybill_id)
    {
        $user = $this->getUser();

        $waybill = Waybill::find($waybill_id);
        // we fetch previous waybill items
        $waybill_items = $waybill->waybillItems;

        foreach ($waybill_items as $waybill_item) {
            // we want to get all invoice item for this waybill item and deduct what was reserved
            $invoice_item = $waybill_item->invoiceItem;
            $invoice_item->quantity_supplied -= $waybill_item->quantity;
            $invoice_item->save();

            // we wanna get and remove all reserved products

            $waybill_quantity = $waybill_item->quantity;
            $reserved_batches = $invoice_item->batches()->where('to_supply', '<=', $waybill_quantity)->get();
            foreach ($reserved_batches as $reserved_batch) {
                if ($reserved_batch->to_supply <= $waybill_quantity) {
                    $item_stock_batch = $reserved_batch->itemStockBatch;
                    $item_stock_batch->reserved_for_supply -= $reserved_batch->to_supply;
                    $item_stock_batch->save();

                    $waybill_quantity -= $reserved_batch->to_supply;
                    // delete the invoice item batch
                    $reserved_batch->delete();
                } else {
                    $item_stock_batch = $reserved_batch->itemStockBatch;
                    $item_stock_batch->reserved_for_supply -= $waybill_quantity;
                    $item_stock_batch->save();

                    $reserved_batch->to_supply -= $waybill_quantity;
                    $reserved_batch->quantity -= $waybill_quantity;
                    $reserved_batch->save();
                    break;
                }
            }

            // delete the waybill item
            $waybill_item->delete();
        }
        // now we want to create a new waybill item with their reservations
        $invoice_ids = $request->invoice_ids;
        $warehouse_id = $request->warehouse_id;
        $message = '';
        $invoice_items = json_decode(json_encode($request->invoice_items));

        $waybill->invoices()->sync($invoice_ids);
        // create way bill items
        $waybill_item_obj = new WaybillItem();
        // $waybill_item_obj->createWaybillItems($waybill->id, $warehouse_id, $invoice_items);
        // $waybill = Waybill::where('waybill_no', $request->waybill_no)->first();
        // if ($waybill) {
        //     return response()->json(['message' => 'Dupicate Waybill No. Refresh the page please!!!'], 500);
        // }
        // check if there are items in stock for this waybil to be generated
        $partial_waybill_generated = [];
        $invoice_items_ids = [];
        foreach ($invoice_items as $invoice_item) {
            if (!in_array($invoice_item->id, $invoice_items_ids)) {
                $invoice_items_ids[] = $invoice_item->id;
                $batches = $invoice_item->batches;

                $invoice_item_update = InvoiceItem::find($invoice_item->id);
                $original_quantity = $invoice_item_update->quantity;
                $original_quantity_supplied = $invoice_item_update->quantity_supplied;
                $quantity_supplied = (int) $invoice_item->quantity_supplied;
                $for_supply = (int) $invoice_item->quantity_for_supply;
                // this will enable previously generated invoice items to be intact if not modified
                if ($for_supply < 1 && $quantity_supplied > 0) {
                    $for_supply = $quantity_supplied;
                    $invoice_item->quantity_for_supply = $quantity_supplied;
                }
                if ($for_supply > 0) {
                    if ($original_quantity > $original_quantity_supplied) {
                        if (($original_quantity - $original_quantity_supplied)  >= $for_supply) {
                            $invoice_item_update->quantity_supplied += $for_supply;
                            $invoice_item_update->save();
                            $this->createInvoiceItemBatches($invoice_item_update, $batches, $for_supply);

                            $waybill_item_obj->createWaybillItems($waybill->id, $warehouse_id, $invoice_item);
                        }
                    }
                }

                if ($original_quantity > $invoice_item_update->quantity_supplied) {
                    $partial_waybill_generated[] = $invoice_item->invoice_id;
                }
            }


            // $item_in_stock_obj = new ItemStockSubBatch();
            // $item_balance = $item_in_stock_obj->fetchBalanceOfItemsInStock($warehouse_id, $invoice_item->item_id);

            // $quantity_for_supply = $invoice_item->quantity_for_supply;
            // // check whether the balance is up to what was raised in the invoice
            // if ($quantity_for_supply > $item_balance) {
            //     $message .= $invoice_item->item->name . ' remains only ' . $item_balance . ' ' . $invoice_item->item->package_type . ' in stock.<br>';
            // }
        }

        $invoice_nos = [];
        foreach ($invoice_ids as $invoice_id) {
            $invoice = Invoice::find($invoice_id);
            if (!in_array($invoice_id, $partial_waybill_generated)) {
                $invoice->full_waybill_generated = '1';
            } else {
                $invoice->full_waybill_generated = '0';
            }
            $invoice->save();
            $title = "Waybill Generated";
            $description = "Waybill ($waybill->waybill_no) generated for invoice ($invoice->invoice_number) by $user->name ($user->email)";
            //log this action to invoice history
            $this->createInvoiceHistory($invoice, $title, $description);

            //log this activity
            $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor', 'stock officer'];
            $this->logUserActivity($title, $description, $roles);
        }
        // }
        // $this->createDispatchedWaybill($waybill, $request);

        return response()->json(compact('waybill'), 200);
    }
    private function createDispatchedWaybill($waybill_id, $vehicle_id)
    {
        //
        $dispatched_waybill = DispatchedWaybill::where('waybill_id', $waybill_id)->first();
        if (!$dispatched_waybill) {
            $dispatched_waybill = new DispatchedWaybill();
        }
        $dispatched_waybill->waybill_id = $waybill_id;
        $dispatched_waybill->vehicle_id = $vehicle_id;
        $dispatched_waybill->save();
    }
    public function waybillExpenses(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $trip_no = $this->nextReceiptNo('trip');
        $vehicles = Vehicle::with('vehicleDrivers.driver.user')/*->where('warehouse_id', $warehouse_id)*/->get();
        $delivery_trips = DeliveryTrip::with('cost.confirmer', 'waybills', 'vehicle.vehicleDrivers.driver.user')->orderBy('id', 'DESC')->where(['warehouse_id' => $warehouse_id])->get();

        $waybills_with_pending_wayfare = Waybill::where(['warehouse_id' => $warehouse_id, 'waybill_wayfare_status' => 'pending'])->where('confirmed_by', '!=', null)->get();

        return response()->json(compact('delivery_trips', 'waybills_with_pending_wayfare', 'trip_no', 'vehicles'), 200);
    }

    public function addWaybillExpenses(Request $request)
    {
        $trip_no = $request->trip_no;
        $delivery_trip = DeliveryTrip::where('trip_no', $trip_no)->first();
        if ($delivery_trip) {
            // $this->incrementReceiptNo('waybill');
            $trip_no = $this->nextReceiptNo('trip');
        }
        $waybill_ids = $request->waybill_ids;
        $description = $request->description;
        $warehouse_id = $request->warehouse_id;
        $vehicle_id = $request->vehicle_id;
        $vehicle_no = $request->vehicle_no;
        $dispatchers = $request->dispatchers;
        // if ($vehicle_id !== null) {
        //     $vehicle = Vehicle::with('vehicleDrivers.driver.user')->find($vehicle_id);
        // }
        $dispatch_company = $request->dispatch_company;

        # code...

        $delivery_trip = new DeliveryTrip();
        $delivery_trip->warehouse_id = $warehouse_id;
        $delivery_trip->vehicle_id = $vehicle_id;
        $delivery_trip->dispatch_company = $dispatch_company;
        $delivery_trip->vehicle_no = $vehicle_no;
        $delivery_trip->dispatchers = $dispatchers;
        $delivery_trip->trip_no = $trip_no;
        $delivery_trip->description = $description;
        if ($delivery_trip->save()) {
            //update next receipt no
            $this->incrementReceiptNo('trip');
            $delivery_trip->waybills()->syncWithoutDetaching($waybill_ids); // add all waybills for this trip
            foreach ($delivery_trip->waybills as $waybill) {
                // update waybill wayfare status
                if ($vehicle_id != null) {
                    $this->createDispatchedWaybill($waybill->id, $vehicle_id);
                }
                $waybill->dispatch_company = $dispatch_company;
                $waybill->waybill_wayfare_status = 'given';
                $waybill->save();
            }
            // populate delivery trip expenses
            $delivery_trip_id = $delivery_trip->id;
            $delivery_expense = DeliveryTripExpense::where('delivery_trip_id', $delivery_trip_id)->first();
            if (!$delivery_expense) {
                $delivery_expense = new DeliveryTripExpense();
                $delivery_expense->warehouse_id = $warehouse_id;
                $delivery_expense->delivery_trip_id = $delivery_trip_id;
                $delivery_expense->amount = $request->amount;
                $delivery_expense->details = $description;
                $delivery_expense->save();
            }
        }
        $user = $this->getUser();
        $title = "Created waybill delivery cost";
        $description = "New delivery cost for trip no.: " . $trip_no . " was created by $user->name ($user->email)";
        //log this activity
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];

        $this->logUserActivity($title, $description, $roles);
        return $this->showDeliveryTrip($delivery_trip->id, $warehouse_id);
    }

    public function deliveryTripsForExtraCost(Request $request)
    {
        $regular_delivery_trips = DeliveryTripExpense::with('deliveryTrip')->where('expense_type', 'regular')->get();
        $extra_delivery_costs = DeliveryTripExpense::with(['confirmer', 'deliveryTrip.waybills', 'deliveryTrip.vehicle.vehicleDrivers.driver.user'])->where('expense_type', 'extra')->get();
        return response()->json(compact('regular_delivery_trips', 'extra_delivery_costs'), 200);
    }

    public function addExtraDeliveryCost(Request $request)
    {
        $delivery_trip_id = $request->delivery_trip_id;
        $delivery_trip = DeliveryTrip::find($delivery_trip_id);
        $delivery_expense = DeliveryTripExpense::where(['delivery_trip_id' => $delivery_trip_id, 'expense_type' => 'extra'])->first();
        if (!$delivery_expense) {
            $delivery_expense = new DeliveryTripExpense();
            $delivery_expense->warehouse_id = $delivery_trip->warehouse_id;
            $delivery_expense->delivery_trip_id = $request->delivery_trip_id;
            $delivery_expense->expense_type = 'extra';
            $delivery_expense->amount = $request->amount;
            $delivery_expense->details = $request->details;
            $delivery_expense->save();
        }
        $delivery_expense = DeliveryTripExpense::with(['confirmer', 'deliveryTrip.waybills', 'deliveryTrip.vehicle.vehicleDrivers.driver.user'])->where('expense_type', 'extra')->find($delivery_expense->id);
        return response()->json(compact('delivery_expense'), 200);
    }
    private function showDeliveryTrip($delivery_trip_id, $warehouse_id)
    {
        $trip_no = $this->nextReceiptNo('trip');
        $delivery_trip = DeliveryTrip::with('cost', 'waybills', 'vehicle.vehicleDrivers.driver.user')->orderBy('id', 'DESC')->find($delivery_trip_id);
        $waybills_with_pending_wayfare = Waybill::where(['warehouse_id' => $warehouse_id, 'waybill_wayfare_status' => 'pending'])->get();
        return response()->json(compact('delivery_trip', 'waybills_with_pending_wayfare', 'trip_no'), 200);
    }
    public function addWaybillToTrip(Request $request)
    {
        $waybill_id = $request->waybill_id;
        $delivery_trip_id = $request->delivery_trip_id;
        $delivery_trip = DeliveryTrip::find($delivery_trip_id);
        // if($delivery_trip->waybills()->count() == 1 ){
        //     // delete the delivery trip entry
        // }
        // $delivery_trip->waybills()->syncWithoutDetaching($waybill_id);
        $delivery_trip->waybills()->syncWithoutDetaching($waybill_id);
        if ($delivery_trip->vehicle_id !== NULL) {
            $this->createDispatchedWaybill($waybill_id, $delivery_trip->vehicle_id);
        }
        // update waybill wayfare status to pending
        $waybill = Waybill::find($waybill_id);
        $waybill->waybill_wayfare_status = 'given';
        $waybill->save();
        $actor = $this->getUser();
        $title = "Waybill added to trip";
        $description = "Waybill $waybill->waybill_no was added to trip with trip no.: " . $delivery_trip->trip_no . " by $actor->name ($actor->phone)";
        //log this activity
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];

        $this->logUserActivity($title, $description, $roles);
        return $this->showDeliveryTrip($delivery_trip->id, $delivery_trip->warehouse_id);
    }
    public function detachWaybillFromTrip(Request $request)
    {
        $waybill_id = $request->waybill_id;
        $delivery_trip_id = $request->delivery_trip_id;
        $delivery_trip = DeliveryTrip::find($delivery_trip_id);
        // if($delivery_trip->waybills()->count() == 1 ){
        //     // delete the delivery trip entry
        // }
        $waybill = Waybill::find($waybill_id);
        $delivery_trip->waybills()->detach($waybill_id);

        // update waybill wayfare status to pending

        $waybill->dispatcher()->delete();
        $waybill->waybill_wayfare_status = 'pending';
        $waybill->save();
        $actor = $this->getUser();
        $title = "Waybill removed from trip";
        $description = "Waybill $waybill->waybill_no was removed from trip with trip no.: " . $delivery_trip->trip_no . " by $actor->name ($actor->phone)";
        //log this activity
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];

        $this->logUserActivity($title, $description, $roles);
        return $this->showDeliveryTrip($delivery_trip->id, $delivery_trip->warehouse_id);
    }
    private function setVehicleAvailability(Vehicle $vehicle, $status)
    {
        $vehicle->availability = $status;
        $vehicle->save();
    }
    public function changeWaybillStatus(Request $request, Waybill $waybill)
    {
        //
        $item_in_stock_obj = new ItemStockSubBatch();
        $invoice_item_obj = new InvoiceItem();
        $user = $this->getUser();
        $status = $request->status;
        // update waybill status
        $waybill->status = $status;
        $waybill->save();
        $vehicle = ($waybill->dispatcher) ? $waybill->dispatcher->vehicle : '';

        // update invoice items to account for partial supplies and complete ones
        $invoice_item_obj->updateInvoiceItemsForWaybill($waybill->waybillItems);
        // update items in stock based on waybill status
        if ($status === 'in transit') {
            // change vehicle status to 'in transit'
            if ($vehicle) {
                $this->setVehicleAvailability($vehicle, 'in transit');
            }

            $item_in_stock_obj->sendItemInStockForDelivery($waybill->waybillItems);
            // let's update the invoice items for this waybill
        }
        $invoices = $waybill->invoices;
        $title = "Waybill status updated";
        $description = "Waybill ($waybill->waybill_no) status updated to " . strtoupper($waybill->status) . " by $user->name ($user->email)";
        if ($status === 'delivered') {
            // change vehicle status to 'available'
            if ($vehicle) {

                $this->setVehicleAvailability($vehicle, 'available');
            }
            $item_in_stock_obj->confirmItemInStockAsSupplied($waybill->dispatchProducts);
            foreach ($invoices as  $invoice) {

                $invoice->status = $status;
                // check for partial supplies
                $incomplete_invoice_item = $invoice->invoiceItems()->where('supply_status', '=', 'Partial')->first();
                if ($incomplete_invoice_item) {
                    $invoice->status = 'partially supplied';
                }
                $invoice->save();
                //log this action to invoice history
                $this->createInvoiceHistory($invoice, $title, $description);
            }
        }


        //log this activity
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
    }

    // private function removeOldInvoiceItemBatchesAndCreateNewOne($invoice_item, $batches, $old_quantity)
    // {
    //     $batch_ids = [];
    //     foreach ($batches as $invoice_item_batch) {
    //         $item_sub_batch = ItemStockSubBatch::find($invoice_item_batch->item_stock_sub_batch_id);
    //         // remove the old quantity reserved
    //         if ($item_sub_batch->reserved_for_supply >= $old_quantity) {
    //             $item_sub_batch->reserved_for_supply -= $old_quantity;
    //         } else {
    //             $item_sub_batch->reserved_for_supply = 0;
    //         }
    //         $item_sub_batch->save();
    //         $batch_ids[] = $invoice_item_batch->item_stock_sub_batch_id;
    //         // delete the old entry
    //         $invoice_item_batch->delete();
    //     }
    //     // create new one
    //     $this->createInvoiceItemBatches($invoice_item, $batch_ids);
    // }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        //
        $actor = $this->getUser();
        $title = "Invoice deleted";
        $description = "Invoice ($invoice->invoice_number) was deleted by $actor->name ($actor->phone)";
        //log this activity
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);

        // $invoice_items = $invoice->invoiceItems; //()->batches()->delete();
        // foreach ($invoice_items as $invoice_item) {
        //     $invoice_item_batches = $invoice_item->batches;
        //     // we want to unreserve any product for this invoice
        //     foreach ($invoice_item_batches as $invoice_item_batch) {
        //         $quantity = $invoice_item_batch->quantity;
        //         $item_stock_sub_batch = ItemStockSubBatch::find($invoice_item_batch->item_stock_sub_batch_id);
        //         $item_stock_sub_batch->reserved_for_supply -= $quantity;
        //         $item_stock_sub_batch->save();

        //         // we then delete the invoice item batch
        //         $invoice_item_batch->delete();
        //     }
        // }
        $invoice->invoiceItems()->delete();
        $invoice->delete();
        return response()->json(null, 204);
    }
    public function deleteWaybill(Waybill $waybill)
    {
        // delete all relationships with waybill and the waybill itself
        $actor = $this->getUser();
        $title = "Waybill deleted";
        $description = "Waybill ($waybill->waybill_no) was deleted by $actor->name ($actor->phone)";
        //log this activity

        $waybill_items = $waybill->waybillItems;
        foreach ($waybill_items as $waybill_item) {
            $invoice_item = $waybill_item->invoiceItem;
            $invoice_item->quantity_supplied = 0;
            $invoice_item->save();
            $batches = $invoice_item->batches;
            foreach ($batches as $batch) {
                // we want to unreserve all reserved products made as a result of waybill generation
                $item_stock_sub_batch = $batch->itemStockBatch;
                $item_stock_sub_batch->reserved_for_supply -= $batch->to_supply;
                $item_stock_sub_batch->save();
            }
            $invoice_item->batches()->delete();
        }
        $waybill->waybillItems()->delete();
        $invoices = $waybill->invoices;
        foreach ($invoices as $invoice) {
            $invoice->full_waybill_generated = '0';
            $invoice->save();
        }
        $waybill->trips()->delete();
        $waybill->dispatcher()->delete();
        $waybill->delete();
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return response()->json(null, 204);
    }
}
