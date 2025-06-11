<?php

use App\Models\Company;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('order_number')->after('id')->nullable();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->bigInteger('ticket_number')->after('id')->nullable();
        });

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $orders = Order::where('company_id', $company->id)->get();

            foreach ($orders as $key => $order) {
                $order->order_number = $key + 1;
                $order->saveQuietly();
            }

            $tickets = Ticket::where('company_id', $company->id)->get();

            foreach ($tickets as $key => $ticket) {
                $ticket->ticket_number = $key + 1;
                $ticket->saveQuietly();
            }
        }

        // Change invoice_number to bigint in PostgreSQL-compatible way
        if (Schema::hasColumn('invoices', 'invoice_number')) {
            // Use correct regex and escaping for PostgreSQL and PHP
            \DB::statement("UPDATE invoices SET invoice_number = NULL WHERE invoice_number !~ E'^\\d+$'");
            \DB::statement('ALTER TABLE invoices ALTER COLUMN invoice_number TYPE bigint USING invoice_number::bigint');
        }
        // Change estimate_number to bigint in PostgreSQL-compatible way
        if (Schema::hasColumn('estimates', 'estimate_number')) {
            \DB::statement("UPDATE estimates SET estimate_number = NULL WHERE estimate_number !~ E'^\\d+$'");
            \DB::statement('ALTER TABLE estimates ALTER COLUMN estimate_number TYPE bigint USING estimate_number::bigint');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

};
