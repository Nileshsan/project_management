<?php

use App\Models\Order;
use App\Models\Company;
use App\Models\UnitType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     */

    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'unit_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->default(null); 
                $table->foreign('unit_id', 'orders_unit_id_foreign')
                    ->references('id')
                    ->on('unit_types')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            $unitData = UnitType::where('company_id', $company->id)->first();

            if ($unitData) {
                Order::where('company_id', $company->id)
                    ->whereNull('unit_id')
                    ->update(['unit_id' => $unitData->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'unit_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign('orders_unit_id_foreign');
                $table->dropColumn('unit_id');
            });
        }
    }

};
