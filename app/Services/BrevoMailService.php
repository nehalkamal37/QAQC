<?php

namespace App\Services;

use GuzzleHttp\Client;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;

class BrevoMailService
{
    public static function send($to, $subject, $html)
    {
        try {
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', env('BREVO_API_KEY'));

            // Disable SSL verification for Windows
            $apiInstance = new TransactionalEmailsApi(
                new Client(['verify' => false]),
                $config
            );

            $email = new SendSmtpEmail([
                'subject' => $subject,
                'htmlContent' => $html,
                'sender' => [
                    'name'  => 'QA System',
                    'email' => 'noreply@myapp.com'
                ],
                'to' => [
                    ['email' => $to]
                ],
            ]);

            return $apiInstance->sendTransacEmail($email);

        } catch (\Exception $e) {
            \Log::error('Brevo email error: ' . $e->getMessage());
            return false;
        }
    }
}
