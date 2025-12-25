<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'payload',
        'headers',
        'is_processed',
        'processing_result',
    ];
}
