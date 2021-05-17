<?php

namespace App\Models\Invoice;

use App\Laravue\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class DeliveryTripExpense extends Model
{
    //
    use SoftDeletes;
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
    public function deliveryTrip()
    {
        return $this->belongsTo(DeliveryTrip::class);
    }
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id');
    }
}
