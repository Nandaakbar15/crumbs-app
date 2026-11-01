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
        Schema::create('hpp_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_batch_id')->nullable()->constrained('production_batches');
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->decimal('total_material_cost', 12, 2);
            $table->decimal('quantity_produced', 12, 4);
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('hpp_per_unit', 12, 2); // cost per unit
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->decimal('gross_margin', 12, 2)->nullable(); // selling_price - hpp_per_unit
            $table->decimal('margin_percentage', 5, 2)->nullable(); // (margin / selling_price) * 100
            $table->text('notes')->nullable();
            $table->date('calculated_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hpp_calculations');
    }
};
