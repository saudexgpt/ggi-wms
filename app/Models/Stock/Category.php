<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
