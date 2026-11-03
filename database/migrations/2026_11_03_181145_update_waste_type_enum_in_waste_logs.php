<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update enum to support both waste and loss types
        DB::statement("ALTER TABLE waste_logs MODIFY COLUMN waste_type ENUM('waste','loss','spill','expired','damaged','miscalculation','other') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE waste_logs MODIFY COLUMN waste_type ENUM('spill','expired','damaged','miscalculation','other') NOT NULL");
    }
};
