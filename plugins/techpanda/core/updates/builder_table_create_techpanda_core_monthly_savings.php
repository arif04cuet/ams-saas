<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTechpandaCoreMonthlySavings extends Migration
{
    public function up()
    {
        Schema::create('techpanda_core_monthly_savings', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('transaction_id');
            $table->integer('user_id');
            $table->string('month', 100);
            $table->integer('year');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('techpanda_core_monthly_savings');
    }
}
