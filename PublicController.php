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
            $res['success'] = 'Thank you for contacting';
            $res['replaceHtml'] = '<section>
                <h2>Message sent</h2>
                <p class="center success">Thank you for contacting us</p>
            </section>';
        }

        if (!$this->sendMail(
            $post['email'],
            ipGetOption(
                'Mailgun.defaultRecipientAddress',
                ipGetOptionLang('Config.websiteEmail')
            ),
            $post['subject'],
            $post['message']
        )) {
            $res['replaceHtml'] = '<section>
                <h2>Could not send message</h2>
                <p class="center error">Something wen\'t wrong. 
                If this issue consists please contact the administrators at ' .
                ipGetOption('Mailgun.defaultRecipientAddress', '[No email available]') .
                '</p>
            </section>';
        }

        ipEvent("Mailgun_registerSubscription", ['post' => $post]);
        return new \Ip\Response\Json($res);
    }

    private function sendMail($from, $to, $subject, $message)
    {
        if (!isset(self::$mg)) {
            self::$mg = Mailgun::create(ipGetOption('Mailgun.apiKey'));
        }

        $data = [
            'from' => $from,
            'to' => $to,
            'subject' => '[' . ipConfig()->baseUrl() . ' Contact] ' . $subject,
            'html' => $message,
            'text' => esc($message)
        ];

        try {
            self::$mg->messages()->send(ipGetOption('Mailgun.domain'), $data);
        } catch (\Exception $e) {
            ipLog()->error('Mailgun.contact Error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }

        ipLog()->notice('Mailgun.contact Message Sent', $data);
        return true;
    }

}