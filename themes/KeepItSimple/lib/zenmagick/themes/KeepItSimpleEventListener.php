<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\themes;

use zenmagick\apps\store\themes\ThemeEventListener;

/**
 * Theme event listener.
 *
 * @author DerManoMann
 * @package zenmagick.themes
 */
class KeepItSimpleEventListener extends ThemeEventListener {

    /**
     * {@inheritDoc}
     */
    public function themeLoaded($event) {
        \ZMTemplateManager::instance()->setRightColBoxes(array('categories.php', 'manufacturers.php', 'information.php', 'banner_box.php'));
        if ('index' == \ZMRequest::instance()->getRequestId()) {
            \ZMTemplateManager::instance()->setLeftColBoxes(array('featured.php', 'reviews.php'));
        } else {
            \ZMTemplateManager::instance()->setLeftColEnabled(false);
            if (\ZMRequest::instance()->isCheckout(false)) {
                \ZMTemplateManager::instance()->setRightColBoxes(array('information.php'));
            }
        }

        \ZMSettings::set('isUseCategoryPage', false);
        \ZMSettings::set('resultListProductFilter', '');
        \ZMSettings::set('zenmagick.mvc.resultlist.defaultPagination', 6);
    }
}
