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
namespace ZenMagick\apps\admin\Installation\Patches\Sql;

use ZenMagick\Base\Runtime;
use ZenMagick\apps\admin\Installation\Patches\SQLPatch;


/**
 * Patch to add review title.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ReviewTitlePatch extends SQLPatch {
    var $sqlFiles_ = array(
        "/apps/admin/installation/etc/review_title_install.sql"
    );
    var $sqlUndoFiles_ = array(
        "/apps/admin/installation/etc/review_title_uninstall.sql"
    );


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('reviewTitle');
        $this->label_ = 'Add column to store review title';
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        $meta = \ZMRuntime::getDatabase()->getMetaData('reviews_description');
        return !array_key_exists('title', $meta);
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        if ($this->isOpen()) {
            return true;
        }

        $baseDir = Runtime::getInstallationPath();
        $status = true;
        foreach ($this->sqlUndoFiles_ as $file) {
            $sql = file($baseDir.$file);
            $status |= $this->_runSQL($sql);
        }
        return $status;
    }

}
