<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBondPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bond_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bond_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->float('amount');
            $table->float('contributed_amount');
            $table->timestamps();

            $table->unique(['user_id', 'bond_id', 'date']);
            $table->index(['user_id', 'bond_id', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bond_positions');
    }
}
