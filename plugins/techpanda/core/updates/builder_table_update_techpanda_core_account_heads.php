<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreAccountHeads extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_account_heads', function($table)
        {
            $table->string('code', 100);
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_account_heads', function($table)
        {
            $table->dropColumn('code');
        });
    }
}
