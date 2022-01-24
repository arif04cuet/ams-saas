<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreAccountHeads3 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_bank_statements', function($table)
        {
            $table->boolean('is_matched')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_bank_statements', function($table)
        {
            $table->dropColumn('is_matched');
        });
    }
    
}