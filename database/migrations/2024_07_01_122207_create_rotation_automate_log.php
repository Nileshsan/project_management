<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rotation_automate_log', function (Blueprint $table) {
            $table->increments('id');
            
            // Company relationship
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id', 'ral_company_id_foreign')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            
            // User relationship with unique index name
            $table->unsignedInteger('user_id')->index('ral_user_id_index');
            $table->foreign('user_id', 'ral_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            
            // Employee shift rotation relationship
            $table->unsignedInteger('employee_shift_rotation_id')->nullable();
            $table->foreign('employee_shift_rotation_id', 'ral_shift_rotation_id_foreign')
                ->references('id')
                ->on('employee_shift_rotations')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            
            // Employee shift relationship
            $table->unsignedBigInteger('employee_shift_id')->nullable();
            $table->foreign('employee_shift_id', 'ral_shift_id_foreign')
                ->references('id')
                ->on('employee_shifts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            
            $table->integer('sequence')->nullable();
            $table->date('cron_run_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rotation_automate_log');
    }
};