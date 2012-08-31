<?php
use ZenMagick\base\Runtime;

$zcPath = $settings->get('zencart.root_dir');
$adminDir = $settings->get('zencart.admin_dir', 'admin');
/**
 * admin/storefront configure.php defines
 */
define('DB_TYPE', 'mysql');
if (!defined('IS_ADMIN_FLAG')) define('IS_ADMIN_FLAG', Runtime::isContextMatch('admin'));

// @todo we want this to be here!
define('DB_PREFIX', $settings->get('apps.store.database.default.prefix'));

// @todo these shouldn't be available by default
//define('DB_SERVER', $settings->get('apps.store.database.default.host'));
//define('DB_SERVER_USERNAME', $settings->get('apps.store.database.default.user'));
//define('DB_SERVER_PASSWORD', $settings->get('apps.store.database.default.password'));
//define('DB_DATABASE', $settings->get('apps.store.database.default.dbname'));

// @todo need to fix up for shared certificates
// @todo probably switch out this mechanism once we fully control the system
define('HTTP_SERVER', 'http://'.$httpServer);
define('HTTPS_SERVER', 'https://'.$httpServer);
define('HTTP_CATALOG_SERVER', 'http://'.$httpServer);
define('HTTPS_CATALOG_SERVER', 'https://'.$httpServer);

define('ENABLE_SSL_ADMIN', $settings->get('zenmagick.http.request.secure') ? 'true' : 'false');
define('ENABLE_SSL_CATALOG', $settings->get('zenmagick.http.request.secure') ? 'true' : 'false');
define('ENABLE_SSL', $settings->get('zenmagick.http.request.secure') ? 'true' : 'false');

define('DIR_WS_ADMIN', str_replace(Runtime::getInstallationPath(), '', $zcPath).'/'.$adminDir.'/');
define('DIR_WS_CATALOG', str_replace('//', '/', '/'.$requestContext.'/'));
define('DIR_WS_HTTPS_ADMIN', DIR_WS_ADMIN);
define('DIR_WS_HTTPS_CATALOG', DIR_WS_CATALOG);

define('DIR_WS_CATALOG_LANGUAGES', HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'includes/languages/');
define('DIR_WS_CATALOG_IMAGES', HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'images/');
define('DIR_WS_CATALOG_TEMPLATE', HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'includes/templates/');
define('DIR_WS_ICONS', 'images/icons/');
define('DIR_WS_IMAGES', 'images/');
define('DIR_WS_UPLOADS', 'images/uploads/');

define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_BOXES', DIR_WS_INCLUDES.'boxes/');
define('DIR_WS_CLASSES', DIR_WS_INCLUDES.'classes/');
define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES.'functions/');
define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES.'languages/');
define('DIR_WS_MODULES', DIR_WS_INCLUDES.'modules/');
define('DIR_WS_TEMPLATES', DIR_WS_INCLUDES.'templates/');

define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');

define('DIR_FS_CATALOG', $zcPath.'/');
define('DIR_FS_ADMIN', $zcPath.'/'.$settings->get('zencart.admin_dir').'/');
define('DIR_FS_CACHE', $zcPath.'/cache/');
define('DIR_FS_CATALOG_LANGUAGES', DIR_FS_CATALOG.'includes/languages/');
define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG.'images/');
define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG.'includes/modules/');
define('DIR_FS_CATALOG_TEMPLATES', DIR_FS_CATALOG.'includes/templates/');
define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG.'download/');
define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG.'pub/');
define('DIR_FS_EMAIL_TEMPLATES', DIR_FS_CATALOG.'email/');
define('DIR_FS_SQL_CACHE', $zcPath.'/cache/');
define('DIR_FS_UPLOADS', DIR_FS_CATALOG.DIR_WS_UPLOADS);

define('SQL_CACHE_METHOD', 'none'); // none,database,file

// @todo we do really want these here (or in db) but it's too early
//define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES.Runtime::getContainer()->get('themeService')->getActiveThemeId().'/');
//define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE.'images/');
//define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES.'icons/');

define('CHARSET', $settings->get('zenmagick.http.html.charset'));

// used by some zen-cart validation code
if (null != $shortUIFormat) {
    define('DOB_FORMAT_STRING', $shortUIFormat);
}
