<?php

use App\Models\Company;
use App\Models\QuickBooksSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quick_books_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable(); // PostgreSQL-compatible
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('sandbox_client_id');
            $table->string('sandbox_client_secret');
            $table->string('client_id');
            $table->string('client_secret');
            $table->string('access_token');
            $table->string('refresh_token');
            $table->string('realmid');
            $table->string('sync_type')->default('one_way'); // Use string instead of enum for PostgreSQL
            $table->string('environment')->default('Production'); // Use string instead of enum for PostgreSQL
            $table->boolean('status');
            $table->timestamps();
        });


        Schema::table('invoices', function (Blueprint $table) {
            $table->bigInteger('quickbooks_invoice_id')->nullable(); // PostgreSQL-compatible
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->bigInteger('quickbooks_payment_id')->nullable(); // PostgreSQL-compatible
        });

        Schema::table('client_details', function (Blueprint $table) {
            $table->bigInteger('quickbooks_client_id')->nullable(); // PostgreSQL-compatible
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->integer('datatable_row_limit')->default(10); // removed ->after() for PostgreSQL
        });

        Schema::table('global_settings', function (Blueprint $table) {
            $table->integer('datatable_row_limit')->default(10); // removed ->after() for PostgreSQL
        });

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            QuickBooksSetting::create(['status' => 0, 'company_id' => $company->id]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('quickbooks_invoice_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('quickbooks_payment_id');
        });

        Schema::table('client_details', function (Blueprint $table) {
            $table->dropColumn('quickbooks_client_id');
        });

        Schema::dropIfExists('quick_books_settings');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('datatable_row_limit');
        });
    }

};
