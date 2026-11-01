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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // KD001, KD002, etc
            $table->string('name', 255);
            $table->foreignId('base_unit_id')->constrained('units');
            $table->decimal('current_stock', 12, 4)->default(0);
            $table->foreignId('current_unit_id')->constrained('units');
            $table->decimal('minimum_threshold', 12, 4)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
