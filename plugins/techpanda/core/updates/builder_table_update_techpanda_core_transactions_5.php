<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreTransactions5 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->string('sessionkey', 200)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->dropColumn('sessionkey');
        });
    }
}
