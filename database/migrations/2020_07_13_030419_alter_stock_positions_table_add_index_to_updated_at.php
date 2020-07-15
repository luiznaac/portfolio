<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStockPositionsTableAddIndexToUpdatedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_positions', function (Blueprint $table) {
            $table->index(['user_id', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_positions', function (Blueprint $table) {
            $table->dropIndex('stock_positions_user_id_updated_at_index');
        });
    }
}
