<?php

namespace App\Services\Campaigns;

use App\Models\CampaignRecipient;
use Illuminate\Support\Facades\Mail;

/**
 * Unlike SMS/WhatsApp, email needs no bespoke provider abstraction - Laravel's
 * Mail facade already is one (SMTP/SES/Mailgun/Brevo/Postmark are just config,
 * via MAIL_MAILER). Defaults to the "log" driver already configured for this
 * app, same as everywhere else.
 */
class EmailProvider
{
    public function send(CampaignRecipient $recipient, string $subject, string $message): SendResult
    {
        $email = $recipient->contact->email;

        if (! $email) {
            return SendResult::failure('Contact has no email address.');
        }

        try {
            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)->subject($subject);
            });

            return SendResult::success();
        } catch (\Throwable $e) {
            return SendResult::failure($e->getMessage());
        }
    }
}
