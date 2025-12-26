<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptModel extends Model
{
    protected $table = 'receipts';

    protected $fillable = [
        'payment_id',
        'receipt_number',
        'storage_path',
        'public_url',
        'issued_at',
    ];
}
