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

    $toolbox = ZMToolbox::instance();
    $zm_heading = array();
    $zm_heading = array('text' => "ZenMagick", 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));

    $zm_contents = array();

    $installer = new ZMInstallationPatcher();
    $zm_contents[] = array('text' => zm_l10n_get("Installation"), 'link' => zen_href_link('zmInstallation.php', '', 'NONSSL'));
    $zm_contents[] = array('text' => zm_l10n_get("Plugin Manager"), 'link' => zen_href_link('zmPlugins.php', '', 'NONSSL'));

    $zm_contents[] = array('text' => zm_l10n_get("Catalog Manager"), 'link' => zen_href_link('zmCatalogManager.php', '', 'NONSSL'));
    $zm_contents[] = array('text' => zm_l10n_get("Cache Admin"), 'link' => zen_href_link('zmCacheAdmin.php', '', 'NONSSL'));
    $zm_contents[] = array('text' => zm_l10n_get("Language Tool"), 'link' => zen_href_link('zmL10n.php', '', 'NONSSL'));
    if (file_exists(DIR_FS_ADMIN.'zmConsole.php')) {
        $zm_contents[] = array('text' => zm_l10n_get("Console"), 'link' => zen_href_link('zmConsole.php', '', 'SSL'));
    }
    $zm_contents[] = array('text' => zm_l10n_get("Theme Builder"), 'link' => zen_href_link('zmThemeBuilder.php', '', 'SSL'));
    if (ZMSettings::get('isZMDefinePages')) {
        $zm_contents[] = array('text' => zm_l10n_get("Static Page Editor"), 'link' => zen_href_link('zmStaticPageEditor.php', '', 'SSL'));
    }
    $zm_contents[] = array('text' => zm_l10n_get("About"), 'link' => zen_href_link('zmAbout.php', '', 'NONSSL'));
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
                $url = $toolbox->net->url('zmPluginPage.php', 'fkt=' . substr($url, 4), false, false);
            } else {
                $url = zen_href_link($url);
            }
            $zp_contents[] = array('text' => $item->getTitle(), 'link' => $url, '', 'NONSSL');
        }
        if (0 < count($zp_contents)) {
            echo zen_draw_admin_box($zp_heading, $zp_contents);
        }
    }

?>
<!-- zenmagick_eof //-->
