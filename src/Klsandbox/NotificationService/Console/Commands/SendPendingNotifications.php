<?php

namespace Klsandbox\NotificationService\Console\Commands;

use Illuminate\Console\Command;
use Klsandbox\NotificationService\Models\NotificationRequest;
use Klsandbox\SmsManager\SmsSender;
use Mail;

class SendPendingNotifications extends Command
{
    protected $name = 'notifications:sendpending';
    protected $description = 'Send pending notifications. Supports email and sms.';

    public function fire()
    {
        $this->comment('Sending notifications');

        $unsentNotificationsQuery = NotificationRequest::forSite()
            ->where('sent', '=', false);

        $this->comment('Unsent notifications - count :' . $unsentNotificationsQuery->count());

        $smsSender = new SmsSender();

        $smsSender->getRouter()->controller('', '\App\Services\SmsController');

        foreach ($unsentNotificationsQuery->get() as $notificationRequest) {
            $this->comment("id:$notificationRequest->target_id name:$notificationRequest->route");

            $sent = false;
            if ($notificationRequest->channel == 'Email') {
                $route = $notificationRequest->route;
                $data = $this->$route($notificationRequest);

                $view = 'email.' . $route;
                $subjectView = 'email.subject-' . $route;
                $subject = view($subjectView, $data);
                Mail::send($view, $data, function ($mail) use ($data, $subject) {
                    $mail->subject($subject);
                    $mail->to($data['email']);
                });

                // TODO: Fix this
                $sent = true;
            } elseif ($notificationRequest->channel == 'Sms') {
                $validate = $smsSender->validate($notificationRequest->route, $notificationRequest->target_id, $notificationRequest->toUser, $notificationRequest->site, $this);
                if ($validate === null) {
                    continue;
                } elseif (!$validate) {
                    $this->error('failed validation');
                    continue;
                }

                $response = $smsSender->send($notificationRequest->route, $notificationRequest->target_id, $notificationRequest->toUser, $notificationRequest->site, $this);
                if ($response) {
                    $sent = true;
                    $notificationRequest->response_text = $response;
                } else {
                    $this->error('Notification not sent');
                }

                if ($notificationRequest->to_customer_id) {
                    if (!$smsSender->validate($notificationRequest->route, $notificationRequest->target_id, $notificationRequest->toCustomer, $notificationRequest->site, $this)) {
                        $this->error('failed validation');
                        continue;
                    }

                    $response = $smsSender->send($notificationRequest->route, $notificationRequest->target_id, $notificationRequest->toCustomer, $notificationRequest->site, $this);
                    if ($response) {
                        $sent = true;
                        $notificationRequest->response_text = $response;
                    } else {
                        $this->error('Notification not sent');
                    }
                }
            }

            $notificationRequest->sent = $sent;
            $notificationRequest->save();
        }
    }
}
