<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('gstin', 15)->nullable();
            $table->string('pan', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('currency', 5)->default('INR');
            $table->string('timezone')->default('Asia/Kolkata');
            $table->string('invoice_prefix', 10)->default('INV');
            $table->integer('invoice_next_number')->default(1);
            $table->text('terms')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('payee_name')->nullable();
            $table->text('bank_account')->nullable(); // encrypted
            $table->string('ifsc', 15)->nullable();
            $table->boolean('show_qr_on_invoice')->default(true);
            $table->boolean('installed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
