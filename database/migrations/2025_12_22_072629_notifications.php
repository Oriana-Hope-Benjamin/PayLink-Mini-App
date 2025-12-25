<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('receipt_id')
                  ->constrained('receipts')
                  ->onDelete('cascade');
                  
            $table->enum('channel', ['EMAIL', 'WHATSAPP']);
            $table->string('recipient'); // The email address or phone number sent to
            
            // Delivery Status
            $table->enum('status', ['QUEUED', 'SENT', 'FAILED'])->default('QUEUED');
            $table->integer('retry_count')->default(0);
            
            // Captures error messages from SendGrid/Twilio/Meta API for debugging
            $table->text('error_log')->nullable(); 
            
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};