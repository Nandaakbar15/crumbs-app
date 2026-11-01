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
        Schema::table('recipes', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('name');
            $table->decimal('selling_price', 12, 2)->nullable()->after('yield_unit_id');
            $table->decimal('overhead_per_batch', 12, 2)->default(0)->after('selling_price');
            $table->string('image_url')->nullable()->after('overhead_per_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['category', 'selling_price', 'overhead_per_batch', 'image_url']);
        });
    }
};
