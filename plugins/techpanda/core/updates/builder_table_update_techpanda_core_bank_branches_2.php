<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreBankBranches2 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_bank_branches', function($table)
        {
            $table->dropColumn('association_id');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_bank_branches', function($table)
        {
            $table->integer('association_id');
        });
    }
}
