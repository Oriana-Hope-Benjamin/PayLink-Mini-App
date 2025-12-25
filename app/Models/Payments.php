<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 


class Payments extends Model
{
    protected $table = 'payments';

   protected $fillable = [
        'internal_reference',
        'external_txn_id',
        'payer_name',
        'payer_phone',
        'payer_email',
        'currency',
        'amount',
        'description',
        'status',

    ];

    /**
     * The "booted" method of the model.
     * Automatically generates a UUID for 'public_ref' when creating a payment.
     */
    protected static function booted()
    {
        static::creating(function ($payment) {
            if (empty($payment->internal_reference)) {
                $payment->internal_reference = (string) Str::uuid();
            }
        });
    }
}
