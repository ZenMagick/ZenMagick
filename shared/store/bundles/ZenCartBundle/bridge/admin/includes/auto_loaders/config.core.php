<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003-2005 The zen-cart developers                      |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: config.core.php 17921 2010-10-10 11:58:15Z wilt $
//
/**
 * autoloader array for catalog application_top.php
 *
 * @package admin
 * @copyright Copyright 2003-2005 zen-cart Development Team
**/
/**
 * 
 * require(DIR_WS_CLASSES . 'class.base.php'); 
 * require(DIR_WS_CLASSES . 'class.notifier.php'); 
 * $zco_notifier = new notifier()'
 * require(DIR_WS_CLASSES . 'sniffer.php'); 
 * require(DIR_WS_CLASSES . 'logger.php'); 
 * require(DIR_WS_CLASSES . 'shopping_cart.php'); 
 * require(DIR_WS_CLASSES . 'products.php'); 
 * require(DIR_WS_CLASSES . 'table_block.php');
 * require(DIR_WS_CLASSES . 'box.php'); 
 * require(DIR_WS_CLASSES . 'message_stack.php'); 
 * require(DIR_WS_CLASSES . 'split_page_results.php'); 
 * require(DIR_WS_CLASSES . 'object_info.php'); 
 * require(DIR_WS_CLASSES . 'class.phpmailer.php');
 * require(DIR_WS_CLASSES . 'class.smtp.php'); 
 * require(DIR_WS_CLASSES . 'upload.php'); 
 * 
 */
  $autoLoadConfig[0][] = array('autoType'=>'classInstantiate',
                                'className'=>'notifier',
                                'objectName'=>'zco_notifier');
/**
 * Breakpoint 10.
 * 
 * require('includes/init_includes/init_file_db_names.php');
 * require('includes/init_includes/init_database.ph');
 * require('includes/version.php');
 * 
 */
  $autoLoadConfig[10][] = array('autoType'=>'include_glob',
                                'loadFile'=>'includes/extra_configures/*.php');
  $autoLoadConfig[10][] = array('autoType'=>'require',
                                'loadFile'=>'../includes/filenames.php');
  $autoLoadConfig[10][] = array('autoType'=>'include',
                                'loadFile'=>'../includes/database_tables.php',
                                'once'=>true);
  $autoLoadConfig[10][] = array('autoType'=>'include_glob',
                                'loadFile'=>'includes/extra_datafiles/*.php');
  $autoLoadConfig[10][] = array('autoType'=>'classInstantiate',
                                'className'=>'queryFactory',
                                'objectName'=> 'db');
  $autoLoadConfig[10][] = array('autoType'=>'require',
                                'loadFile'=> '../includes/version.php');
/**
 * Breakpoint 20.
 * 
 * require('includes/init_includes/init_db_config_read.php');
 * 
 */
  $autoLoadConfig[20][] = array('autoType'=>'service',
                                'name'=>'productTypeLayoutService',
                                'method'=>'defineAll');
/**
 * Breakpoint 30.
 * 
 * require('includes/init_includes/init_gzip.php');
 * $sniffer = new sniffer();
 * 
 */
  $autoLoadConfig[30][] = array('autoType'=>'classInstantiate',
                                'className'=>'sniffer',
                                'objectName'=>'sniffer');
/**
 * Breakpoint 40.
 * 
 * require('includes/init_includes/init_general_funcs.php');
 * require('includes/init_includes/init_tlds.php');
 * 
 */
  $autoLoadConfig[40][] = array('autoType'=>'include_glob',
                                'loadFile'=>'includes/functions/{general.php,database.php,functions_customers.php,functions_metatags.php,functions_prices.php,html_output.php,localization.php,password_funcs.php}');
  $autoLoadConfig[40][] = array('autoType'=>'include_glob',
                                'loadFile'=> '../includes/functions/{audience.php,functions_email.php,sessions.php,zen_mail.php}');
  $autoLoadConfig[40][] = array('autoType'=>'include_glob',
                                'loadFile'=> 'includes/functions/extra_functions/*.php');
/**
 * Breakpoint 60.
 * 
 * require('includes/init_includes/init_sessions.php');
 * 
 */
/**
 * Breakpoint 70.
 * 
 * require('includes/init_includes/init_languages.php');
 * 
 */
  $autoLoadConfig[70][] = array('autoType'=>'service',
                                'name'=>'themeService',
                                'method'=>'initThemes');
  $autoLoadConfig[70][] = array('autoType'=>'service',
                                'name'=>'themeService',
                                'method'=>'getActiveThemeId',
                                'resultVar'=>'template_dir');
  $autoLoadConfig[70][] = array('autoType'=>'require',
                                'loadFile'=>'includes/languages/%language%.php');
  $autoLoadConfig[70][] = array('autoType'=>'require',
                                'loadFile'=>'includes/languages/%language%/%current_page%.php');
  $autoLoadConfig[70][] = array('autoType'=>'include_glob',
                                'loadFile'=>'includes/languages/%language%/extra_definitions/*.php');

/**
 * Breakpoint 80.
 * 
 * require('includes/init_includes/init_templates.php');
 * 
 */
  $autoLoadConfig[90][] = array('autoType'=>'classInstantiate',
                                'className'=>'template_func',
                                'objectName'=>'template');
/**
 * Breakpoint 90.
 * 
 * $zc_products = new products();
 * require(DIRWS_FUNCTIONS . 'localization.php');
 * 
 */
  $autoLoadConfig[90][] = array('autoType'=>'classInstantiate',
                                'className'=>'products',
                                'objectName'=>'zc_products');
/**
 * Breakpoint 100.
 * 
 * $messageStack = new messageStack();
 * 
 */
  $autoLoadConfig[100][] = array('autoType'=>'classInstantiate',
                                 'className'=>'messageStack',
                                 'objectName'=>'messageStack');
/**
 * Breakpoint 120.
 * 
 * require('includes/init_includes/init_special_funcs.php');
 * 
 */
  $autoLoadConfig[120][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_special_funcs.php');

/**
 * Breakpoint 130.
 * 
 * require('includes/init_includes/init_category_path.php');
 * 
 */

/**
 * Breakpoint 140.
 * 
 * require('includes/init_includes/init_errors.php');
 * 
 */

/**
 * Breakpoint 150.
 * 
 * require('includes/init_includes/init_admin_auth.php');
 * 
 */

/**
 * Breakpoint 160.
 * 
 * require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'audience.php');
 * 
 */

/**
 * Breakpoint 170.
 * 
 * require('includes/init_includes/init_admin_history.php');
 * 
 */
/**
 * Breakpoint 180.
 * 
 * require('includes/init_includes/init_html_editor.php);
 * 
 */
