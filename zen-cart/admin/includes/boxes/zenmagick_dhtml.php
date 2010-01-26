<?php
/**
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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

    $toolbox = $request->getToolbox();
    $zm_heading = array();
    $zm_heading = array('text' => "ZenMagick", 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));

    $zm_contents = array();

    $installer = new ZMInstallationPatcher();
    $zm_contents[] = array('text' => zm_l10n_get("Installation"), 'link' => $toolbox->admin->url('installation', '', true));
    $zm_contents[] = array('text' => zm_l10n_get("Plugin Manager"), 'link' => $toolbox->admin->url('plugins', '', true));

    $zm_contents[] = array('text' => zm_l10n_get("Catalog Manager"), 'link' => $toolbox->admin->url('catalog_manager', '', true));
    $zm_contents[] = array('text' => zm_l10n_get("Cache Admin"), 'link' => $toolbox->admin->url('cache_admin', '', true));
    $zm_contents[] = array('text' => zm_l10n_get("Language Tool"), 'link' => $toolbox->admin->url('l10n', '', true));
    if (file_exists(DIR_FS_ADMIN.'zmConsole.php')) {
        $zm_contents[] = array('text' => zm_l10n_get("Console"), 'link' => $toolbox->admin->url('console', '', true));
    }
    $zm_contents[] = array('text' => zm_l10n_get("Theme Builder"), 'link' => $toolbox->admin->url('theme_builder', '', true));
    $zm_contents[] = array('text' => zm_l10n_get("Static Page Editor"), 'link' => $toolbox->admin->url('static_page_editor', '', true));
    $zm_contents[] = array('text' => zm_l10n_get("About"), 'link' => $toolbox->admin->url('about', '', true));
    echo zen_draw_admin_box($zm_heading, $zm_contents);

    $pluginItems = ZMAdminMenu::getItemsForParentId(ZMAdminMenu::MENU_PLUGINS);
    if (0 < count($pluginItems)) {
        $zp_heading = array('text' => "Plugins", 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));

        $zp_contents = array();
        foreach ($pluginItems as $item) {
            $zp_contents[] = array('text' => $item->getTitle(), 'link' => $item->getURL(), '', 'NONSSL');
        }

        echo zen_draw_admin_box($zp_heading, $zp_contents);
    }

?>
<!-- zenmagick_eof //-->
