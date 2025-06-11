<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create new column with different name
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status_new')->nullable();
        });

        // Copy data to new column
        DB::statement("UPDATE invoices SET status_new = status");

        // Drop old column
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Rename new column to original name
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        // Add check constraint for enum values
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT invoices_status_check 
            CHECK (status IN ('paid', 'unpaid', 'partial', 'canceled', 'draft', 'pending-confirmation'))");

        // Set default value and not null constraint
        DB::statement("ALTER TABLE invoices ALTER COLUMN status SET DEFAULT 'unpaid'");
        DB::statement("ALTER TABLE invoices ALTER COLUMN status SET NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove check constraint
        DB::statement('ALTER TABLE invoices DROP CONSTRAINT invoices_status_check');

        // Remove not null and default constraints
        DB::statement('ALTER TABLE invoices ALTER COLUMN status DROP NOT NULL');
        DB::statement('ALTER TABLE invoices ALTER COLUMN status DROP DEFAULT');
    }
};