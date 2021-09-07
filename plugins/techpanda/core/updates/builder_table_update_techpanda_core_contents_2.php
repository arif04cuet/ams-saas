<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreContents2 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_contents', function($table)
        {
            $table->integer('association_id');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_contents', function($table)
        {
            $table->dropColumn('association_id');
        });
    }
}
