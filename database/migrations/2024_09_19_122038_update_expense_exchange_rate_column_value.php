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
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->change();
        });

        // PostgreSQL compatible UPDATE with JOIN syntax
        DB::statement('
            UPDATE expenses 
            SET exchange_rate = c.exchange_rate
            FROM currencies c
            WHERE c.id = expenses.currency_id
        ');

        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('dedicated_subdomain')->nullable()->after('currency_key_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable(false)->change();
        });

        DB::statement('UPDATE expenses SET exchange_rate = NULL');

        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('dedicated_subdomain');
        });
    }
};