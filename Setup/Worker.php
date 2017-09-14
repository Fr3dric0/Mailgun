<?php
namespace Plugin\Mailgun;


class Worker {

    public function activate() {

        $isSandbox = ipGetOption("Mailgun.isSandbox", false) == true;

        if ($isSandbox && empty(ipGetOption("Mailgun.sandboxEmail"))) {
            throw new \Ip\Exception("Mailgun is set to sandbox-mode, without a Sandbox Email!");
        }

    }
}