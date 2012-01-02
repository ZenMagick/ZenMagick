<?php
/**
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

    $toolbox = $request->getToolbox();
    $zm_heading = array();
    $zm_heading = array('text' => "ZenMagick", 'link' => '../zenmagick/apps/admin/web/');

    $zm_contents = array();
    echo zen_draw_admin_box($zm_heading, $zm_contents);

?>
<!-- zenmagick_eof //-->
