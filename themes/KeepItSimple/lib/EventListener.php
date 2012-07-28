<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\themes\KeepItSimple;

use zenmagick\base\Runtime;
use zenmagick\apps\store\themes\ThemeEventListener;

/**
 * Theme event listener.
 *
 * @author DerManoMann
 * @package zenmagick.themes.KeepItSimple
 */
class EventListener extends ThemeEventListener {

    /**
     * {@inheritDoc}
     */
    public function themeLoaded($event) {
        $templateManager = $this->container->get('templateManager');
        $templateManager->setRightColBoxes(array('categories.php', 'manufacturers.php', 'information.php', 'banner_box.php'));
        $requestId = $this->container->get('request')->getRequestId();
        if ('index' == $requestId) {
            $templateManager->setLeftColBoxes(array('featured.php', 'reviews.php'));
        } else {
            $templateManager->setLeftColEnabled(false);
            if ($requestId == 'shopping_cart' || !(false === strpos($requestId, 'checkout_'))) {
                $templateManager->setRightColBoxes(array('information.php'));
            }
        }
    }
}
