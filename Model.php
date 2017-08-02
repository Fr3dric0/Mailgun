<?php

namespace Plugin\Mailgun;

class Model
{
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