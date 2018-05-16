<?php
namespace Dropcat\Lib;

use Exception;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

/**
 * Class Upload
 *
 * Upload something somewhere.
 *
 * @package Dropcat\Lib
 */
class Mail
{

    /**
     * Create a folder on a remote server.
     */
    public function send($config, $subject, $body)
    {
        $host = $config['mailer_host'];
        $port = $config['mailer_port'];
        $user = $config['mailer_user'];
        $password = $config['mailer_password'];
        $mailer_from_email = $config['mailer_from_email'];
        $mailer_from_name = $config['mailer_from_name'];
        $mailer_to_email = $config['mailer_to_email'];
        $mailer_to_name = $config['mailer_to_name'];
        $which_transport = $config['mailer_transport'];


        if ($which_transport == 'smtp') {
            $transport = (new Swift_SmtpTransport($host, $port));

            if (isset($password)) {
                $transport->setPassword($password);
            }
            if (isset($user)) {
                $transport->setUsername($user);
            }
        }

        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message($subject))
          ->setFrom([$mailer_from_email => $mailer_from_name])
          ->setTo([$mailer_to_email => $mailer_to_name])
          ->setBody($body)
        ;

        $result = $mailer->send($message);
    }
}
