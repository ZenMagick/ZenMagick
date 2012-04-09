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
 
  $autoLoadConfig[0][] = array('autoType'=>'include',
                               'loadFile'=> 'includes/configure.php');
  $autoLoadConfig[0][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_begin.php');
  $autoLoadConfig[0][] = array('autoType'=>'classInstantiate',
                                'className'=>'notifier',
                                'objectName'=>'zco_notifier');

  $autoLoadConfig[10][] = array('autoType'=>'include_glob',
                                'loadFile'=>'includes/extra_configures/*.php');
  $autoLoadConfig[10][] = array('autoType'=>'require',
                                'loadFile'=>'includes/filenames.php');
  $autoLoadConfig[10][] = array('autoType'=>'include',
                                'loadFile'=>'includes/database_tables.php',
                                'once'=>true);
  $autoLoadConfig[10][] = array('autoType'=>'include_glob',
                                'loadFile'=>'includes/extra_datafiles/*.php');

  $autoLoadConfig[10][] = array('autoType'=>'classInstantiate',
                                'className'=>'queryFactory',
                                'objectName'=> 'db');

  $autoLoadConfig[20][] = array('autoType'=>'include', // actually used by the paypal modules!
                                'loadFile'=> 'includes/version.php');

  $autoLoadConfig[50][] = array('autoType'=>'classInstantiate',
                                'className'=>'sniffer',
                                'objectName'=>'sniffer');

  $autoLoadConfig[60][] = array('autoType'=>'include_glob',
                                'loadFile'=> 'includes/functions/{functions_email.php,functions_general.php,html_output.php,functions_ezpages.php,sessions.php,zen_mail.php}');
  $autoLoadConfig[60][] = array('autoType'=>'include_glob',
                                'loadFile'=> 'includes/functions/extra_functions/*.php');

  $autoLoadConfig[60][] = array('autoType'=>'include',
                                'loadFile'=> 'includes/modules/payment/paypal/paypal_functions.php',
                                'loaderPrefix'=>'paypal_ipn');

  $autoLoadConfig[70][] = array('autoType'=>'service',
                                'name'=>'session', 'method'=>'getToken', 'session' => true,
                                'resultVar'=>'securityToken');

  $autoLoadConfig[80][] = array('autoType'=>'classInstantiate',
                                 'className'=>'messageStack',
                                 'objectName'=>'messageStack');
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

 $autoLoadConfig[81][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_paypal_ipn_sessions.php',
                                'loaderPrefix'=>'paypal_ipn');

  $autoLoadConfig[90][] = array('autoType'=>'classInstantiate',
                                'className'=>'currencies',
                                'objectName'=>'currencies');

  $autoLoadConfig[100][] = array('autoType'=>'classInstantiate',
                                 'className'=>'template_func',
                                 'objectName'=>'template');
  $autoLoadConfig[100][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_sanitize.php');

  $autoLoadConfig[110][] = array('autoType'=>'include',
                                'loadFile'=>'includes/classes/db/mysql/define_queries.php');
  $autoLoadConfig[110][] = array('autoType'=>'classInstantiate',
                                 'className'=> 'language',
                                 'objectName'=>'lng');
  $autoLoadConfig[110][] = array('autoType'=>'service',
                                'name'=>'themeService',
                                'method'=>'getActiveThemeId',
                                'resultVar'=>'template_dir');
  $autoLoadConfig[110][] = array('autoType'=>'include',
                                 'once'=>true,
                                 'loadFile'=>'includes/languages/%template_dir%/%language%.php');
  $autoLoadConfig[110][] = array('autoType'=>'include',
                                 'once'=>true,
                                 'loadFile'=>'includes/languages/%language%.php');
  $autoLoadConfig[110][] = array('autoType'=>'include_glob',
                                 'loadFile'=> array(
                                              'includes/languages/%language%/extra_definitions/%template_dir%/*.php',
                                              'includes/languages/%language%/extra_definitions/*.php'));

  $autoLoadConfig[120][] = array('autoType'=>'service',
                                'name'=>'productTypeLayoutService',
                                'method'=>'defineAll');
  $autoLoadConfig[120][] = array('autoType'=>'objectMethod',
                                'objectName'=>'navigation',
                                'methodName' => 'add_current_page',
                                'loaderPrefix'=>'config');
  $autoLoadConfig[120][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_currencies.php');

  $autoLoadConfig[130][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_customer_auth.php',
                                 'loaderPrefix'=>'config');

  $autoLoadConfig[140][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_cart_handler.php');

  $autoLoadConfig[150][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_special_funcs.php');

  $autoLoadConfig[160][] = array('autoType'=>'classInstantiate',
                                 'className'=>'breadcrumb',
                                 'objectName'=>'breadcrumb');
  $autoLoadConfig[160][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_category_path.php');

  $autoLoadConfig[170][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_add_crumbs.php',
                                 'loaderPrefix'=>'config');

  $autoLoadConfig[170][] = array('autoType'=>'include_glob',
                                 'loadFile'=>array(
                                                   'includes/languages/%language%/%template_dir%/checkout_process.php',
                                                   'includes/languages/%language%/checkout_process.php'),
                                 'loaderPrefix'=>'paypal_ipn');

  $autoLoadConfig[180][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_header.php',
                                'loaderPrefix'=>'config');
