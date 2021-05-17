<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceHistory extends Model
{
    //
    use SoftDeletes;

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
