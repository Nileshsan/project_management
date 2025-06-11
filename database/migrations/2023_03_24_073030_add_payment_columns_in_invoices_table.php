
<?php

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
        Schema::table('invoices', function (Blueprint $table) {
            // First try to drop the index if it exists
            try {
                DB::statement('DROP INDEX IF EXISTS payments_offline_method_id_foreign');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            if (!Schema::hasColumn('invoices', 'offline_method_id')) {
                $table->unsignedBigInteger('offline_method_id')->nullable();
                
                // Create index with a unique name
                $table->foreign('offline_method_id', 'invoices_offline_method_id_foreign')
                    ->references('id')
                    ->on('offline_payment_methods')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            }

            if (!Schema::hasColumn('invoices', 'transaction_id')) {
                $table->string('transaction_id')->nullable();
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
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['offline_method_id']);
            
            // Then drop the columns
            $table->dropColumn(['offline_method_id', 'transaction_id']);
        });
    }
};