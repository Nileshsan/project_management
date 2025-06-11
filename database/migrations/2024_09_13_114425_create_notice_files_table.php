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
        Schema::create('notice_board_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Changed index and foreign key names to be unique
            $table->unsignedInteger('notice_id')->index('nbu_notice_id_index');
            $table->foreign('notice_id', 'nbu_notice_id_foreign')
                ->references('id')
                ->on('notices')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
                
            $table->enum('type', ['employee', 'client'])->default('employee');
            
            // Changed index and foreign key names to be unique
            $table->unsignedInteger('user_id')->index('nbu_user_id_index');
            $table->foreign('user_id', 'nbu_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        Schema::create('notice_files', function (Blueprint $table) {
            $table->increments('id');
            
            $table->unsignedInteger('notice_id')->index('nf_notice_id_index');
            $table->string('filename');
            $table->text('description')->nullable();
            $table->string('google_url')->nullable();
            $table->string('hashname')->nullable();
            $table->string('size')->nullable();
            $table->string('dropbox_link')->nullable();
            $table->string('external_link')->nullable();
            $table->string('external_link_name')->nullable();
            
            $table->unsignedInteger('added_by')->nullable()->index('nf_added_by_index');
            $table->unsignedInteger('last_updated_by')->nullable()->index('nf_last_updated_by_index');
            
            // Add foreign keys with unique names
            $table->foreign('added_by', 'nf_added_by_foreign')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
                
            $table->foreign('last_updated_by', 'nf_last_updated_by_foreign')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
                
            $table->foreign('notice_id', 'nf_notice_id_foreign')
                ->references('id')
                ->on('notices')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice_files');
        Schema::dropIfExists('notice_board_users');
    }
};