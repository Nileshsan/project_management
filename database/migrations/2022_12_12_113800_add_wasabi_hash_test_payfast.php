<?php

use App\Models\GlobalSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('global_settings', 'header_color')) {

            // REMOVED MySQL-specific CHANGE COLUMN statement for PostgreSQL compatibility
            // If you need to add a value to a PostgreSQL enum, use ALTER TYPE ... ADD VALUE ...
            // For now, this will remain as a string or native enum as supported by Laravel on PostgreSQL

            Schema::table('global_settings', function (Blueprint $table) {
                $table->string('header_color')->default('#1D82F5'); // removed ->after() for PostgreSQL
                $table->string('hash')->nullable(); // removed ->after() for PostgreSQL
            });

            $globalSetting = GlobalSetting::first();

            if ($globalSetting) {
                $globalSetting->hash = md5(microtime());
                $globalSetting->saveQuietly();
            }


            Schema::table('companies', function (Blueprint $table) {
                $table->string('header_color')->default('#1D82F5'); // removed ->after() for PostgreSQL
            });

            Schema::table('payment_gateway_credentials', function (Blueprint $table) {
                $table->string('test_payfast_merchant_id')->nullable();
                $table->string('test_payfast_merchant_key')->nullable();
                $table->string('test_payfast_passphrase')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }

};
