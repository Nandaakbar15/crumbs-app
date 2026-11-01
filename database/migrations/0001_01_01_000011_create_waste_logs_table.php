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
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients');
            $table->foreignId('production_batch_id')->nullable()->constrained('production_batches');
            $table->enum('waste_type', ['spill', 'expired', 'damaged', 'miscalculation', 'other']);
            $table->decimal('quantity', 12, 4);
            $table->foreignId('unit_id')->constrained('units');
            $table->text('reason')->nullable();
            $table->date('recorded_date');
            $table->string('recorded_by', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_logs');
    }
};
