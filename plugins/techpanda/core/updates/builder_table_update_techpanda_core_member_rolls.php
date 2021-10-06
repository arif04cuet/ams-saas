<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreMemberRolls extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_member_rolls', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_member_rolls', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
}
