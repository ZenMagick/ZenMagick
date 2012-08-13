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

$container->setParameter('admin.session_timeout',  SESSION_TIMEOUT_ADMIN);
