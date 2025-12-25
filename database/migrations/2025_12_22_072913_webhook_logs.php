<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            
            $table->string('provider'); // e.g., 'MTN_MOMO', 'AIRTEL_MONEY'
            
            // Store the exact payload received. 
            // specific 'json' column type allows for querying inside the JSON if needed.
            $table->json('payload'); 
            
            // Store headers to debug signature verification issues
            $table->json('headers')->nullable(); 
            
            $table->boolean('is_processed')->default(false);
            
            // Helpful to see if this log matched a payment or was an orphan
            $table->string('processing_result')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};