<?php

namespace Plugin\Mailgun;

use Mailgun\Mailgun;

class PublicController
{

    private static $mg;

    public function contact()
    {
        ipRequest()->mustBePost();

        $post = ipRequest()->getPost();
        $res = ['status' => 'ok'];
        $form = Model::createForm();

        $errors = $form->validate($post);

        $errors = ipFilter('Mailgun_validateContactForm', $errors, $post);

        if ($errors) {
            $res['status'] = 'error';
            $res['errors'] = $errors;
        } else {
            $res['success'] = 'Thank you for subscribing';
        }

        $this->sendMail(
            $post['email'],
            ipGetOption(
                'Mailgun.defaultRecipientAddress',
                ipGetOptionLang('Config.websiteEmail')
            ),
            $post['subject'],
            $post['message']
        );

        ipEvent("Mailgun_registerSubscription", ['post' => $post]);
        return new \Ip\Response\Json($res);
    }

    private function sendMail(String $from, String $to, String $subject, String $message)
    {
        if (!isset(self::$mg)) {
            self::$mg = Mailgun::create(ipGetOption('Mailgun.apiKey'));
        }

        $data = [
            'from' => $from,
            'to' => $to,
            'subject' => '[lindhagen.io Contact] ' . $subject,
            'html' => $message,
            'text' => esc($message)
        ];

        try {
            self::$mg->messages()->send(ipGetOption('Mailgun.domain'), $data);
        } catch (\Exception $e) {
            ipLog()->error('Mailgun.contact Error', [
                'error' => $e->getMessage()
            ]);
            return;
        }

        ipLog()->notice('Mailgun.contact Message Sent', $data);
    }

}