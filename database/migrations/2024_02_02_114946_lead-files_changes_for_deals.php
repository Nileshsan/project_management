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
        try {
            DB::beginTransaction();

            if (Schema::hasTable('lead_files')) {
                // First check and drop foreign key if exists
                $foreignKeys = $this->listTableForeignKeys('lead_files');
                if (in_array('lead_files_lead_id_foreign', $foreignKeys)) {
                    Schema::table('lead_files', function (Blueprint $table) {
                        $table->dropForeign('lead_files_lead_id_foreign');
                    });
                }

                // Create new column before dropping old one
                Schema::table('lead_files', function (Blueprint $table) {
                    $table->unsignedBigInteger('deal_id')->nullable()->after('lead_id');
                });

                // Copy data
                DB::statement('UPDATE lead_files SET deal_id = lead_id');

                // Drop old column
                Schema::table('lead_files', function (Blueprint $table) {
                    $table->dropColumn('lead_id');
                });

                // Rename table
                Schema::rename('lead_files', 'deal_files');

                // Add foreign key with explicit name
                Schema::table('deal_files', function (Blueprint $table) {
                    $table->foreign('deal_id', 'deal_files_deal_id_foreign')
                        ->references('id')
                        ->on('deals')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                });
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::beginTransaction();

            if (Schema::hasTable('deal_files')) {
                // Drop foreign key first
                Schema::table('deal_files', function (Blueprint $table) {
                    $table->dropForeign('deal_files_deal_id_foreign');
                });

                // Create new column
                Schema::table('deal_files', function (Blueprint $table) {
                    $table->unsignedBigInteger('lead_id')->nullable()->after('deal_id');
                });

                // Copy data back
                DB::statement('UPDATE deal_files SET lead_id = deal_id');

                // Drop deal_id column
                Schema::table('deal_files', function (Blueprint $table) {
                    $table->dropColumn('deal_id');
                });

                // Rename table back
                Schema::rename('deal_files', 'lead_files');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get list of foreign keys for a table
     */
    private function listTableForeignKeys(string $table): array
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();
        return array_map(function($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }
};