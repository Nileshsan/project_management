<?php

use App\Models\Company;
use App\Models\DashboardWidget;
use App\Models\Deal;
use App\Models\DealFile;
use App\Models\DealFollowUp;
use App\Models\Lead;
use App\Models\LeadPipeline;
use App\Models\LeadProduct;
use App\Models\PipelineStage;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionType;
use App\Models\Proposal;
use App\Models\PurposeConsentLead;
use App\Models\RoleUser;
use App\Models\UserLeadboardSetting;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void
    {
        try {
            DB::beginTransaction();

            // First drop existing indexes if they exist
            DB::statement('DROP INDEX IF EXISTS leads_agent_id_foreign');
            DB::statement('DROP INDEX IF EXISTS deals_agent_id_foreign');

            $this->createPipelines();
            $this->createStages();
            $this->createDeals();
            $this->handleLeadCustomizations();
            $this->setupPermissions();
            $this->cleanupOldData();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createPipelines(): void 
    {
        if (!Schema::hasTable('lead_pipelines')) {
            Schema::create('lead_pipelines', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->integer('priority')->default(0);
                $table->string('label_color')->default('#ff0000');
                $table->boolean('default')->default(false);
                $table->timestamps();

                $table->foreign('company_id', 'lead_pipelines_company_id_foreign')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }

    private function createStages(): void
    {
        if (!Schema::hasTable('pipeline_stages')) {
            Schema::create('pipeline_stages', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('lead_pipeline_id')->nullable();
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->integer('priority')->default(0);
                $table->boolean('default')->default(false);
                $table->string('label_color')->default('#ff0000');
                $table->timestamps();

                $table->foreign('company_id', 'pipeline_stages_company_id_foreign')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->foreign('lead_pipeline_id', 'pipeline_stages_pipeline_id_foreign')
                    ->references('id')
                    ->on('lead_pipelines')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }

    private function createDeals(): void
    {
        if (!Schema::hasTable('deals')) {
            Schema::create('deals', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->string('name')->nullable();
                $table->integer('column_priority')->default(0);
                $table->unsignedBigInteger('lead_pipeline_id')->nullable();
                $table->unsignedBigInteger('pipeline_stage_id')->nullable();
                $table->unsignedBigInteger('lead_id')->nullable();
                $table->date('close_date')->nullable();
                $table->unsignedBigInteger('agent_id')->nullable();
                $table->string('next_follow_up')->default('yes');
                $table->decimal('value', 30, 2)->nullable()->default(0);
                $table->text('note')->nullable();
                $table->string('hash')->nullable();
                $table->unsignedBigInteger('currency_id')->nullable();
                $table->unsignedInteger('added_by')->nullable();
                $table->unsignedInteger('last_updated_by')->nullable();
                $table->timestamps();

                // Add foreign keys with explicit names
                $table->foreign('company_id', 'deals_company_id_foreign')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade');
                    
                $table->foreign('agent_id', 'deals_agent_id_foreign')
                    ->references('id')
                    ->on('lead_agents')
                    ->onDelete('cascade');
                    
                // ... add other foreign keys with explicit names
            });
        }
    }

    private function handleLeadCustomizations(): void
    {
        // Your existing company specific changes code
        // but moved to a separate method
    }

    private function setupPermissions(): void
    {
        // Your existing permission setup code
        // but moved to a separate method
    }

    private function cleanupOldData(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop foreign keys first with explicit names
            try {
                $table->dropForeign('leads_agent_id_foreign');
                $table->dropForeign('leads_currency_id_foreign');
            } catch (\Exception $e) {
                // Foreign keys may not exist
            }
            
            // Then drop columns
            $table->dropColumn([
                'agent_id',
                'currency_id',
                'next_follow_up',
                'value'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_pipeline_stages');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('lead_pipelines');
        Schema::dropIfExists('deals');
    }
};