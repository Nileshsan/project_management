<?php

use App\Models\Leave;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL compatible UPDATE with JOIN syntax
        DB::statement('
            UPDATE leaves 
            SET paid = lt.paid,
                updated_at = NOW()
            FROM leave_types lt
            WHERE lt.id = leaves.leave_type_id
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset paid status to default if needed
        DB::statement('UPDATE leaves SET paid = false');
    }
};