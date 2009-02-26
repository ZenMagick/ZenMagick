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
 */
?>
<?php


/**
 * Cron image controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_cron
 * @version $Id$
 */
class ZMCronImageController extends ZMController {

    /**
     * {@inheritDoc}
     */
    public function process() { 
        $plugin = ZMPlugins::getPluginForId('zm_cron');
        header("Content-Type: image/gif");

        if (null != $plugin) {
            // execute configured jobs
            $plugin->runCron();
        }

        // create 1x1 image
        echo base64_decode("R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");

        // no more output
        return null;
    }

}

?>
