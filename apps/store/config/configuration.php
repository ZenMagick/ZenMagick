<?php
/**
 * Map various zencart configuration options to their container parameter
 * equivalents
 *
 */

$rootDir = $container->getParameter('kernel.root_dir');
$context = $container->getParameter('kernel.context');

// @todo use parameters.yaml for db parameters.
$config = $rootDir.'/config/store-config.yaml';
$yaml = \Symfony\Component\Yaml\Yaml::parse($config);

$parameters = $yaml['apps']['store']['database']['default'];
$container->setParameter('database_driver', $parameters['driver']);
$container->setParameter('database_host', $parameters['host']);
$container->setParameter('database_name', $parameters['dbname']);
$container->setParameter('database_user', $parameters['user']);
$container->setParameter('database_password', $parameters['password']);
$container->setParameter('database_prefix', $parameters['prefix']);



\ZMRuntime::setDatabase('default', $parameters);
$configService = new \ZenMagick\apps\store\services\ConfigService;
foreach ($configService->loadAll() as $key => $value) {
    if (!defined($key)) { // @todo define at the last possible moment
        define($key, $value);
    }
}

$container->setParameter('session_handler', 'session.handler.pdo');

$container->setParameter('zencart.root_dir', realpath(dirname($rootDir)));
$container->setParameter('zencart.admin_dir', defined('ZENCART_ADMIN_FOLDER') ? ZENCART_ADMIN_FOLDER : 'admin');

if ('admin' == $context) {
    $container->setParameter('session_timeout',  SESSION_TIMEOUT_ADMIN);
}

