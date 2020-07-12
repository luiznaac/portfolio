<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockDividendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->date('date_paid');
            $table->date('reference_date');
            $table->float('value', 10, 8);
            $table->timestamps();

            $table->unique(['stock_id', 'type', 'date_paid', 'reference_date']);
            $table->index(['stock_id', 'date_paid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_dividends');
    }
}
