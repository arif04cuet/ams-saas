<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreTransactions3 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->renameColumn('offline_branch', 'offline_branch_id');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->renameColumn('offline_branch_id', 'offline_branch');
        });
    }
}
