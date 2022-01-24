<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1066 extends Migration
{
    public function up()
    {
         Schema::table('techpanda_core_user_profiles', function($table)
         {
             
             $table->string('official_mobile')->nullable();
             $table->string('roll')->nullable();
             $table->string('nominee_mobile')->nullable();
             
         });
    }

    public function down()
    {
          Schema::table('techpanda_core_user_profiles', function($table)
         {
             
             $table->dropColumn(['official_mobile','roll','nominee_mobile']);
             
         });
    }
}