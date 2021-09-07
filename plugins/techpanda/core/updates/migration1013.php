<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1013 extends Migration
{
     public function up()
    {
        Schema::table('backend_users', function($table)
        {
            $table->string('office_name')->nullable();
            $table->string('work_post_code')->nullable();
        });
    }

    public function down()
    {
       Schema::table('backend_users', function($table)
        {
            $table->dropColumn('office_name');
            $table->dropColumn('work_post_code');
              
        });
    }
}