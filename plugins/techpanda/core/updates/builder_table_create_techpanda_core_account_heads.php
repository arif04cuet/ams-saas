<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTechpandaCoreAccountHeads extends Migration
{
    public function up()
    {
        Schema::create('techpanda_core_account_heads', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 250);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('techpanda_core_account_heads');
    }
}
