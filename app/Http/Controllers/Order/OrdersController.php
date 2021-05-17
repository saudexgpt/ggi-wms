<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Order\OrderHistory;
use App\Models\Order\OrderPayment;
use App\Models\Order\OrderStatus;
use App\Models\Order\OrderItem;
use App\Models\Order\DispatchedOrder;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $user = $this->getUser();
        $warehouse_id = $request->warehouse_id;
        $orders = Order::with(['warehouse', 'customer.user', 'customer.type', 'orderItems.item','histories'=>function($q) {
                $q->orderBy('id','DESC');
            },
        'currency'])->where('warehouse_id', $warehouse_id)->get();
        if (isset($request->status) && $request->status != '') {
            ////// query by status //////////////
            $status = $request->status;
            $orders = Order::with(['warehouse', 'customer.user', 'customer.type', 'orderItems.item', 'histories' => function ($q) {
                    $q->orderBy('id', 'DESC');
                },
            'currency'])->where(['warehouse_id'=>$warehouse_id, 'order_status'=>$status])->get();
        }
        return response()->json(compact('orders'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignOrderToWarehouse(Request $request, Order $order)
    {

        $warehouse_id = $request->warehouse_id;
        $order->warehouse_id = $warehouse_id;
        $order->save();

        return $this->show($order);
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
        $order_items = $request->order_items;
        $order = new Order();
        $order->warehouse_id        = $request->warehouse_id;
        $order->customer_id         = $request->customer_id;
        $order->currency_id         = $request->currency_id;
        $order->order_number        = $this->nextInvoiceNo();
        $order->order_status        = $request->order_status;
        $order->ordered_at          = date('Y-m-d H:i:s', strtotime($request->ordered_at));
        $order->subtotal            = $request->subtotal;
        $order->discount            = $request->discount;
        $order->tax                 = $request->tax;
        $order->amount              = $request->amount;
        $order->notes               = $request->notes;
        $order->save();
        $description = "New $order->order_status order ($order->order_number) created by: $user->name ($user->email)";
        //log this action to order history
        $this->createOrderHistory($order, $description);
        //create items ordered for
        $this->createOrderItems($order, $order_items);
        //////update next invoice number/////
        $this->incrementInvoiceNo();
        return $this->show($order);
    }

    private function createOrderHistory($order, $description)
    {
        $order_history = new OrderHistory();
        $order_history->order_id = $order->id;
        $order_history->status_code = $order->order_status;
        $order_history->description = $description;
        $order_history->save();

    }

    private function createOrderItems($order, $order_items)
    {
        foreach ($order_items as $order_item) {

            $order_item_obj = new OrderItem();
            $order_item_obj->order_id = $order->id;
            $order_item_obj->item_id = $order_item['item_id'];
            $order_item_obj->quantity = $order_item['quantity'];
            $order_item_obj->price = $order_item['price'];
            $order_item_obj->total = $order_item['total'];
            $order_item_obj->tax = $order_item['tax'];
            $order_item_obj->save();
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
        $order =  $order->with(['warehouse', 'customer.user', 'customer.type', 'orderItems.item', 'histories' => function ($q) {
                $q->orderBy('id', 'DESC');
            },
        'currency'])->find($order->id);
        return response()->json(compact('order'), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function changeOrderStaus(Request $request, Order $order)
    {
        //
        $user = $this->getUser();
        $status = $request->status;
        $order->order_status = $status;
        $order->save();
        $description = "Order ($order->order_number) status changed to ".strtoupper($order->order_status)." by: $user->name ($user->email)";
        //log this action to order history
        $this->createOrderHistory($order, $description);
        return $this->show($order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
