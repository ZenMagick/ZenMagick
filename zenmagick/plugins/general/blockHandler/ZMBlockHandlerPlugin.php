<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Allows to manage blocks of template space by adding formatted HTML comments.
 *
 * <p>Later, the content to be inserted at those comments can be assigned via UI.</p>
 *
 * @package org.zenmagick.plugins.blockHandler
 * @author DerManoMann
 * @version $Id$
 */
class ZMBlockHandlerPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Block Handler', 'Generic content management via HTML comments', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        // force creating service instance
        ZMBlockManager::instance();
        if (!ZMLangUtils::asBoolean(ZMSettings::get('isAdmin'))) {
            // handle storefront
            ZMEvents::instance()->attach(ZMBlockManager::instance());
        }

        $this->addMenuItem('BlockHandlerAdmin', zm_l10n_get('Block Handler Admin'), 'BlockHandlerAdmin');


        // ********* SAMPLE CODE **************** //
        // this should perhaps come from the database, whereever as there might be more contents, so
        // the sort order must be determined beforehand

        if (ZMTemplateManager::instance()->isLeftColEnabled()) {
            foreach (ZMTemplateManager::instance()->getLeftColBoxNames() as $box) {
                ZMBlockManager::instance()->registerBlock('leftColumn', new ZMSideboxBlockContents($box));
            }
        }

        if (ZMTemplateManager::instance()->isRightColEnabled()) {
            foreach (ZMTemplateManager::instance()->getRightColBoxNames() as $box) {
                ZMBlockManager::instance()->registerBlock('rightColumn', new ZMSideboxBlockContents($box));
            }
        }

    }

}

?>
