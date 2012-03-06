<?php
/**
 * application_top.php Common actions carried out at the start of each page invocation.
 *
 * Initializes common classes & methods. Controlled by an array which describes
 * the elements to be initialised and the order in which that happens.
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: application_top.php 19731 2011-10-09 17:20:30Z wilt $
 */
/**
 * inoculate against hack attempts which waste CPU cycles
 */
$contaminated = (isset($_FILES['GLOBALS']) || isset($_REQUEST['GLOBALS'])) ? true : false;
$paramsToAvoid = array('GLOBALS', '_COOKIE', '_ENV', '_FILES', '_GET', '_POST', '_REQUEST', '_SERVER', '_SESSION', 'HTTP_COOKIE_VARS', 'HTTP_ENV_VARS', 'HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_POST_FILES', 'HTTP_RAW_POST_DATA', 'HTTP_SERVER_VARS', 'HTTP_SESSION_VARS');
$paramsToAvoid[] = 'autoLoadConfig';
$paramsToAvoid[] = 'mosConfig_absolute_path';
$paramsToAvoid[] = 'hash';
$paramsToAvoid[] = 'main';
foreach($paramsToAvoid as $key) {
  if (isset($_GET[$key]) || isset($_POST[$key]) || isset($_COOKIE[$key])) {
    $contaminated = true;
    break;
  }
}
$paramsToCheck = array('main_page', 'cPath', 'products_id', 'language', 'currency', 'action', 'manufacturers_id', 'pID', 'pid', 'reviews_id', 'filter_id', 'zenid', 'sort', 'number_of_uploads', 'notify', 'page_holder', 'chapter', 'alpha_filter_id', 'typefilter', 'disp_order', 'id', 'key', 'music_genre_id', 'record_company_id', 'set_session_login', 'faq_item', 'edit', 'delete', 'search_in_description', 'dfrom', 'pfrom', 'dto', 'pto', 'inc_subcat', 'payment_error', 'order', 'gv_no', 'pos', 'addr', 'error', 'count', 'error_message', 'info_message', 'cID', 'page', 'credit_class_error_code');
if (!$contaminated) {
  foreach($paramsToCheck as $key) {
    if (isset($_GET[$key]) && !is_array($_GET[$key])) {
      if (substr($_GET[$key], 0, 4) == 'http' || strstr($_GET[$key], '//')) {
        $contaminated = true;
        break;
      }
      if (isset($_GET[$key]) && strlen($_GET[$key]) > 43) {
        $contaminated = true;
        break;
      }
    }
  }
}
unset($paramsToCheck, $paramsToAvoid, $key);
if ($contaminated) {
  header('HTTP/1.1 406 Not Acceptable');
  exit(0);
}
unset($contaminated);
/* *** END OF INNOCULATION *** */

define('IS_ADMIN_FLAG', false);
define('PAGE_PARSE_START_TIME', microtime());
define('DEBUG_AUTOLOAD', false);

/if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
  @ini_set('display_errors', TRUE);
  error_reporting(version_compare(PHP_VERSION, 5.3, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE : version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT : E_ALL & ~E_NOTICE);
} else {
  error_reporting(0);
}


// set php_self in the local scope
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

if (file_exists('includes/configure.php')) {
  include('includes/configure.php');
} else {
  $problemString = 'includes/configure.php not found';
  require('includes/templates/template_default/templates/tpl_zc_install_suggested_default.php');
  exit;
}

foreach(glob(DIR_FS_CATALOG.'includes/extra_configures/*.php') as $file) {
    include $file;
}
/**
 * Admin only.. destroy later
$template_dir = '';
define('DIR_WS_TEMPLATES', DIR_WS_INCLUDES . 'templates/');
*/


$autoLoadConfig = array();
$loaderPrefix = isset($loaderPrefix) ? $loaderPrefix : 'config';
$loader_file = $loaderPrefix . '.core.php';
require('includes/initsystem.php');
require('includes/autoload_func.php');

if ($spider_flag == false) {
// counter and counter history
  require(DIR_WS_INCLUDES . 'counter.php');
}
// get customers unique IP that paypal does not touch
$customers_ip_address = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['customers_ip_address'])) {
  $_SESSION['customers_ip_address'] = $customers_ip_address;
}
