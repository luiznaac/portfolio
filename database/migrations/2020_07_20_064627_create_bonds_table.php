<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBondsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bond_issuer_id')->constrained()->onDelete('cascade');
            $table->foreignId('bond_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('index_id')->nullable()->constrained()->onDelete('cascade');
            $table->float('index_rate')->nullable();
            $table->float('interest_rate')->nullable();
            $table->date('maturity_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonds');
    }
}
