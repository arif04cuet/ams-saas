<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateUserProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('techpanda_core_user_profiles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            
            $table->date('dob')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('sex')->nullable();
            $table->string('nid')->nullable();


            $table->string('marital_status')->nullable();
            $table->string('spouse_name')->nullable();
            $table->text('kids')->nullable();
            $table->string('nominee_name')->nullable();
            $table->string('nominee_nid')->nullable();
            $table->string('nominee_relation')->nullable();


            $table->string('present_house_no')->nullable();
            $table->string('present_road_no')->nullable();
            $table->text('present_address')->nullable();
            $table->string('present_post_code')->nullable();
            $table->string('present_phone')->nullable();

            $table->string('permanent_house_no')->nullable();
            $table->string('permanent_road_no')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('permanent_post_code')->nullable();
            $table->string('permanent_phone')->nullable();


            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('techpanda_core_user_profiles');
    }
}
