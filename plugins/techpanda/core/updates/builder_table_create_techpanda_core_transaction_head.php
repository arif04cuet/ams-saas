<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTechpandaCoreTransactionHead extends Migration
{
    public function up()
    {
        Schema::create('techpanda_core_transaction_head', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('transaction_id');
            $table->integer('head_id');
            $table->integer('quantity');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('techpanda_core_transaction_head');
    }
}
