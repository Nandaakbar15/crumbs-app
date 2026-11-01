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
        Schema::create('shopping_lists', function (Blueprint $table) {
            $table->id();
            $table->string('shopping_list_code', 50)->unique();
            $table->foreignId('production_batch_id')->constrained('production_batches');
            $table->decimal('total_estimated_cost', 12, 2)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'purchased'])->default('draft');
            $table->date('purchased_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_lists');
    }
};
