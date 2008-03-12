<?php
/**
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

    $zm_heading = array();
    $zm_heading = array('text' => "ZenMagick", 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));

    $zm_contents = array();

    $installer = new ZMInstallationPatcher();
    $zm_contents[] = array('text' => zm_l10n_get("Installation"), 'link' => zen_href_link(ZM_ADMINFN_INSTALLATION, '', 'NONSSL'));
    $zm_contents[] = array('text' => zm_l10n_get("Plugin Manager"), 'link' => zen_href_link(ZM_ADMINFN_PLUGINS, '', 'NONSSL'));

    $zm_contents[] = array('text' => zm_l10n_get("Catalog Manager"), 'link' => zen_href_link(ZM_ADMINFN_CATALOG_MANAGER, '', 'NONSSL'));
    $zm_contents[] = array('text' => zm_l10n_get("Cache Admin"), 'link' => zen_href_link(ZM_ADMINFN_CACHE, '', 'NONSSL'));
    $zm_contents[] = array('text' => zm_l10n_get("Language Tool"), 'link' => zen_href_link(ZM_ADMINFN_L10N, '', 'NONSSL'));
    if (file_exists(DIR_FS_ADMIN.ZM_ADMINFN_CONSOLE)) {
        $zm_contents[] = array('text' => zm_l10n_get("Console"), 'link' => zen_href_link(ZM_ADMINFN_CONSOLE, '', 'SSL'));
    }
    $zm_contents[] = array('text' => zm_l10n_get("Theme Builder"), 'link' => zen_href_link(ZM_ADMINFN_THEME_BUILDER, '', 'SSL'));
    if (zm_setting('isZMDefinePages')) {
        $zm_contents[] = array('text' => zm_l10n_get("Static Page Editor"), 'link' => zen_href_link(ZM_ADMINFN_SP_EDITOR, '', 'SSL'));
    }
    $zm_contents[] = array('text' => zm_l10n_get("About"), 'link' => zen_href_link(ZM_ADMINFN_ABOUT, '', 'NONSSL'));
    echo zen_draw_admin_box($zm_heading, $zm_contents);

    if (0 < count($_zm_menu)) {
        $zp_heading = array();
        $zp_heading = array('text' => "Plugins", 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));

        $zp_contents = array();

        foreach ($_zm_menu as $item) {
            if (null == $item || 'plugins' != $item->getParent()) {
                continue;
            }
            $url = $item->getURL();
            if (0 === strpos($url, 'fkt:')) {
                $url = zm_href('zmPluginPage.php', 'fkt=' . substr($url, 4), false);
            } else {
                $url = zen_href_link($url);
            }
            $zp_contents[] = array('text' => $item->getTitle(), 'link' => $url, '', 'NONSSL');
        }
        echo zen_draw_admin_box($zp_heading, $zp_contents);
    }

?>
<!-- zenmagick_eof //-->
