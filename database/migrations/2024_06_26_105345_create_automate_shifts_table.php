<?php

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
        Schema::create('automate_shifts', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Changed index name to be unique
            $table->unsignedInteger('user_id')->index('automate_shifts_user_id_index');
            
            // Changed foreign key name to be unique
            $table->foreign('user_id', 'automate_shifts_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            
            $table->unsignedInteger('employee_shift_rotation_id')->nullable();
            
            // Added explicit foreign key name
            $table->foreign('employee_shift_rotation_id', 'automate_shifts_rotation_id_foreign')
                ->references('id')
                ->on('employee_shift_rotations')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automate_shifts');
    }
};