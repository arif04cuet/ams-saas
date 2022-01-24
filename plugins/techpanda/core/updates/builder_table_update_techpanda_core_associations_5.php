<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreAssociations5 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_associations', function($table)
        {
            $table->string('sms_api_key', 250)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_associations', function($table)
        {
            $table->dropColumn('sms_api_key');
        });
    }
}
