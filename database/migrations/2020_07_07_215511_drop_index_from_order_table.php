<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropIndexFromOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_date_index');
            $table->dropIndex('orders_user_id_type_index');
            $table->dropIndex('orders_user_id_stock_id_date_index');
            $table->dropIndex('orders_user_id_stock_id_date_type_index');

            $table->index(['user_id', 'stock_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_stock_id_index');

            $table->index(['user_id', 'stock_id', 'date', 'type']);
            $table->index(['user_id', 'stock_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type']);
        });
    }
}
