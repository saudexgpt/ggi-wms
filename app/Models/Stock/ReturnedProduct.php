<?php

namespace App\Models\Stock;

use App\Laravue\Models\User;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;

class ReturnedProduct extends Model
{
    //
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function stocker()
    {
        return $this->belongsTo(User::class, 'stocked_by', 'id');
    }
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id');
    }
}
