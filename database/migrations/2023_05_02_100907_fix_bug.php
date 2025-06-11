<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // PostgreSQL compatible way to modify the gender column
        if (Schema::hasColumn('users', 'gender')) {
            // Create new column
            Schema::table('users', function (Blueprint $table) {
                $table->string('gender_new')->nullable()->default('male');
            });

            // Copy data from old column to new
            DB::statement("UPDATE users SET gender_new = gender");

            // Drop old column
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('gender');
            });

            // Rename new column to original name
            DB::statement('ALTER TABLE users RENAME COLUMN gender_new TO gender');

            // Add check constraint to enforce enum values
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_gender_check CHECK (gender IN ('male', 'female', 'others'))");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the check constraint
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_gender_check');
        });
    }
};