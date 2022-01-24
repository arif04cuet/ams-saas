<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1010 extends Migration
{
   public function up()
    {
        Schema::table('backend_users', function($table)
        {
            $table->smallInteger('association_id')->nullable()->change();
        });
    }

    public function down()
    {
       Schema::table('backend_users', function($table)
        {
           
              
        });
    }
}