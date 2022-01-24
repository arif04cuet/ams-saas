<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreMemberRolls2 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_member_rolls', function($table)
        {
            $table->boolean('is_superuser')->default(false);
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_member_rolls', function($table)
        {
            $table->dropColumn('is_superuser');
        });
    }
}
