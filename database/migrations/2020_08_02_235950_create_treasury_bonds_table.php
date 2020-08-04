<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreasuryBondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treasury_bonds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bond_type_id')->default(1)->constrained()->onDelete('cascade');
            $table->foreignId('index_id')->nullable()->constrained()->onDelete('cascade');
            $table->float('index_rate')->default(100)->nullable();
            $table->float('interest_rate')->nullable();
            $table->date('maturity_date');
            $table->timestamps();

            $table->unique(['index_id', 'interest_rate', 'maturity_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('treasury_bonds');
    }
}
