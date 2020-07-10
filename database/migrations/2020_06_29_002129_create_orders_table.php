<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('type');
            $table->integer('quantity');
            $table->float('price');
            $table->float('cost');
            $table->float('average_price');
            $table->timestamps();

            $table->index(['user_id', 'stock_id', 'date', 'type']);
            $table->index(['user_id', 'stock_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
