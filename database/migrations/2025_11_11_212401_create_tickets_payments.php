<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    // Pastikan namanya 'ticket_payments' (BUKAN 'tickets_payments')
    Schema::create('ticket_payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ticket_id')->constrained('tickets');
        $table->string('qrcode');
        $table->dateTime('book_date');
        $table->dateTime('paid_date')->nullable();
        $table->string('status')->default('process');
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nama tabel di sini juga diubah
        Schema::dropIfExists('ticket_payments');
    }
};