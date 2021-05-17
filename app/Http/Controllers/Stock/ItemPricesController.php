<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\Item;
use App\Models\Stock\ItemPrice;
use Illuminate\Http\Request;

class ItemPricesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $item_prices = ItemPrice::with(['item', 'currency'])->get();
        return response()->json(compact('item_prices'));
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
        $item_price = new ItemPrice();
        $item_price->item_id = $request->item_id;
        $item_price->currency_id = $request->currency_id;
        $item_price->sale_price = $request->sale_price;
        $item_price->purchase_price = $request->purchase_price;
        $item_price->save();

        return $this->show($item_price);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stock\ItemPrice  $itemPrice
     * @return \Illuminate\Http\Response
     */
    public function show(ItemPrice $item_price)
    {
        //
        $item_price = $item_price->with(['currency', 'item'])->find($item_price->id);
        return response()->json(compact('item_price'), 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stock\ItemPrice  $item_price
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ItemPrice $item_price)
    {
        //
        $item_price->warehouse_id = $request->warehouse_id;
        $item_price->item_id = $request->item_id;
        $item_price->currency_id = $request->currency_id;
        $item_price->sale_price = $request->sale_price;
        $item_price->purchase_price = $request->purchase_price;
        $item_price->save();

        return $this->show($item_price);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stock\ItemPrice  $item_price
     * @return \Illuminate\Http\Response
     */
    public function destroy(ItemPrice $item_price)
    {
        //
        $item_price->delete();
        return response()->json(null, 204);
    }
}
