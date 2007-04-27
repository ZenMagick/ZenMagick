<?php
/**
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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

    $za_heading = array();
    $za_heading = array('text' => "ZenMagick", 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));

    $za_contents = array();

    $installer = new ZMInstallationPatcher();
    $za_contents[] = array('text' => zm_l10n_get("Installation"), 'link' => zen_href_link(ZM_ADMINFN_INSTALLATION, '', 'NONSSL'));
    $za_contents[] = array('text' => zm_l10n_get("Plugins"), 'link' => zen_href_link(ZM_ADMINFN_PLUGINS, '', 'NONSSL'));

    $za_contents[] = array('text' => zm_l10n_get("Catalog Manager"), 'link' => zen_href_link(ZM_ADMINFN_CATALOG_MANAGER, '', 'NONSSL'));
    $za_contents[] = array('text' => zm_l10n_get("Language Tool"), 'link' => zen_href_link(ZM_ADMINFN_L10N, '', 'NONSSL'));
    $za_contents[] = array('text' => zm_l10n_get("Cache Manager"), 'link' => zen_href_link(ZM_ADMINFN_CACHE, '', 'NONSSL'));
    if (file_exists(DIR_FS_ADMIN.ZM_ADMINFN_CONSOLE)) {
        $za_contents[] = array('text' => zm_l10n_get("Console"), 'link' => zen_href_link(ZM_ADMINFN_CONSOLE, '', 'SSL'));
    }
    $za_contents[] = array('text' => zm_l10n_get("About"), 'link' => zen_href_link(ZM_ADMINFN_ABOUT, '', 'NONSSL'));

    echo zen_draw_admin_box($za_heading, $za_contents);
?>
<!-- zenmagick_eof //-->
