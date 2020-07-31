<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('index_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('index_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->float('value', 10, 8)->nullable();
            $table->timestamps();

            $table->unique(['index_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('index_values');
    }
}
