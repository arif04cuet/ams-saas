<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreAssociations4 extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_associations', function($table)
        {
            $table->boolean('is_enable_sms')->default(0);
            $table->string('sms_gateway', 200)->nullable();
            $table->string('sms_username', 200)->nullable();
            $table->string('sms_password', 200)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_associations', function($table)
        {
            $table->dropColumn('is_enable_sms');
            $table->dropColumn('sms_gateway');
            $table->dropColumn('sms_username');
            $table->dropColumn('sms_password');
        });
    }
}
