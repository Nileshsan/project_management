<?php

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
        // PostgreSQL compatible column type change
        DB::statement('ALTER TABLE expenses ALTER COLUMN price TYPE double precision');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original type if needed
        DB::statement('ALTER TABLE expenses ALTER COLUMN price TYPE numeric(13,2)');
    }
};