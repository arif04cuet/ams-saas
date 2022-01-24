<?php
namespace Techpanda\Core\Classes\Jobs;

use Backend\Models\User;
use Mail;
use Techpanda\Core\Models\Content;

class SendMail
{
    public function fire($job, $data)
    {
        
        $content = Content::withoutGlobalScopes()->find($data['contentId']);
        $user = User::find($data['userId']);

        Mail::send('techpanda.core::mail.content', $content->toArray(), function ($message) use ($user,$content) {
            
            $fullName = $user->first_name.' '.$user->last_name;
            $message->to($user->email, $fullName);
            $message->subject('New content published on p60ftcsociety.org.bd');
            if($content->files)
            {
                foreach($content->files as $file)
                    $message->attach($file->getPath());

            }
            
        });

        $job->delete();
    }
}