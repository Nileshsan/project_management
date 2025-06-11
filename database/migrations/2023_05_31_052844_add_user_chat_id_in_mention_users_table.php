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
        Schema::table('mention_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_chat_id')->nullable();
            $table->foreign('user_chat_id')
                ->references('id')
                ->on('users_chat')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        // PostgreSQL compatible way to modify the storage_location column
        if (Schema::hasColumn('file_storage', 'storage_location')) {
            // Create new column
            Schema::table('file_storage', function (Blueprint $table) {
                $table->string('storage_location_new')->default('local')->nullable(false);
            });

            // Copy data
            DB::statement("UPDATE file_storage SET storage_location_new = storage_location");

            // Drop old column
            Schema::table('file_storage', function (Blueprint $table) {
                $table->dropColumn('storage_location');
            });

            // Rename new column
            DB::statement('ALTER TABLE file_storage RENAME COLUMN storage_location_new TO storage_location');

            // Add check constraint
            DB::statement("ALTER TABLE file_storage ADD CONSTRAINT storage_location_check 
                CHECK (storage_location IN ('local', 'aws_s3', 'digitalocean', 'wasabi', 'minio'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mention_users', function (Blueprint $table) {
            $table->dropForeign(['user_chat_id']);
            $table->dropColumn('user_chat_id');
        });

        // Remove check constraint if exists
        DB::statement('ALTER TABLE file_storage DROP CONSTRAINT IF EXISTS storage_location_check');
    }
};