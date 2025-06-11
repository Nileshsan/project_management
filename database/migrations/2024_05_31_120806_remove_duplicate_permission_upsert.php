<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL compatible DELETE syntax
        DB::statement('
            DELETE FROM user_permissions p1
            USING user_permissions p2
            WHERE
                p1.id > p2.id 
                AND p1.permission_id = p2.permission_id 
                AND p1.user_id = p2.user_id
        ');

        // Add unique constraint with explicit name
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->unique(
                ['permission_id', 'user_id'],
                'user_permissions_permission_user_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropUnique('user_permissions_permission_user_unique');
        });
    }
};