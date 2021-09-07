<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreContents3 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_contents', function($table)
        {
            $table->boolean('send_members')->default(false);
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_contents', function($table)
        {
            $table->dropColumn('send_members');
        });
    }
}
