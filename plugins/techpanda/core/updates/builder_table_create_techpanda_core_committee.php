<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTechpandaCoreCommittee extends Migration
{
    public function up()
    {
        Schema::create('techpanda_core_committee', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 200);
            $table->string('code', 100);
            $table->text('body')->nullable();
            $table->boolean('status')->default(1);
            $table->date('valid_from');
            $table->date('valid_to');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('techpanda_core_committee');
    }
}
