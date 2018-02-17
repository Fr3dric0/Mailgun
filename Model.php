<?php

namespace Plugin\Mailgun;

use Ip\Exception;
use Mailgun\Exception\HttpClientException;
use Mailgun\Mailgun;

class Model
{

    private static $mg;
    private $from, $fromName, $subject, $to, $to_name, $html, $plain;
    private $replyTo = null;
    private $domain, $key;
    private $tags = [];

    /**
     * Model constructor.
     * @param string $from Sender email
     * @param string $subject
     * @param string|null $to
     * @param string|null $domain
     * @throws Exception
     */
    function __construct($from, $subject, $to = null, $domain = null)
    {
        if (class_exists('Mailgun')) {
            throw new Exception('Missing required composer module Mailgun!');
        }
//
//        if (!isset(self::$mg)) {
//            self::$mg = Mailgun::create(ipGetOption('Mailgun.apiKey'));
//        }

        $this->from = $from;
        $this->subject = $subject;

        $this->to = !empty($to) ? $to : ipGetOption(
            'Mailgun.defaultRecipientAddress',
            ipGetOptionLang('Config.websiteEmail')
        );

        $this->domain = $domain;

        $this->key = ipGetOption('Mailgun.apiKey');
    }

    public function setToName($name) {
        $this->to_name = $name;
        return $this;
    }

    public function setFromName($name)
    {
        $this->fromName = $name;

        return $this;
    }

    public function setHtml($message)
    {
        $this->html = $message;
        return $this;
    }

    public function setPlain($message)
    {
        $this->plain = $message;
        return $this;
    }

    public function addTag($tag)
    {
        if (count($this->tags) >= 3) {
            throw new \Exception("Message has reached the maximum amount of tags (3)");
        }

        $this->tags[] = $tag;

        return $this;
    }

    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function send()
    {
        $isSandbox = ipGetOption("Mailgun.isSandbox", false) == true;

        if (empty($this->html) && empty($this->plain)) {
            ipLog()->warning("[WARN] Trying to send message with empty body", [
                'html' => $this->html,
                'plain' => $this->plain
            ]);

            throw new HttpClientException("Missing e-mail body! Please populate html og plain before sending", 400);
        }

        $mg = \Mailgun\Mailgun::create(ipGetOption('Mailgun.apiKey'));

        $data = [
            'from' => !empty($this->fromName) ?
                $this->fromName . " <$this->from>" : $this->from,
            'to' => !$isSandbox ? $this->to : ipGetOption("Mailgun.sandboxEmail", $this->to),
            'to_name' => !empty($this->to_name) ? $this->to_name : null,
            'subject' => $this->subject,
            'html' => $this->html,
            'text' => esc($this->plain)
        ];

        if (!empty($this->replyTo)) {
            $data['h:Reply-To'] = $this->replyTo;
        }

        if (!empty($data['cc']) && $isSandbox) {
            $data['cc'] = ipGetOption('Mailgun.sandboxEmail', $data['cc']);
        }

        if (!empty($data['bcc']) && $isSandbox) {
            $data['bcc'] = ipGetOption('Mailgun.sandboxEmail', $data['bcc']);
        }

        $mg->messages()->send(ipGetOption('Mailgun.domain', $this->domain), $data);
    }

    public static function createForm()
    {
        $form = new \Ip\Form();
        $form->setMethod('post');
        $form->setAction(ipConfig()->baseUrl());
        $form->setEnvironment(\Ip\Form::ENVIRONMENT_PUBLIC);
        $form->addClass('mailgun contact');

        $form->addField(new \Ip\Form\Field\Hidden([
            'name' => 'pa',
            'value' => 'Mailgun.contact'
        ]));

        $form->addField(new \Ip\Form\Field\Text([
            'name' => 'name',
            'label' => 'Name'
        ]));

        $form->addField(new \Ip\Form\Field\Email([
            'name' => 'email',
            'label' => 'Email',
            'validators' => ['Required']
        ]));

        $form->addField(new \Ip\Form\Field\Text([
            'name' => 'subject',
            'label' => 'Subject',
            'validators' => ['Required']
        ]));

        $form->addField(new \Ip\Form\Field\RichText([
            'name' => 'message',
            'label' => 'Message',
            'validators' => ['Required']
        ]));

        $form->addField(new \Ip\Form\Field\Submit(['value' => 'Send']));

        return $form;
    }
}