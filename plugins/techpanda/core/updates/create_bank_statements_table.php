<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateBankStatementsTable extends Migration
{
    public function up()
    {
        Schema::create('techpanda_core_bank_statements', function (Blueprint $table) {
            
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->date('transaction_date');
            $table->date('value_date');
            $table->string('transaction_ref_number');
            $table->string('user_ref_number');
            $table->string('description')->nullable();
            $table->string('transaction_branch_code')->nullable();
            $table->decimal('debit',10,2)->nullable();
            $table->decimal('credit',10,2)->nullable();
            $table->decimal('balance',10,2)->nullable(); 
            $table->smallInteger('association_id')->unsigned();
            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('techpanda_core_bank_statements');
    }
}