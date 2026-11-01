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
        Schema::table('production_batches', function (Blueprint $table) {
            $table->decimal('quantity_sold', 12, 4)->default(0)->after('produced_quantity');
            $table->decimal('revenue', 15, 2)->default(0)->after('quantity_sold');
            $table->dateTime('recorded_date')->nullable()->after('revenue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_batches', function (Blueprint $table) {
            $table->dropColumn(['quantity_sold', 'revenue', 'recorded_date']);
        });
    }
};
