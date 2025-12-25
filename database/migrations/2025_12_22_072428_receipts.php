<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')
                ->unique()
                ->constrained('payments')
                ->onDelete('cascade');

            $table->string('receipt_number')->unique(); // e.g., RCP-2024-001

            // Storage details
            $table->string('storage_path'); // S3 Key or internal path
            $table->string('public_url')->nullable(); // Optional public access URL

            $table->timestamp('issued_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
