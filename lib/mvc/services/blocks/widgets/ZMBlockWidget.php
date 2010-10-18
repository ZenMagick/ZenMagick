<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * A block widget.
 *
 * <p>This base class will render the configured template if set, or return an empty string.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.services.blocks.widgets
 */
class ZMBlockWidget extends ZMWidget {
    private $sortOrder_;
    private $template_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->sortOrder_ = 0;
        $this->template_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the sort order.
     *
     * @param int sortOrder The sort order.
     */
    public function setSortOrder($sortOrder) {
        $this->sortOrder_ = $sortOrder;
    }

    /**
     * Get the sort order.
     *
     * @return int The sort order.
     */
    public function getSortOrder() {
        return $this->sortOrder_;
    }

    /**
     * Set the template name.
     *
     * @param string template The template.
     */
    public function setTemplate($template) {
        $this->template_ = $template;
    }

    /**
     * Get the template name.
     *
     * @return string The template.
     */
    public function getTemplate() {
        return $this->template_;
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, $view) {
        if (empty($this->template_) || !$view->exists($request, $this->template_)) {
            return '';
        }

        // hand on all custom properties
        return $view->fetch($request, $this->template_, $this->getProperties());
    }

}
