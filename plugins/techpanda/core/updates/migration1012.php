<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1012 extends Migration
{
     public function up()
    {
        Schema::table('backend_users', function($table)
        {
            $table->string('fullname_bn')->nullable();
            $table->string('designation')->nullable();
            $table->string('section')->nullable();
            $table->string('work_address')->nullable();
        });
    }

    public function down()
    {
       Schema::table('backend_users', function($table)
        {
            $table->dropColumn('fullname_bn');
            $table->dropColumn('designation');
            $table->dropColumn('section');
            $table->dropColumn('work_address');
              
        });
    }
}