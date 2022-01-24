<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreContents extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_contents', function($table)
        {
            $table->integer('category_id');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_contents', function($table)
        {
            $table->dropColumn('category_id');
        });
    }
}
