<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreTransactionHead extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_transaction_head', function($table)
        {
            $table->renameColumn('head_id', 'headfee_id');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_transaction_head', function($table)
        {
            $table->renameColumn('headfee_id', 'head_id');
        });
    }
}
