<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreAssociations6 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_associations', function($table)
        {
            $table->text('sms_schedule')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_associations', function($table)
        {
            $table->dropColumn('sms_schedule');
        });
    }
}
