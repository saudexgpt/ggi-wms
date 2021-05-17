<?php

namespace App;

use App\Models\Setting\CustomerType;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Customer extends Model
{
    //
    use SoftDeletes;
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function type()
    {
        return $this->belongsTo(CustomerType::class, 'customer_type_id', 'id');
    }
}
