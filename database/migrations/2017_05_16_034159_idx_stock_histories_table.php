<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IdxStockHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('idx_stock_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('emiten_code');
            $table->date('date');
      			$table->bigInteger('high');
      			$table->bigInteger('low');
      			$table->bigInteger('close');
      			$table->bigInteger('open');
      			$table->bigInteger('volume');
      			$table->bigInteger('frequency');
      			$table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
		Schema::drop('idx_stock_histories');
    }
}
