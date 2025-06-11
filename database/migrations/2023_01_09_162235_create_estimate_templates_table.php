<?php

use App\Models\Company;
use App\Models\Contract;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        // estimate_templates table
        Schema::create('estimate_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
            $table->double('sub_total');
            $table->double('total');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('discount_type'); // PostgreSQL: use string, not enum
            $table->double('discount');
            $table->boolean('invoice_convert')->default(false);
            $table->string('status')->default('waiting'); // PostgreSQL: use string, not enum
            $table->text('note')->nullable(); // PostgreSQL: no mediumText
            $table->longText('description')->nullable();
            $table->string('calculate_tax')->default('after_discount'); // PostgreSQL: use string, not enum
            $table->text('client_comment')->nullable();
            $table->boolean('signature_approval')->default(true);
            $table->text('hash')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });

        // estimate_template_items table
        Schema::create('estimate_template_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('estimate_template_id');
            $table->foreign('estimate_template_id')->references('id')->on('estimate_templates')->onDelete('cascade')->onUpdate('cascade');
            $table->string('hsn_sac_code')->nullable();
            $table->string('item_name');
            $table->string('type')->default('item'); // PostgreSQL: use string, not enum
            $table->smallInteger('quantity'); // PostgreSQL: use smallInteger for tinyInteger
            $table->double('unit_price');
            $table->double('amount');
            $table->text('item_summary')->nullable();
            $table->string('taxes')->nullable();
            $table->timestamps();
        });

        // estimate_template_item_images table
        Schema::create('estimate_template_item_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('estimate_template_item_id');
            $table->foreign('estimate_template_item_id')->references('id')->on('estimate_template_items')->onDelete('cascade')->onUpdate('cascade');
            $table->string('filename');
            $table->string('hashname')->nullable();
            $table->string('size')->nullable();
            $table->string('external_link')->nullable();
            $table->timestamps();
        });

        // contracts table: add project_id (no after())
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
        });

        // invoice_settings table: add contract fields (no after())
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->string('contract_prefix')->default('CONT');
            $table->string('contract_number_separator')->default('#');
            $table->unsignedInteger('contract_digit')->default(3);
        });

        // lead_notes table: change details to longText
        Schema::table('lead_notes', function (Blueprint $table) {
            $table->longText('details')->change();
        });

        // client_details table: add company_logo (no after())
        if (!Schema::hasColumn('client_details', 'company_logo')) {
            Schema::table('client_details', function (Blueprint $table) {
                $table->string('company_logo')->nullable();
            });
        }

        // contracts table: drop company_logo if exists
        if (Schema::hasColumn('contracts', 'company_logo')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropColumn('company_logo');
            });
        }

        // invoice_settings table: add status/signatory fields (no after())
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->boolean('show_status')->default(true);
            $table->boolean('authorised_signatory')->default(false);
            $table->string('authorised_signatory_signature')->nullable();
        });

        // SET lat long null for default address (PostgreSQL-compatible)
        DB::statement("UPDATE company_addresses SET latitude=NULL, longitude=NULL WHERE latitude='26.91243360'");

        $this->customFieldsContracts();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_templates');
    }

    private function customFieldsContracts()
    {

        $companies = Company::select('id')->get();
        $customFieldGroup = [];

        foreach ($companies as $company) {
            $customFieldGroup = [
                [
                    'name' => 'Contract',
                    'model' => Contract::CUSTOM_FIELD_MODEL,
                    'company_id' => $company->id
                ]
            ];
        }

        CustomFieldGroup::insert($customFieldGroup);
    }

};
