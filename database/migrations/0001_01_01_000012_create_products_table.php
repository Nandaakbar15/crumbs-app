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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // PRD001, PRD002
            $table->string('name', 255);
            $table->foreignId('recipe_id')->nullable()->constrained('recipes');
            $table->decimal('selling_price', 12, 2);
            $table->integer('quantity_per_unit')->default(1);
            $table->foreignId('unit_id')->constrained('units');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
