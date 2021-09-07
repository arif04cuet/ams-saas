<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTechpandaCoreHeadFees extends Migration
{
    public function up()
    {
        Schema::create('techpanda_core_head_fees', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->smallInteger('head_id');
            $table->smallInteger('fee');
            $table->smallInteger('year');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('techpanda_core_head_fees');
    }
}
