<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1015 extends Migration
{
    public function up()
    {
         Schema::table('techpanda_core_user_profiles', function($table)
         {
             
             $table->integer('user_id')->unsigned();
             
             $table->foreign('user_id')->references('id')->on('backend_users');
             
         });
    }

    public function down()
    {
        // Schema::drop('techpanda_core_table');
        
         Schema::table('techpanda_core_user_profiles', function($table)
         {
             
             $table->dropForeign('user_id');
             $table->dropColumn('user_id');
             
         });
         
         
       
    }
}