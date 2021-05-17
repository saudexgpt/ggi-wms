<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;
use App\Models\Setting\Tax;

use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{

    use SoftDeletes;
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function stocks()
    {
        return $this->hasMany(ItemStockSubBatch::class);
    }
    public function taxes()
    {
        return $this->belongsToMany(Tax::class)
            ->using(ItemTax::class)
            ->withTimestamps();
    }
    public function price()
    {
        return $this->hasOne(ItemPrice::class);
    }
    public function returnedProducts()
    {
        return $this->hasMany(ReturnedProduct::class);
    }
}
