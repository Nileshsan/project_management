<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Task;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('start_date')->nullable()->change();
            $table->dateTime('due_date')->nullable()->change();
        });

        // PostgreSQL compatible datetime functions
        DB::statement("
            UPDATE tasks 
            SET start_date = start_date::date + created_at::time,
                due_date = due_date::date + created_at::time
            WHERE created_at IS NOT NULL 
            AND start_date::time = '00:00:00'::time 
            AND due_date::time = '00:00:00'::time
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable()->change();
            $table->date('due_date')->nullable()->change();
        });
    }
};