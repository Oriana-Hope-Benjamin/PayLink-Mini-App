<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); 
            $table->uuid('internal_reference')->unique();
            
            // External ID from MoMo provider (e.g., MTN's resourceId). 
            $table->string('external_txn_id')->nullable()->unique(); 
            
            $table->string('payer_name');
            $table->string('payer_phone', 20); // E.164 format
            $table->string('payer_email')->nullable();
            $table->string('currency', 3); // ISO code: UGX, KES, USD
            $table->unsignedBigInteger('amount'); // Stored in lowest unit (e.g. cents)
            $table->string('description')->nullable();
            
            // State management
            $table->enum('status', ['PENDING', 'PROCESSING', 'SUCCESS', 'FAILED'])->default('PENDING');
             
            $table->timestamps();

            // Indexes for faster searching
            $table->index('status');
            $table->index('payer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};