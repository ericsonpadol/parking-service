<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Copywrite;
use App\CustomConfig;
use Illuminate\Support\Facades\Mail;

class MailHelper extends Model
{
    /**
     * this mail helper is use to notify the admin that a document has been submitted by
     * the user for review
     * @param array $mailParams
     * @return mixed
     */
    public function reviewUserDocumentMail(array $mailParams) {
        $toReplaceSubject = ['/:docu_title:/', '/:user_fullname:/'];
        $fromReplaceSubject = [$mailParams['docu_title'], $mailParams['user_fullname']];
        $subjectContent = preg_replace($toReplaceSubject, $fromReplaceSubject, Copywrite::MAIL_DOCUMENT_REVIEW_SUBJECT);
        $toReplaceMail = ['/:docu_title:/', '/:user_fullname:/', '/:user_email:/', '/:docu_uri:/', '/:docu_message:/'];
        $fromReplaceMail = [$mailParams['docu_title'], $mailParams['user_fullname'],  $mailParams['user_email'], $mailParams['docu_uri'] ,$mailParams['docu_message']];
        $emailContent = preg_replace($toReplaceMail, $fromReplaceMail, Copywrite::MAIL_DOCUMENT_REVIEW_BODY);

        $mailboxParams = [
            'mail_subject' => $subjectContent,
            'mail_content' => $emailContent,
            'email_to' => config('app.admin_email'),
            'name_to' => config('app.admin_email_name'),
            'email_from' => env('MAIL_FROM_ADDRESS'),
            'name_from' => env('MAIL_FROM_NAME')
        ];

        $fireMailbox = Mail::send('document_review_mail', $mailboxParams, function ($message) use ($mailboxParams) {
            $message->from($mailboxParams['email_from'], $mailboxParams['name_from']);
            $message->to($mailboxParams['email_to'], $mailboxParams['name_to'])
                ->subject($mailboxParams['mail_subject']);
        });

        return $fireMailbox;
    }

    /**
     * this mail helper is use to notify the user that his account is approved
     * @param array $mailParams
     * @return mixed
     */
    public function approvedNotifyUserMail(array $mailParams) {
        $toReplace = ['/:user_fullname:/'];
        $fromReplace = [$mailParams['user_fullname']];
        $emailContent = preg_replace($toReplace, $fromReplace, Copywrite::MAIL_ACCOUNT_APPROVED_BODY_HTML);

        $mailboxParams = [
            'mail_content' => $emailContent,
            'email_to' => $mailParams['mail_to_email'],
            'name_to' => $mailParams['user_fullname'],
            'email_from' => env('MAIL_FROM_ADDRESS'),
            'name_from' => env('MAIL_FROM_NAME'),
        ];

        $fireMailbox = Mail::send('account_approved_mail', $mailboxParams, function ($message) use ($mailboxParams) {
            $message->from($mailboxParams['email_from'], $mailboxParams['name_from']);
            $message->to($mailboxParams['email_to'], $mailboxParams['name_to'])
                ->subject(Copywrite::MAIL_ACCOUNT_APPROVED_SUBJECT);
        });

        return $fireMailbox;
    }

    /**
     * this mail helper is for account approval
     * @param array $mailParams
     * @return mixed
     */
    public function approvedUserMail(array $mailParams) {
        $toReplace = ['/:user_email:/', '/:user_fullname:/', '/:approval_link:/', '/:approval_spiel:/'];
        $fromReplace = [
            $mailParams['user_email'],
            $mailParams['user_fullname'],
            $mailParams['approval_link'],
            Copywrite::MAIL_APPROVAL_SPIEL,
        ];
        $emailContent = preg_replace($toReplace, $fromReplace, Copywrite::MAIL_ACCOUNT_APPROVAL_BODY_HTML);

        $mailboxParams = [
            'mail_content' => $emailContent,
            'email_to' => config('app.admin_email'),
            'name_to' => config('app.admin_email_name'),
            'email_from' => env('MAIL_FROM_ADDRESS'),
            'name_from' => env('MAIL_FROM_NAME')
        ];

        $fireMailbox = Mail::send('approval_mail', $mailboxParams, function ($message) use ($mailboxParams) {
            $message->from($mailboxParams['email_from'], $mailboxParams['name_from']);
            $message->to($mailboxParams['email_to'], $mailboxParams['name_to'])
                ->subject(Copywrite::MAIL_ACCOUNT_APPROVAL_SUBJECT);
        });

        return $fireMailbox;
    }

    /**
     * this mail helper is for reset password
     *
     * @param array $mailParams
     * @return mixed
     */
    public function createResetPasswordMail(array $mailParams) {

        $toReplace = ['/:full_name:/', '/:reset_token:/'];
        $fromReplace = [$mailParams['mail_to_name'], $mailParams['reset_token']];
        $emailContent = preg_replace($toReplace, $fromReplace, Copywrite::MAIL_RESET_PASSWORD_BODY_HTML);

        $mailboxParams = [
            'mail_content' => $emailContent,
            'email_to' => $mailParams['mail_to_email'],
            'name_to' => $mailParams['mail_to_name'],
            'email_from' => env('MAIL_FROM_ADDRESS'),
            'name_from' => env('MAIL_FROM_NAME')
        ];

        $fireMailbox = Mail::send('reset_password_mail', $mailboxParams, function($message) use ($mailboxParams) {
                    $message->from($mailboxParams['email_from'], $mailboxParams['name_from']);
                    $message->to($mailboxParams['email_to'], $mailboxParams['name_to'])->subject(Copywrite::MAIL_RESET_PASSWORD_SUBJECT);
                });

        return $fireMailbox;
    }

    /**
     * this mail helper is for account verification
     *
     * @param array $mailParams
     * @return mixed
     */
    public function accountVerificationMail(array $mailParams) {
        $toReplace = ['/:full_name:/', '/:activation_spiel:/', '/:activation_link:/'];
        $fromReplace = [$mailParams['mail_fullname'], $mailParams['activation_spiel'], $mailParams['activation_link']];
        $emailContent = preg_replace($toReplace, $fromReplace, Copywrite::MAIL_ACTIVATION_BODY_HTML);

        $mailboxParams = [
            'mail_content' => $emailContent,
            'email_to' => $mailParams['mail_to_email'],
            'name_to' => $mailParams['mail_to_name'],
            'email_from' => env('MAIL_FROM_ADDRESS'),
            'name_from' => env('MAIL_FROM_NAME')
        ];

        $fireMailbox = Mail::send('account_activation_mail', $mailboxParams, function($message) use ($mailboxParams) {
            $message->from($mailboxParams['email_from'], $mailboxParams['name_from']);
            $message->to($mailboxParams['email_to'], $mailboxParams['name_to'])->subject(Copywrite::MAIL_ACTIVATION_SUBJECT);
        });

        return $fireMailbox;
    }

    /**
     * this mail helper is for account recovery
     *
     * @param Array $mailParams mixed
     * @return Object mixed
     */
    public function accountRecovery(array $mailParams) {
        $toReplace = ['/:full_name:/', '/:reset_token:/', '/:email_address:/'];
        $fromReplace = [$mailParams['mail_to_name'], $mailParams['reset_token'], $mailParams['mail_to_email']];
        $emailContent = preg_replace($toReplace, $fromReplace, Copywrite::MAIL_ACCOUNT_RECOVERY_BODY_HTML);

        $mailboxParams = [
            'mail_content' => $emailContent,
            'email_to' => $mailParams['mail_to_email'],
            'name_to' => $mailParams['mail_to_name'],
            'email_from' => env('MAIL_FROM_ADDRESS'),
            'name_from' => env('MAIL_FROM_NAME')
        ];

        $fireMailbox = Mail::send('account_recovery_mail', $mailboxParams, function($message) use ($mailboxParams) {
            $message->from($mailboxParams['email_from'], $mailboxParams['name_from']);
            $message->to($mailboxParams['email_to'], $mailboxParams['name_to'])->subject(Copywrite::MAIL_ACCOUNT_RECOVERY_SUBJECT);
        });

        return $fireMailbox;
    }
}
