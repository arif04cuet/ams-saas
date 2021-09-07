<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreBankBranches3 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_bank_branches', function($table)
        {
            $table->renameColumn('bank_name', 'bank_id');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_bank_branches', function($table)
        {
            $table->renameColumn('bank_id', 'bank_name');
        });
    }
}
