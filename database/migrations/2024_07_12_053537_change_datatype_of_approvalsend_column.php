<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First create new column
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('approval_send_new')->default(false);
        });

        // Copy data with proper type casting
        DB::statement('UPDATE tasks SET approval_send_new = (approval_send::text)::boolean');

        // Drop old column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('approval_send');
        });

        // Rename new column to original name
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('approval_send_new', 'approval_send');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create temporary column
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('approval_send_temp')->nullable();
        });

        // Copy data back
        DB::statement("UPDATE tasks SET approval_send_temp = CASE WHEN approval_send THEN '1' ELSE '0' END");

        // Drop boolean column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('approval_send');
        });

        // Create new enum column
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('approval_send', ['0', '1'])->default('0');
        });

        // Copy data to enum
        DB::statement('UPDATE tasks SET approval_send = approval_send_temp');

        // Drop temp column
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('approval_send_temp');
        });
    }
};