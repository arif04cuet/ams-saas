<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreTransactions6 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->integer('approved_by')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->dropColumn('approved_by');
        });
    }
}
