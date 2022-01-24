<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreBankBranches extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_bank_branches', function($table)
        {
            $table->string('bank_name', 100)->nullable();
            $table->integer('association_id');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_bank_branches', function($table)
        {
            $table->dropColumn('bank_name');
            $table->dropColumn('association_id');
        });
    }
}
