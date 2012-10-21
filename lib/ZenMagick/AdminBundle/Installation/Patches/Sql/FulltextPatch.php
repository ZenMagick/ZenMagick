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
namespace ZenMagick\AdminBundle\Installation\Patches\Sql;

use ZenMagick\Base\Runtime;
use ZenMagick\AdminBundle\Installation\Patches\SQLPatch;


/**
 * Patch to create fulltext indexes for product search.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class FulltextPatch extends SQLPatch {
    var $sqlFiles_ = array(
        "fulltext_install.sql"
    );
    var $sqlUndoFiles_ = array(
        "fulltext_uninstall.sql"
    );


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('sqlFulltext');
        $this->label_ = 'Create indices for fulltext product search';
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        // the SQL doesn't break if re-applied
        return true;
    }

}
