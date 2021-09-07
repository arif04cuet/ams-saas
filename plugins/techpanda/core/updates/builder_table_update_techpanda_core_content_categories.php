<?php namespace Techpanda\Core\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTechpandaCoreContentCategories extends Migration
{
    public function up()
    {
        Schema::table('techpanda_core_content_categories', function($table)
        {
            $table->integer('association_id');
        });
    }
    
    public function down()
    {
        Schema::table('techpanda_core_content_categories', function($table)
        {
            $table->dropColumn('association_id');
        });
    }
}
