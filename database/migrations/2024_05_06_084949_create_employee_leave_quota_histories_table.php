<?php

use App\Models\EmployeeLeaveQuotaHistory;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employee_leave_quota_histories')) {
            return;
        }

        Schema::create('employee_leave_quota_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('leave_type_id');
            
            // Changed index names to be unique
            $table->foreign('user_id', 'elqh_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
                
            $table->foreign('leave_type_id', 'elqh_leave_type_id_foreign')
                ->references('id')
                ->on('leave_types')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
                
            $table->double('no_of_leaves');
            $table->double('leaves_used')->default(0);
            $table->double('leaves_remaining')->default(0);
            $table->date('for_month');
            $table->timestamps();

            // Add regular indexes with unique names
            $table->index('user_id', 'elqh_user_id_index');
            $table->index('leave_type_id', 'elqh_leave_type_id_index');
        });

        // ... rest of your data insertion code remains the same ...
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_quota_histories');
    }
};