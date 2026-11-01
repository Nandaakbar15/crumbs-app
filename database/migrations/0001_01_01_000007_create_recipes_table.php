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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // RCP001, RCP002
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('yield_quantity', 12, 4);
            $table->foreignId('yield_unit_id')->constrained('units');
            $table->integer('estimated_production_time')->nullable(); // in minutes
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
