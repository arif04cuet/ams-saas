<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration106 extends Migration
{
    public function up()
    {
        Schema::table('backend_users', function($table)
        {
            $table->smallInteger('association_id')->unsigned();
            $table->string('mobile')->nullable();
            $table->string('cadre')->nullable();
        });
    }

    public function down()
    {
       Schema::table('backend_users', function($table)
        {
            $table->dropColumn('association_id');
            $table->dropColumn('mobile');
            $table->dropColumn('cadre');
              
        });
    }
}