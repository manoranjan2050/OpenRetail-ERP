<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('name');
            $table->enum('customer_type', ['retail', 'wholesale', 'distributor', 'vip'])->default('retail')->after('photo');
            $table->string('gstin', 15)->nullable()->after('customer_type');
        });

        // Change billing_cycle enum to include yearly
        DB::statement("ALTER TABLE customers MODIFY billing_cycle ENUM('none','weekly','monthly','yearly') NOT NULL DEFAULT 'none'");
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['photo', 'customer_type', 'gstin']);
        });
        DB::statement("ALTER TABLE customers MODIFY billing_cycle ENUM('none','weekly','monthly') NOT NULL DEFAULT 'none'");
    }
};
