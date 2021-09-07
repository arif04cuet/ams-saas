<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreBankStatements extends Migration
{
   
    public function up()
    {
        Schema::table('techpanda_core_account_heads', function($table)
        {
            $table->smallInteger('sort_order')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_account_heads', function($table)
        {
            $table->dropColumn('sort_order');
        });
    }
    
}