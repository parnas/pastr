<?php

$credFile = (isset($_ENV['CRED_FILE'])) ? $_ENV['CRED_FILE'] : '../app/config/creds.json';

if(file_exists($credFile)) {
    $cred = json_decode(file_get_contents($credFile));

    if(isset($cred->MYSQLS)) {
        $container->setParameter('database_host', $cred->MYSQLS->MYSQLS_HOSTNAME);
        $container->setParameter('database_port', $cred->MYSQLS->MYSQLS_PORT);
        $container->setParameter('database_name', $cred->MYSQLS->MYSQLS_DATABASE);
        $container->setParameter('database_user', $cred->MYSQLS->MYSQLS_USERNAME);
        $container->setParameter('database_password', $cred->MYSQLS->MYSQLS_PASSWORD);
    }

    if(isset($cred->MAILGUN)) {
        $container->setParameter('mailer_host', $cred->MAILGUN->MAILGUN_SMTP_SERVER);
        $container->setParameter('mailer_port', $cred->MAILGUN->MAILGUN_SMTP_PORT);
        $container->setParameter('mailer_user', $cred->MAILGUN->MAILGUN_SMTP_LOGIN);
        $container->setParameter('mailer_password', $cred->MAILGUN->MAILGUN_SMTP_PASSWORD);
    }

    if(isset($cred->LOGENTRIES)) {
        $container->setParameter('logentries_token', $cred->LOGENTRIES->LOGENTRIES_TOKEN);
    }

    if(isset($cred->OPENREDIS)) {
        $url = parse_url($cred->OPENREDIS->OPENREDIS_URL);

        foreach($url as $key => $value) {
            $container->setParameter('redis_'.$key, $value);
        }
    }

    if(isset($cred->CONFIG->CONFIG_VARS)) {
        foreach($cred->CONFIG->CONFIG_VARS as $key => $value) {
            $container->setParameter(strtolower($key), $value);
        }
    }
}
