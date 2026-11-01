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
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 50)->unique(); // BATCH20240115001
            $table->foreignId('recipe_id')->constrained('recipes');
            $table->integer('batch_number'); // jumlah batch yang dibuat
            $table->decimal('produced_quantity', 12, 4);
            $table->foreignId('produced_unit_id')->constrained('units');
            $table->date('production_date');
            $table->text('production_notes')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_batches');
    }
};
