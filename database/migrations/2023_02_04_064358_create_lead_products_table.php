<?php
<?php

use App\Models\Company;
use App\Models\LeadCustomForm;
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
        // Create lead_custom_fields table first
        Schema::create('lead_custom_fields', function (Blueprint $table) {
            $table->id(); // Uses bigIncrements for PostgreSQL compatibility
            $table->unsignedBigInteger('company_id')->nullable(); // Changed to bigInteger for PostgreSQL
            $table->text('custom_data')->nullable();
            $table->timestamps();

            // Add explicit constraint name for PostgreSQL
            $table->foreign('company_id', 'lead_custom_fields_company_id_foreign')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        // Use transaction to ensure data consistency
        DB::transaction(function () {
            $companies = Company::select('id')->get();

            foreach ($companies as $company) {
                // Check and create Product form
                $leadCustomProductForm = LeadCustomForm::where('company_id', $company->id)
                    ->where('field_name', 'product')
                    ->first();

                if (!$leadCustomProductForm) {
                    LeadCustomForm::create([
                        'field_display_name' => 'Product',
                        'field_name' => 'product',
                        'field_order' => 8,
                        'field_type' => 'select',
                        'company_id' => $company->id,
                    ]);
                }

                // Check and create Source form
                $leadCustomSourceForm = LeadCustomForm::where('company_id', $company->id)
                    ->where('field_name', 'source')
                    ->first();

                if (!$leadCustomSourceForm) {
                    LeadCustomForm::create([
                        'field_display_name' => 'Source',
                        'field_name' => 'source',
                        'field_order' => 9,
                        'field_type' => 'select',
                        'company_id' => $company->id,
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_custom_fields');
    }
};