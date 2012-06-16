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
//

  $autoLoadConfig[10][] = array('autoType'=>'include_glob',
                                'loadFile'=>'includes/extra_configures/*.php');

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

  $autoLoadConfig[90][] = array('autoType'=>'classInstantiate',
                                'className'=>'products',
                                'objectName'=>'zc_products');

