<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStockProfitsTableAddUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_profits', function (Blueprint $table) {
            $table->unique(['user_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_profits', function (Blueprint $table) {
            $table->dropForeign('stock_profits_user_id_foreign');
            $table->dropForeign('stock_profits_order_id_foreign');
            $table->dropUnique(['user_id', 'order_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('order_id')->references('id')->on('orders');
        });
    }
}
