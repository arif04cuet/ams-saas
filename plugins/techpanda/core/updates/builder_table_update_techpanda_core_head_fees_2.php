<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreHeadFees2 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_head_fees', function($table)
        {
            $table->string('year', 200);
            $table->dropColumn('from_year');
            $table->dropColumn('to_year');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_head_fees', function($table)
        {
            $table->dropColumn('year');
            $table->smallInteger('from_year');
            $table->smallInteger('to_year');
        });
    }
}
