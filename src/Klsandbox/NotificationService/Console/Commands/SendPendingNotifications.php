<?php

namespace Klsandbox\NotificationService\Console\Commands;

use Illuminate\Console\Command;
use Klsandbox\NotificationService\Models\NotificationRequest;
use Klsandbox\SmsManager\SmsSender;
use Mail;

class SendPendingNotifications extends Command
{
    protected $signature = 'notifications:sendpending {limit?}';
    protected $description = 'Send pending notifications. Supports email and sms.';

    public function fire()
    {
        $this->comment('Sending notifications');

        $limit = $this->argument('limit');

        if ($limit) {
            $this->comment('Limit ' . $limit);
        }

        $unsentNotificationsQuery = NotificationRequest::where('sent', '=', false);

        $this->comment('Unsent notifications - count :' . $unsentNotificationsQuery->count());

        $smsSender = new SmsSender();

        $smsSender->getRouter()->controller('', '\App\Services\SmsController');

        $quota = $limit;

        foreach ($unsentNotificationsQuery->get() as $notificationRequest) {
            $this->comment("id:$notificationRequest->id target_id:$notificationRequest->target_id name:$notificationRequest->route");

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
                $targetUser = null;

                if ($notificationRequest->to_customer_id) {
                    $targetUser = $notificationRequest->toCustomer;
                } else {
                    $targetUser = $notificationRequest->toUser;
                }

                $validate = $smsSender->validate($notificationRequest->route, $notificationRequest->target_id, $targetUser, $this);
                if ($validate === null) {
                    continue;
                } elseif (!$validate) {
                    $this->error('failed validation');
                    continue;
                }

                if ($quota == 0) {
                    $this->comment('Quota exhausted');
                    break;
                }

                $quota--;

                $response = $smsSender->send($notificationRequest->route, $notificationRequest->target_id, $targetUser, $this);
                if ($response) {
                    $sent = true;
                    $notificationRequest->response_text = $response;
                } else {
                    $this->error('Notification not sent');
                }
            }

            $notificationRequest->sent = $sent;
            $notificationRequest->save();
        }
    }
}
