<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['expenses', 'payments', 'invoices'];

        foreach ($tables as $table) {
            // PostgreSQL compatible type casting and rounding
            DB::statement("UPDATE {$table} SET exchange_rate = ROUND((1.0 / NULLIF(exchange_rate, 0))::numeric, 4) WHERE exchange_rate IS NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['expenses', 'payments', 'invoices'];

        foreach ($tables as $table) {
            // Reverse the calculation with proper type casting
            DB::statement("UPDATE {$table} SET exchange_rate = ROUND((1.0 / NULLIF(exchange_rate, 0))::numeric, 4) WHERE exchange_rate IS NOT NULL");
        }
    }
};