<?php

namespace App\Models\Transfers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequestHistory extends Model
{
    //
    use SoftDeletes;

    public function transferRequest()
    {
        return $this->belongsTo(TransferRequest::class, 'transfer_request_id', 'id');
    }
}
