<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pusher_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pusher_settings', 'pusher_app_id')) {
                $table->string('pusher_app_id')->nullable();
            }
            if (!Schema::hasColumn('pusher_settings', 'pusher_app_key')) {
                $table->string('pusher_app_key')->nullable();
            }
            if (!Schema::hasColumn('pusher_settings', 'pusher_app_secret')) {
                $table->string('pusher_app_secret')->nullable();
            }
            if (!Schema::hasColumn('pusher_settings', 'pusher_cluster')) {
                $table->string('pusher_cluster')->nullable();
            }
            if (!Schema::hasColumn('pusher_settings', 'pusher_status')) {
                $table->boolean('pusher_status')->default(false);
            }
            if (!Schema::hasColumn('pusher_settings', 'force_tls')) {
                $table->boolean('force_tls')->default(true);
            }
            if (!Schema::hasColumn('pusher_settings', 'encrypted')) {
                $table->boolean('encrypted')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pusher_settings', function (Blueprint $table) {
            $columns = [
                'pusher_app_id',
                'pusher_app_key',
                'pusher_app_secret',
                'pusher_cluster',
                'pusher_status',
                'force_tls',
                'encrypted',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pusher_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
