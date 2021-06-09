<?php

namespace App\Models\Invoice;

use App\Customer;
use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    //
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
