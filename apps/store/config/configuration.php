<?php
/**
 * Map various zencart configuration options to their container parameter
 * equivalents
 *
 * @todo move to the zc bundle
 */

$zcDir = realpath(dirname($container->getParameter('kernel.root_dir')));

$container->setParameter('zencart.root_dir', $zcDir);
$container->setParameter('zencart.admin_dir', defined('ZENCART_ADMIN_FOLDER') ? ZENCART_ADMIN_FOLDER : 'admin');

$container->setParameter('session.class', 'zenmagick\http\session\Session');

$container->setParameter('zenmagick.http.session.timeout',  1440);
if ('admin' == $container->getParameter('kernel.context')) {
    $container->setParameter('zenmagick.http.session.timeout',  SESSION_TIMEOUT_ADMIN);
}
if ('storefront' == $container->getParameter('kernel.context')) {
    $container->setParameter('session.class', 'zenmagick\apps\storefront\http\Session');
}
