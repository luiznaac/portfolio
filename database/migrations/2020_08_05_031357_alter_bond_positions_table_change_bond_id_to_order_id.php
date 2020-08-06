<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBondPositionsTableChangeBondIdToOrderId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bond_positions', function (Blueprint $table) {
            $table->dropForeign('bond_positions_bond_id_foreign');
            $table->dropIndex('bond_positions_user_id_bond_id_updated_at_index');
            $table->dropForeign('bond_positions_user_id_foreign');
            $table->dropUnique('bond_positions_user_id_bond_id_date_unique');

            $table->dropColumn('bond_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreignId('bond_order_id')->constrained()->onDelete('cascade');

            $table->unique(['user_id', 'bond_order_id', 'date']);
            $table->index(['user_id', 'bond_order_id', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bond_positions', function (Blueprint $table) {
            $table->dropForeign('bond_positions_bond_order_id_foreign');
            $table->dropIndex('bond_positions_user_id_bond_order_id_updated_at_index');
            $table->dropForeign('bond_positions_user_id_foreign');
            $table->dropUnique('bond_positions_user_id_bond_order_id_date_unique');

            $table->dropColumn('bond_order_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreignId('bond_id')->constrained()->onDelete('cascade');

            $table->unique(['user_id', 'bond_id', 'date']);
            $table->index(['user_id', 'bond_id', 'updated_at']);
        });
    }
}
