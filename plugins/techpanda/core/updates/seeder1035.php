<?php namespace Techpanda\Core\Updates;

use Seeder;
use DB;

class Seeder1035 extends Seeder
{
    public function run()
    {
        DB::table('techpanda_core_banks')->insert([
            
                'name'=>'DBBL',
                'code'=>'dbbl'
                
            ]);
    }
}