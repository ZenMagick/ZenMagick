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
namespace zenmagick\plugins\productTags;

use Plugin;


/**
 * Allow to add tags to products.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ProductTagsPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->setPreferredSortOrder(22);
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        $this->executePatch(file($this->getPluginDirectory()."/sql/install.sql"), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        $conn = \ZMRuntime::getDatabase();
        $sm = $conn->getSchemaManager();
        $sm->dropTable($conn->getPrefix().'tags');
        $sm->dropTable($conn->getPrefix().'product_tags');
    }

}
