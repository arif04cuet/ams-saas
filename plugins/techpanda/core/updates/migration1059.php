<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1059 extends Migration
{
   
     public function up()
    {
        Schema::table('backend_users', function($table)
        {
            $table->text('initial_balance')->nullable();
        });
    }

    public function down()
    {
       Schema::table('backend_users', function($table)
        {
            $table->dropColumn('initial_balance');
              
        });
    }
    
    
}