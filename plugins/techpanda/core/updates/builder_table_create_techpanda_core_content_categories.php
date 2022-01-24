<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTechpandaCoreContentCategories extends Migration
{
    public function up()
    {
        Schema::create('techpanda_core_content_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title', 250);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('techpanda_core_content_categories');
    }
}
