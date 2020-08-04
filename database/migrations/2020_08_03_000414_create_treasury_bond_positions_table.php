<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreasuryBondPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treasury_bond_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treasury_bond_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->float('amount');
            $table->float('contributed_amount');
            $table->timestamps();

            $table->unique(['user_id', 'treasury_bond_id', 'date']);
            $table->index(['user_id', 'treasury_bond_id', 'updated_at'], 'treasury_bond_positions_user_id_bond_id_updated_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('treasury_bond_positions');
    }
}
