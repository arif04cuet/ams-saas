<?php namespace Techpanda\Core\Updates;

use Seeder;
use Backend\Models\UserRole;
use DB;


class Seeder1011 extends Seeder
{
    public function run()
    {
        
        //delete existing role
        
         DB::table('backend_user_roles')->truncate();
        

        $roles = [
            
                [ 'code'=>'member','name' => 'Association Member'],
                [ 'code'=>'tenant-admin','name' => 'Association Admin']
            
            ];
            
            DB::table('backend_user_roles')->insert($roles);
       

    }
}