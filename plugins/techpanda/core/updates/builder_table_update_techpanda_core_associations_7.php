<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreAssociations7 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_associations', function($table)
        {
            $table->integer('bank_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_associations', function($table)
        {
            $table->dropColumn('bank_id');
        });
    }
}
