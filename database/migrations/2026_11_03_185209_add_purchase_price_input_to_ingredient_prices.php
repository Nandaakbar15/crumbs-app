<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_prices', function (Blueprint $table) {
            // Store the original price as typed by user (before normalization to per-base-unit)
            $table->decimal('purchase_price_input', 12, 2)->nullable()->after('price_per_unit');
            // Store the purchase quantity as typed by user (e.g. 250 for "250g")
            $table->decimal('purchase_qty_input', 12, 4)->nullable()->default(1)->after('purchase_price_input');
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_prices', function (Blueprint $table) {
            $table->dropColumn(['purchase_price_input', 'purchase_qty_input']);
        });
    }
};
