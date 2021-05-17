<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\Item;
use App\Models\Stock\ItemPrice;
use App\Models\Stock\ItemTax;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $items = Item::with(['category', 'stocks', 'taxes', 'price'])->orderBy('name')->get();

        return response()->json(compact('items'));
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stock\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {

        $item = $item->with(['category', 'stocks', 'taxes', 'price'])->find($item->id);
        // $item->currency_id = $item->price->currency_id;
        // $item->purchase_price = $item->price->purchase_price;
        // $item->sale_price = $item->price->sale_price;
        return response()->json(compact('item'), 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request, Item $item)
    {
        //
        $user = $this->getUser();
        $tax_ids =  $request->tax_ids;
        $name = $request->name;
        $category_id = $request->category_id;
        // $sku = $request->sku;
        $package_type = $request->package_type;
        $quantity_per_carton = $request->quantity_per_carton;
        $description = $request->description;
        $picture = $request->picture;
        $item = Item::where('name', $name)->first();

        if (!$item) {
            $item = new Item();
            $item->name = $name;
            $item->package_type = $package_type;
            $item->quantity_per_carton = $quantity_per_carton;
            $item->category_id = $category_id;
            // $item->sku = $sku;
            $item->description = $description;
            $item->picture = $picture;
            $item->save();

            // save item taxes
            if ($tax_ids) {
                foreach ($tax_ids  as $tax_id) {
                    $item_tax = new ItemTax();
                    $item_tax->tax_id = $tax_id;
                    $item_tax->item_id = $item->id;
                    $item_tax->save();
                }
            }
            //save item price
            $item_price = new ItemPrice();
            $item_price->item_id = $item->id;
            $item_price->currency_id = $request->currency_id;
            $item_price->sale_price = $request->sale_price;
            // $item_price->purchase_price = $request->purchase_price;
            $item_price->save();
            // log this action
            $title = "Product Added";
            $description = $name . " added to list of products by " . $user->name;;
            $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
            $this->logUserActivity($title, $description, $roles);
            return $this->show($item);
        }
        return response()->json(['message' => 'Duplicate SKU'], 500);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Item\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Item $item)
    {
        //
        $user = $this->getUser();
        $tax_ids =  $request->tax_ids;
        $item->name = $request->name;
        $item->category_id = $request->category_id;
        $item->package_type = $request->package_type;
        $item->quantity_per_carton = $request->quantity_per_carton;
        // $item->sku = $request->sku;
        $item->description = $request->description;
        $item->picture = $request->picture;
        $item->save();

        //update item price
        $item_price = ItemPrice::where('item_id', $item->id)->first();
        if (!$item_price) {
            $item_price = new ItemPrice();
        }
        $item_price->item_id = $item->id;
        $item_price->currency_id = $request->currency_id;
        $item_price->sale_price = $request->sale_price;
        // $item_price->purchase_price = $request->purchase_price;
        $item_price->save();

        //update item tax
        if ($tax_ids) {
            foreach ($tax_ids  as $tax_id) {
                $item_tax = ItemTax::where(['item_id' => $item->id, 'tax_id' => $tax_id])->first();
                if (!$item_tax) {
                    $item_tax = new ItemTax();
                    $item_tax->tax_id = $tax_id;
                    $item_tax->item_id = $item->id;
                    $item_tax->save();
                }
            }
        }
        $title = "Product details modified";
        $description = "Product information with ID $item->id  was modified by " . $user->name;;
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);
        return $this->show($item);
    }
    public function destroyItemTax(Request $request)
    {
        //
        $tax = Item::find($request->item_id); // ->taxes()->where('tax_id', $request->tax_id)->first();
        $tax->taxes()->detach($request->tax_id);

        //$item_tax->delete();
        return response()->json(null, 204);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        // first log this event
        $user = $this->getUser();
        $title = "Product deleted";
        $description = $item->name . " was removed from list of products by " . $user->name;
        $roles = ['assistant admin', 'warehouse manager', 'warehouse auditor'];
        $this->logUserActivity($title, $description, $roles);

        $item->taxes()->detach(); //use detach for pivoted relationship (hasManyThrough)
        $item->price()->delete();
        $item->delete();
        return response()->json(null, 204);
    }
}
