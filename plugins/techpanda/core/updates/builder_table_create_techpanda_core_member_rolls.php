<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTechpandaCoreMemberRolls extends Migration
{
    public function up()
    {
        Schema::create('techpanda_core_member_rolls', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 200);
            $table->string('roll', 100);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('techpanda_core_member_rolls');
    }
}
