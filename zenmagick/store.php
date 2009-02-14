<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php

    // pick up messages from zen-cart request handling
    ZMMessages::instance()->_loadMessageStack();

    // main request processor
    if (ZMSettings::get('isEnableZMThemes')) {

        ZMEvents::instance()->fireEvent(null, ZMEvents::DISPATCH_START);
        zm_dispatch();
        ZMEvents::instance()->fireEvent(null, ZMEvents::DISPATCH_DONE);

        // allow plugins and event subscribers to filter/modify the final contents
        $_zm_args = ZMEvents::instance()->fireEvent(null, ZMEvents::FINALISE_CONTENTS, array('contents' => ZMPlugins::filterResponse(ob_get_clean())));
        echo $_zm_args['contents'];

        // clear messages if not redirect...
        ZMRequest::getSession()->clearMessages();

        ZMEvents::instance()->fireEvent(null, ZMEvents::ALL_DONE);

        require('includes/application_bottom.php');
        exit;
    }

?>
