<?php
/**
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

    if (!file_exists(dirname(__FILE__).'/extra_boxes/product_music_extras_dhtml.php')) {
        $extrasItems = ZMAdminMenu::getItemsForParentId(ZMAdminMenu::MENU_EXTRAS);
        if (0 < count($extrasItems)) {
            $zp_heading = array('text' => "Extras", 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));

            $zp_contents = array();
            foreach ($extrasItems as $item) {
                $zp_contents[] = array('text' => $item->getTitle(), 'link' => $item->getURL(), '', 'NONSSL');
            }

            echo zen_draw_admin_box($za_heading, $zp_contents);
        }
    }

    $toolbox = $request->getToolbox();
    $zm_heading = array();
    $zm_heading = array('text' => "ZenMagick", 'link' => '../zenmagick/apps/admin/web/');

    $zm_contents = array();
    echo zen_draw_admin_box($zm_heading, $zm_contents);

?>
<!-- zenmagick_eof //-->
