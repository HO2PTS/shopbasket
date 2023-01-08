<?php
namespace App\Service;

use Mailjet\Client;
use Mailjet\Resources;

class MailService {

    private $api_key = 'bb08062dd57348c2f8727f02b4511fef';
    private $api_key_secret = '4039299141926302c951d1fad63020db';

    public function send($email, $name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "nivix77@gmail.com",
                        'Name' => "Hugo"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $name
                        ]
                    ],
                    'TemplateID' => 4446753,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables'=> [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
            
        return ;
    }
}