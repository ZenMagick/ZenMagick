<?php
/**
 * Map various zencart configuration options to their container parameter
 * equivalents
 *
 */

$context = $container->getParameter('kernel.context');

$host = $container->getParameter('database_host');
$port = $container->getParameter('database_port');
$user = $container->getParameter('database_user');
$password = $container->getParameter('database_password');
$dbname = $container->getParameter('database_name');
$prefix = $container->getParameter('table_prefix');

\ZMRuntime::setDatabase('default', compact('host', 'port', 'user', 'password', 'dbname', 'prefix'));
try {
    $configService = new \ZenMagick\StoreBundle\Services\ConfigService;
    $configService->loadAll();
    $container->setParameter('session_handler', 'session.handler.pdo');
} catch(\Exception $e) {
    // Couldn't connect... this is arguably the wrong place to do this.
    if ($e->getCode() != 1049) {
        throw $e;
    }
    $container->setParameter('zenmagick.plugins.enabled', false);
}
$container->setParameter('zencart.admin_dir', defined('ZENCART_ADMIN_FOLDER') ? ZENCART_ADMIN_FOLDER : 'admin');

if ('admin' == $context) {
    $container->setParameter('session_timeout',  defined('SESSION_TIMEOUT_ADMIN') ? SESSION_TIMEOUT_ADMIN : 1440);
}

// @todo this is too naive. do better
if (!defined('SEND_EMAILS')) {
    return;
}

$container->setParameter('mailer_disable_delivery', SEND_EMAILS != 'true');

// @todo we should make this available with the rest of the email parameters.
if (defined('DEVELOPER_OVERRIDE_EMAIL_ADDRESS') && '' != DEVELOPER_OVERRIDE_EMAIL_ADDRESS) {
    $container->setParameter('mailer_delivery_address', DEVELOPER_OVERRIDE_EMAIL_ADDRESS);
}

$transport = EMAIL_TRANSPORT;
if ('PHP' == $transport) {
    $transport = 'mail';
}
if (in_array($transport, array('sendmail', 'sendmail-f', 'Qmail'))) {
    $transport = 'sendmail';
};
if ('smtp.gmail.com' == EMAIL_SMTPAUTH_MAIL_SERVER) {
    $transport = 'gmail';
}
if (in_array($transport, array('smtp', 'smtpauth'))) {
    $transport = 'smtp';
    $container->setParameter('mailer_host', EMAIL_SMTPAUTH_MAIL_SERVER);
    $port = EMAIL_SMTPAUTH_MAIL_SERVER_PORT;
    if ('' == trim($port)) $port = false;

    $container->setParameter('mailer_port', $port);
    if (in_array($port, array(465, 587))) {
        $container->setParameter('encryption', 'ssl');
    }
}

$container->setParameter('mailer_transport', $transport);
if ('' != trim(EMAIL_SMTPAUTH_MAILBOX)) {
    $container->setParameter('mailer_username', EMAIL_SMTPAUTH_MAILBOX);
    $container->setParameter('mailer_password', EMAIL_SMTPAUTH_PASSWORD);
}
