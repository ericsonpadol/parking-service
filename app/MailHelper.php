<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use SendGrid\Mail\Mail as SendGridMailbox;
use App\Copywrite;

class MailHelper extends Model
{
    /*
     * this mail helper is for reset password
     *
     * @var $mailParams Array mixed
     * @return $email Object mixed
     *
     */

    public function createResetPasswordMail(array $mailParams) {

        $toReplace = array('/:full_name:/', '/:reset_token:/');
        $fromReplace = array($mailParams['mail_to_name'], $mailParams['reset_token']);
        $emailContent = preg_replace($toReplace, $fromReplace, Copywrite::MAIL_RESET_PASSWORD_BODY_HTML);

        $email = new SendGridMailbox();
        $email->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $email->setSubject(Copywrite::MAIL_RESET_PASSWORD_SUBJECT);
        $email->addTo($mailParams['mail_to_email'], $mailParams['mail_to_name']);
        $email->addContent('text/html', $emailContent);

        return $email;
    }

}
