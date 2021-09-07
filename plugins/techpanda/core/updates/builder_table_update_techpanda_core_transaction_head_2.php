<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreTransactionHead2 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_transaction_head', function($table)
        {
            $table->decimal('amount', 10, 2);
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_transaction_head', function($table)
        {
            $table->dropColumn('amount');
        });
    }
}
