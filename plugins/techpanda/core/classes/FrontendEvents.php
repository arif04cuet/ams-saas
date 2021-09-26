<?php

namespace Techpanda\Core\Classes;

use Mail;

class FrontendEvents
{

    public function subscribe($events)
    {
        $events->listen('online.application.submitted', 'Techpanda\Core\Classes\FrontendEvents@onlineApplicationSubmittedHandler');
        $events->listen('associate.application.submitted', 'Techpanda\Core\Classes\FrontendEvents@associateApplicationSubmittedHandler');
    }

    public function associateApplicationSubmittedHandler($user)
    {
        // send emails to user and admin
        $data = $user->toArray();
        $data['fullName'] = $user->full_name;
        $data['link'] = config('app.url') . '/backend/backend/users/update/' . $user->id;

        //send to user
        $template = 'techpanda.core::mail.associate_application_submitted_to_user';
        Mail::send($template, $data, function ($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });

        //send to admin
        $template = 'techpanda.core::mail.associate_application_submitted_to_admin';
        Mail::send($template, $data, function ($message) use ($user) {
            $message->to('bcsp60ftccoop@gmail.com', 'P60FTC Cooperative');
        });
    }

    public function onlineApplicationSubmittedHandler($user)
    {
        // send emails to user and admin
        $data = $user->toArray();
        $data['fullName'] = $user->full_name;
        $data['link'] = config('app.url') . '/backend/backend/users/update/' . $user->id;

        //send to user
        $template = 'techpanda.core::mail.online_application_submitted_to_user';
        Mail::send($template, $data, function ($message) use ($user) {
            $message->to($user->email, $user->full_name);
        });

        //send to admin
        $template = 'techpanda.core::mail.online_application_submitted_to_admin';
        Mail::send($template, $data, function ($message) use ($user) {
            $message->to('bcsp60ftccoop@gmail.com', 'P60FTC Cooperative');
        });
    }
}
