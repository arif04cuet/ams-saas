<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreBankBranches4 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_bank_branches', function($table)
        {
            $table->text('address')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_bank_branches', function($table)
        {
            $table->dropColumn('address');
        });
    }
}
