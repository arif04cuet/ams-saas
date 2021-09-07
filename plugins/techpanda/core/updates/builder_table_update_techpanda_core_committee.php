<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreCommittee extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_committee', function($table)
        {
            $table->text('members')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_committee', function($table)
        {
            $table->dropColumn('members');
        });
    }
}
