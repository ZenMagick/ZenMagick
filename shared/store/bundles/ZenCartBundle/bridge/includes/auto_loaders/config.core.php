<?php
/**
 * autoloader array for catalog application_top.php
 * see  {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: config.core.php 4271 2006-08-26 01:21:02Z drbyte $
 */
 
/**
 * Changes: 
 *
 * dropped classes already available via ZenCartClassLoader
 * dropped init_sefu, init_gzip
 * dropped db cache
 * dropped phpbb3
 */
  $autoLoadConfig[0][] = array('autoType'=>'classInstantiate',
                                'className'=>'notifier',
                                'objectName'=>'zco_notifier');
/**
 * Breakpoint 10.
 * 
 * require('includes/init_includes/init_database.php');
 * require('includes/version.php');
 * 
 */
  $autoLoadConfig[10][] = array('autoType'=>'require',
                                'loadFile'=>'includes/filenames.php');
  $autoLoadConfig[10][] = array('autoType'=>'require',
                                'loadFile'=>'includes/database_tables.php');
  $autoLoadConfig[10][] = array('autoType'=>'include_glob',
                                'loadFile'=>'includes/extra_datafiles/*.php');

  $autoLoadConfig[10][] = array('autoType'=>'classInstantiate',
                                'className'=>'queryFactory',
                                'objectName'=> 'db');
/**
 * Breakpoint 20.
 * 
 * require('includes/init_includes/init_file_db_names.php');
 * 
 */
  $autoLoadConfig[20][] = array('autoType'=>'include',
                                'loadFile'=> DIR_WS_INCLUDES . 'version.php');
/**
 * Breakpoint 30.
 * 
 * $zc_cache = new cache(); 
 * 
 */

/**
 * Breakpoint 40.
 * 
 * require('includes/init_includes/init_db_config_read.php');
 * 
 */
  $autoLoadConfig[40][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_db_config_read.php');
/**
 * Breakpoint 50.
 * 
 * $sniffer = new sniffer();
 * require('includes/init_includes/init_gzip.php'); 
 * require('includes/init_includes/init_sefu.php'); 
 * $phpBB = new phpBB();
 */
  $autoLoadConfig[50][] = array('autoType'=>'classInstantiate',
                                'className'=>'sniffer',
                                'objectName'=>'sniffer');

/**
 * Breakpoint 60.
 * 
 * require('includes/init_includes/init_general_funcs.php'); 
 * require('includes/init_includes/init_tlds.php'); 
 * 
 */
  $autoLoadConfig[60][] = array('autoType'=>'include_glob',
                                'loadFile'=> 'includes/functions/{functions_email.php,functions_general.php,html_output.php,functions_ezpages.php}');
  $autoLoadConfig[60][] = array('autoType'=>'include_glob',
                                'loadFile'=> 'includes/functions/extra_functions/*.php');
  $autoLoadConfig[60][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_tlds.php');
/**
 * Include PayPal-specific functions
 *  require('includes/modules/payment/paypal/paypal_functions.php');
 */
  $autoLoadConfig[60][] = array('autoType'=>'include',
                                'loadFile'=> DIR_WS_MODULES . 'payment/paypal/paypal_functions.php',
                                'loaderPrefix'=>'paypal_ipn');

/**
 * Breakpoint 70.
 * 
 * require('includes/init_includes/init_sessions.php'); 
 * 
 */
  $autoLoadConfig[70][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_sessions.php');

  $autoLoadConfig[71][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_paypal_ipn_sessions.php',
                                'loaderPrefix'=>'paypal_ipn');

/**
 * Breakpoint 80.
 * 
 * if(!$_SESSION['cart']) $_SESSION['cart'] = new shoppingCart();
 * if(!$_SESSION['navigaton']) $_SESSION['navigation'] = new navigaionHistory();
 * 
 */
  $autoLoadConfig[80][] = array('autoType'=>'classInstantiate',
                                'className'=>'shoppingCart',
                                'objectName'=>'cart',
                                'checkInstantiated'=>true,
                                'classSession'=>true);
  $autoLoadConfig[80][] = array('autoType'=>'classInstantiate',
                                'className'=>'navigationHistory',
                                'objectName'=>'navigation',
                                'checkInstantiated'=>true,
                                'classSession'=>true,
                                'loaderPrefix'=>'config');
/**
 * Breakpoint 90.
 * 
 * currencies = new currencies();
 * 
 */
  $autoLoadConfig[90][] = array('autoType'=>'classInstantiate',
                                'className'=>'currencies',
                                'objectName'=>'currencies');
/**
 * Breakpoint 100.
 * 
 * require('includes/init_includes/init_sanitize.php'); 
 * $template = new template_func();
 * 
 */
  $autoLoadConfig[100][] = array('autoType'=>'classInstantiate',
                                 'className'=>'template_func',
                                 'objectName'=>'template');
  $autoLoadConfig[100][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_sanitize.php');
/**
 * Breakpoint 110.
 * 
 * require('includes/init_includes/init_languages.php'); 
 * require('includes/init_includes/init_templates.php'); 
 * 
 */
  $autoLoadConfig[110][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_languages.php');
  $autoLoadConfig[110][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_templates.php');
/**
 * Breakpoint 120.
 * 
 * $_SESSION['navigation']->add_current_page();
 * require('includes/init_includes/init_currencies.php'); 
 * 
 */
  $autoLoadConfig[120][] = array('autoType'=>'objectMethod',
                                'objectName'=>'navigation',
                                'methodName' => 'add_current_page',
                                'loaderPrefix'=>'config');
  $autoLoadConfig[120][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_currencies.php');
/**
 * Breakpoint 130.
 * 
 * require('includes/init_includes/init_customer_auth.php'); 
 * messageStack = new messageStack();
 * 
 */
  $autoLoadConfig[130][] = array('autoType'=>'classInstantiate',
                                 'className'=>'messageStack',
                                 'objectName'=>'messageStack');
  $autoLoadConfig[130][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_customer_auth.php',
                                 'loaderPrefix'=>'config');
/**
 * Breakpoint 140.
 * 
 * require('includes/init_includes/init_cart_handler.php'); 
 * 
 */
  $autoLoadConfig[140][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_cart_handler.php');
/**
 * Breakpoint 150.
 * 
 * require('includes/init_includes/init_special_funcs.php'); 
 * 
 */
  $autoLoadConfig[150][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_special_funcs.php');
/**
 * Breakpoint 160.
 * 
 * require('includes/init_includes/init_category_path.php'); 
 * $breadcrumb = new breadcrumb();
 */
  $autoLoadConfig[160][] = array('autoType'=>'classInstantiate',
                                 'className'=>'breadcrumb',
                                 'objectName'=>'breadcrumb');
  $autoLoadConfig[160][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_category_path.php');
/**
 * Breakpoint 170.
 * 
 * require('includes/init_includes/init_add_crumbs.php'); 
 * 
 */
  $autoLoadConfig[170][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_add_crumbs.php',
                                 'loaderPrefix'=>'config');

  $autoLoadConfig[170][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_ipn_postcfg.php',
                                 'loaderPrefix'=>'paypal_ipn');  
/**
 * Breakpoint 180.
 * 
 * require('includes/init_includes/init_header.php'); 
 * 
 */
  $autoLoadConfig[180][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_header.php',
                                'loaderPrefix'=>'config');
?>
