<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreTransactions2 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->string('offline_channel', 100)->nullable();
            $table->integer('offline_branch')->nullable();
            $table->string('offline_atmid', 100)->nullable();
            $table->string('title', 200)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->dropColumn('offline_channel');
            $table->dropColumn('offline_branch');
            $table->dropColumn('offline_atmid');
            $table->string('title', 200)->nullable(false)->change();
        });
    }
}
