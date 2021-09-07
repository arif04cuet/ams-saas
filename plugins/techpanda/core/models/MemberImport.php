<?php

namespace Techpanda\Core\Models;

use ApplicationException;
use Backend\Models\ImportModel;
use Backend\Models\User;
use BackendAuth;
use Exception;
use System\Classes\MediaLibrary;
use System\Models\File;

class MemberImport extends ImportModel
{
    public $table = 'backend_users';

    /**
     * Validation rules
     */
    public $rules = [
        'login'   => 'required',
        'email' => 'required|email',
        'first_name' => 'required'
    ];

    public $imageStoragePath = '/members';

    public $imagePublic = true;


    public function importData($results, $sessionKey = null)
    {
        $firstRow = reset($results);


        /*
         * Import
         */
        foreach ($results as $row => $data) {
            try {

                $data = array_map('trim',$data);
                
                $member = [
                    'first_name' => $data['first_name'],
                    'login' => $data['login'],
                    'email' => $data['email'],
                    'mobile' => $data['mobile'],
                    'cadre' => $data['cadre'],
                    'role_id' => $data['role_id'],
                    'is_activated' => 1,
                    'association_id' => $data['association_id'],
                    'password' => '12345678',
                    'password_confirmation' => '12345678'
                ];

               $user = BackendAuth::register($member);
               
               //upload image for each user
               $avatar = $this->findAvatar($data['login']);

               if($avatar){

                   $user->avatar = $avatar;
                   $user->save();
               }


                if($user)
                    $this->logCreated();
              
            } catch (Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    private function findAvatar($username)
    {
        $last3 = substr($username,-3);
        
        $library = MediaLibrary::instance();
        $files = $library->listFolderContents($this->imageStoragePath, 'title', 'image');

        foreach ($files as $file) {
            
            $pathinfo = pathinfo($file->publicUrl);

            if (strpos($pathinfo['filename'],$last3) !==false) {
                $newFile = new File();
                $newFile->is_public = $this->imagePublic;
                $newFile->fromFile(storage_path('app/media' . $file->path));

                return $newFile;
            }
        }
    }

    
    protected function findDuplicateMember($data)
    {
        if ($id = array_get($data, 'id')) {
            return User::find($id);
        }

        $title = array_get($data, 'email');
        $member = User::where('email', $title);

        if ($login = array_get($data, 'member_number')) {
            $member->orWhere('login', $login);
        }

        return $member->first();
    }
}
