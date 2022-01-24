<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreTransactions extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->integer('user_id')->unsigned();
            $table->dateTime('tnx_date');
            $table->string('tnx_id', 100)->nullable();
            $table->string('status', 100)->default('unpaid');
            $table->integer('bank_statement_id')->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->boolean('is_online')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_transactions', function($table)
        {
            $table->dropColumn('user_id');
            $table->dropColumn('tnx_date');
            $table->dropColumn('tnx_id');
            $table->dropColumn('status');
            $table->dropColumn('bank_statement_id');
            $table->dropColumn('approval_date');
            $table->dropColumn('is_online');
        });
    }
}
