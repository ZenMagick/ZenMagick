<?php
/**
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

require_once('../zenmagick/init.php');
require_once('../zenmagick/admin_init.php');

    $za_heading = array();
    $za_heading = array('text' => "ZenMagick", 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));

    $za_contents = array();

    $installer = new ZMInstallationPatcher();
    if ($installer->isPatchesOpen()) {
        $za_contents[] = array('text' => "Installation", 'link' => zen_href_link(ZM_ADMINFN_INSTALLATION, '', 'NONSSL'));
    }

    $za_contents[] = array('text' => "Catalog Manager", 'link' => zen_href_link(ZM_ADMINFN_CATALOG_MANAGER, '', 'NONSSL'));
    $za_contents[] = array('text' => "Language Tool", 'link' => zen_href_link(ZM_ADMINFN_L10N, '', 'NONSSL'));

    if (0 != count(zm_get_obsolete_files())) {
        $za_contents[] = array('text' => "Installation Cleanup", 'link' => zen_href_link(ZM_ADMINFN_CLEANUP, '', 'NONSSL'));
    }
  
    echo zen_draw_admin_box($za_heading, $za_contents);
?>
<!-- zenmagick_eof //-->
