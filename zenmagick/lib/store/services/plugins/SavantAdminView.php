<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * A Savant(3) view for admin pages.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.plugins
 * @version $Id$
 */
class SavantAdminView extends SavantView {
    private $templatePath_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->setTemplatePath(array(DIR_FS_ADMIN.'content'.DIRECTORY_SEPARATOR));
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
    protected function getLayout() {
        //XXX: for now we are dealing with views only
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplatePath($request) {
        return $this->templatePath_;
    }

    /**
     * Set the template path.
     *
     * @param array path The new template path.
     */
    public function setTemplatePath($path) {
        $this->templatePath_ = $path;
    }

}

?>
