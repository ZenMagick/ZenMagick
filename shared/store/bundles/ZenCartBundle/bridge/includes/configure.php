<?php
use zenmagick\base\Runtime;

$settings = Runtime::getSettings();
$request = Runtime::getContainer()->get('request');
/**
 * admin/storefront configure.php defines
 */
define('DB_TYPE', 'mysql');

// @todo we want this to be here!
//define('DB_PREFIX', $settings->get('apps.store.database.default.prefix'));

// @todo these shouldn't be available by default
//define('DB_SERVER', $settings->get('apps.store.database.default.host'));
//define('DB_SERVER_USERNAME', $settings->get('apps.store.database.default.user'));
//define('DB_SERVER_PASSWORD', $settings->get('apps.store.database.default.password'));
//define('DB_DATABASE', $settings->get('apps.store.database.default.dbname'));


// @todo need to fix up for shared certificates
// @todo probably switch out this mechanism once we fully control the system
$httpServer = $request->getHttpHost().'/';
define('HTTP_SERVER', 'http://'.$httpServer);
define('HTTPS_SERVER', 'https://'.$httpServer);
define('HTTP_CATALOG_SERVER', 'http://'.$httpServer);
define('HTTPS_CATALOG_SERVER', 'https://'.$httpServer);

define('ENABLE_SSL_ADMIN', $settings->get('zenmagick.http.request.secure') ? 'true' : 'false');
define('ENABLE_SSL_CATALOG', $settings->get('zenmagick.http.request.secure') ? 'true' : 'false');
define('ENABLE_SSL', $settings->get('zenmagick.http.request.secure') ? 'true' : 'false');

define('DIR_WS_ADMIN', '/'.$settings->get('apps.store.zencart.admindir').'/');
define('DIR_WS_CATALOG', basename($request->getBaseUrl).'/');
define('DIR_WS_HTTPS_ADMIN', DIR_WS_ADMIN);
define('DIR_WS_HTTPS_CATALOG', DIR_WS_CATALOG);

define('DIR_WS_BOXES', 'includes/boxes/');
define('DIR_WS_CATALOG_LANGUAGES', HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'includes/languages/');
define('DIR_WS_CATALOG_IMAGES', HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'images/');
define('DIR_WS_CATALOG_TEMPLATE', HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'includes/templates/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_WS_DOWNLOAD_PUBLIC', DIR_WS_CATALOG.'pub/');
define('DIR_WS_FUNCTIONS', 'includes/functions/');
define('DIR_WS_ICONS', 'images/icons/');
define('DIR_WS_IMAGES', 'images/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_LANGUAGES', 'includes/languages/');
define('DIR_WS_MODULES', 'includes/modules/');
define('DIR_WS_TEMPLATES', 'includes/templates/');
define('DIR_WS_UPLOADS', 'images/uploads/');

define('DIR_FS_CATALOG', ZC_INSTALL_PATH);
define('DIR_FS_ADMIN', ZC_INSTALL_PATH.$settings->get('apps.store.zencart.admindir').'/');
define('DIR_FS_CACHE', ZC_INSTALL_PATH.'/cache/');
define('DIR_FS_CATALOG_LANGUAGES', DIR_FS_CATALOG.'includes/languages/');
define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG.'images/');
define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG.'includes/modules/');
define('DIR_FS_CATALOG_TEMPLATES', DIR_FS_CATALOG.'includes/templates/');
define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG.'download/');
define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG.'pub/');
define('DIR_FS_EMAIL_TEMPLATES', DIR_FS_CATALOG.'email/');
define('DIR_FS_SQL_CACHE', ZC_INSTALL_PATH.'/cache/');
define('DIR_FS_UPLOADS', DIR_FS_CATALOG.DIR_WS_UPLOADS);

define('SQL_CACHE_METHOD', 'none'); // none,database,file

define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES.Runtime::getContainer()->get('themeService')->getActiveThemeId().'/');
define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE.'images/');
define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES.'icons/');

define('CHARSET', $settings->get('zenmagick.http.html.charset'));

