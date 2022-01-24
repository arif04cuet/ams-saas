<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreTransactionHead3 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_transaction_head', function($table)
        {
            $table->renameColumn('amount', 'total');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_transaction_head', function($table)
        {
            $table->renameColumn('total', 'amount');
        });
    }
}
