<?php

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\LanguageSetting;
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

        if (!Schema::hasColumn('file_storage', 'storage_location')) {
            Schema::table('file_storage', function (Blueprint $table) {
                $table->enum('storage_location', ['local', 'aws_s3', 'digitalocean'])->default('local');
            });
        }

        // REMOVED MySQL-specific CHANGE COLUMN statement for PostgreSQL compatibility
        // If you need to convert storage_location to a PostgreSQL enum, use CREATE TYPE and ALTER TABLE ... TYPE ... USING ...
        // For now, this will remain as a string or native enum as supported by Laravel on PostgreSQL


        if (!Schema::hasColumn('companies', 'show_new_webhook_alert')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->boolean('show_new_webhook_alert')->default(0);
            });

            // Use PostgreSQL-compatible SQL (no backticks)
            DB::statement("UPDATE companies SET show_new_webhook_alert = '1'");
        }


        $this->foreignKeyFixCompaniesTable();
        $this->languageFlags();
        $this->contractNumbers();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('show_new_webhook_alert');
        });
    }

    private function contractNumbers()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->bigInteger('contract_number')->after('id')->nullable();
        });

        Schema::table('contract_templates', function (Blueprint $table) {
            $table->bigInteger('contract_template_number')->after('id')->nullable();
        });


        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $contracts = Contract::where('company_id', $company->id)->get();

            foreach ($contracts as $key => $contract) {
                $contract->contract_number = $key + 1;
                $contract->saveQuietly();
            }

            $templates = ContractTemplate::where('company_id', $company->id)->get();

            foreach ($templates as $key => $template) {
                $template->contract_template_number = $key + 1;
                $template->saveQuietly();
            }
        }
    }

    private function languageFlags()
    {
        LanguageSetting::where('language_code', 'ar')
            ->where('flag_code', '<>', 'sa')
            ->update(['flag_code' => 'sa']); // Saudi Arabia

        LanguageSetting::where('language_code', 'pt-br')
            ->where('flag_code', '<>', 'br')
            ->update(['flag_code' => 'br']); // Brazil

        LanguageSetting::where('language_code', 'fa')
            ->where('flag_code', '<>', 'ir')
            ->update(['flag_code' => 'ir']); // Iraq

        LanguageSetting::whereIn('language_code', ['zh-CN', 'zh-TW'])
            ->where('flag_code', '<>', 'cn')
            ->update(['flag_code' => 'cn']); // China
    }

    private function foreignKeyFixCompaniesTable()
    {
        // PostgreSQL-compatible: drop foreign key if exists, then recreate
        try {
            Schema::table('companies', function (Blueprint $table) {
                // Use try/catch to avoid aborting if the constraint does not exist
                try {
                    $table->dropForeign(['default_task_status']);
                } catch (\Throwable $th) {}
                $table->foreign('default_task_status')
                    ->references('id')->on('taskboard_columns')
                    ->onDelete('set null');
            });
        } catch (\Throwable $th) {
            // Ignore if the constraint does not exist
        }
    }

};
