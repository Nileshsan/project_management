<?php

use App\Models\Company;
use App\Models\LeadCustomForm;
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
        // Use transaction to ensure data consistency
        \DB::transaction(function () {
            $companies = Company::select('id')->get();

            foreach ($companies as $company) {
                // Check and create Product form
                $LeadCustomProductForm = LeadCustomForm::where('company_id', $company->id)
                    ->where('field_name', 'product')
                    ->first();

                if (!$LeadCustomProductForm) {
                    LeadCustomForm::create([
                        'field_display_name' => 'Product',
                        'field_name' => 'product',
                        'field_order' => 8,
                        'field_type' => 'select',
                        'company_id' => $company->id,
                    ]);
                }

                // Check and create Source form
                $LeadCustomSourceForm = LeadCustomForm::where('company_id', $company->id)
                    ->where('field_name', 'source')
                    ->first();

                if (!$LeadCustomSourceForm) {
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

        // Create lead_custom_fields table if it doesn't exist
        if (!Schema::hasTable('lead_custom_fields')) {
            Schema::create('lead_custom_fields', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('company_id')->nullable();
                $table->text('custom_data')->nullable();
                $table->timestamps();

                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        } else {
            // Add custom_data column if table exists but column doesn't
            if (!Schema::hasColumn('lead_custom_fields', 'custom_data')) {
                Schema::table('lead_custom_fields', function (Blueprint $table) {
                    $table->text('custom_data')->nullable();
                });
            }
        }
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