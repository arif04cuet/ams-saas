<?php

namespace Techpanda\Core\Classes;

use Techpanda\Core\Models\Association;

class Helper
{
    private static $association = null;

    public static function getAssociation($id = null)
    {
        $id = self::getAssociationId() ? self::getAssociationId() : $id;

        if (!self::$association)
            self::$association =  Association::find($id);

        return self::$association;
    }

    public static function getAssociationId()
    {
        return session('user.association_id');
    }

    public static function getTenantField()
    {
        return 'association_id';
    }


    public static function address($user, $type = 'office')
    {
        $item = [];
        switch ($type) {
            case 'office':

                $item = [
                    'line1' =>   $user->first_name . ' ' . $user->last_name,
                    'line2' =>   $user->designation,
                    'line3' => $user->section,
                    'line4' => $user->office_name ? $user->office_name : '',
                    'line5' => 'Mobile: ' . $user->mobile,
                    'line6' => 'Member #' . $user->login
                ];

                break;


            case 'present':
                $item = [
                    'line1' =>   $user->first_name . ' ' . $user->last_name,
                    'line2' =>   $user->profile ? $user->profile->present_house_no . ', ' . $user->profile->present_road_no : '',
                    'line3' =>   $user->profile ? $user->profile->present_address . ', ' . $user->profile->present_post_code : '',
                    'line4' =>   '',
                    'line5' => 'Mobile:' . $user->mobile,
                    'line6' => 'Member #' . $user->login
                ];
                break;


            case 'permanent':
                $item = [
                    'line1' =>   $user->first_name . ' ' . $user->last_name,
                    'line2' =>   $user->profile ? $user->profile->permanent_house_no . ', ' . $user->profile->permanent_road_no : '',
                    'line3' =>   $user->profile ? $user->profile->permanent_address . ', ' . $user->profile->permanent_post_code : '',
                    'line4' =>   '',
                    'line5' => 'Mobile:' . $user->mobile,
                    'line6' => 'Member #' . $user->login
                ];
                break;
        }

        return $item;
    }
}
