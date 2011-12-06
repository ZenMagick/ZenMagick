<?php
/*
 * ZenMagick - Another PHP framework.
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


/**
 * Widget base class.
 *
 * <p>Widgets are simple UI element container. They have some basic meta data (<em>title</em>,
 * <em>description</em>) and can either be enabled or disabled.</p>
 *
 * <p>Depending on the nature of the widgets, subclasses might implement custom properties as
 * needed.</p>
 *
 * <p>Since widgets are typically used in the context of an HTML page, the <code>render($request,$view)</code> method is expected
 * to return valid HTML that will display the widget.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.widgets
 */
abstract class ZMWidget extends ZMObject {
    private $title_;
    private $description_;
    private $enabled_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->title_ = '';
        $this->enabled_ = true;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the title.
     *
     * @param string title The title.
     */
    public function setTitle($title) {
        $this->title_ = $title;
    }

    /**
     * Get the title.
     *
     * @return string The title.
     */
    public function getTitle() {
        return $this->title_;
    }

    /**
     * Set the description.
     *
     * @param string description The description.
     */
    public function setDescription($description) {
        $this->description_ = $description;
    }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function getDescription() {
        return $this->description_;
    }

    /**
     * Controls whether this widget is enabled or not.
     *
     * @param boolean enabled The enabled state.
     */
    public function setEnabled($enabled) {
        $this->enabled_ = $enabled;
    }

    /**
     * Check if this widget is enabled.
     *
     * @return boolean The enabled state.
     */
    public function isEnabled() {
        return $this->enabled_;
    }

    /**
     * Get the HTML to render this widget.
     *
     * @param ZMRequest request The current request.
     * @param ZMView view The current view.
     * @return string The HTML.
     */
    public abstract function render($request, $view);

}
