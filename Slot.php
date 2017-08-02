<?php

namespace Plugin\Mailgun;

class Slot
{

    public static function Mailgun_contactForm($params)
    {
        $form = Model::createForm();

        $params['form'] = $form;

        return ipView('view/contactForm.php', $params)->render();
    }
}