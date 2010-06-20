<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * A dashboard widget.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.dashbord
 */
abstract class ZMDashboardWidget extends ZMWidget {
    private $id_;
    private $minimize_;
    private $maximize_;


    /**
     * Create new user.
     *
     * @param string id The widget id; default is <code>null</code> to use the class name.
     * @param string title The title; default is <code>null</code> to use the id.
     */
    function __construct($id=null, $title=null) {
        parent::__construct();
        $this->id_ = null != $id ? $id : get_class();
        $this->setTitle(null != $title ? $title : $this->id_);
        $this->minimize_ = true;
        $this->maximize_ = false;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the id.
     *
     * @return int The id.
     */
    public function getId() { return $this->id_; }

    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setId($id) { $this->id_ = $id; }

    /**
     * Get the (HTML) contents.
     *
     * @param ZMRequest request The current request.
     * @return string The contents.
     */
    public abstract function getContents($request);

    /**
     * Get the minimize flag.
     *
     * <p>Tells the dashboard wheter this widget can be minimized or not.</p>
     *
     * @return boolean The minimize flag.
     */
    public function isMinimize() { return $this->minimize_; }

    /**
     * Set the minimize flag.
     *
     * @parm boolean minimize The new value.
     */
    public function setMinimize($minimize) { $this->minimize_ = $minimize; }

    /**
     * Get the maximize flag.
     *
     * <p>Tells the dashboard wheter this widget can be maximized or not.</p>
     *
     * @return boolean The maximize flag.
     */
    public function isMaximize() { return $this->maximize_; }

    /**
     * Set the maximize flag.
     *
     * @parm boolean maximize The new value.
     */
    public function setMaximize($maximize) { $this->maximize_ = $maximize; }

    /**
     * {@inheritDoc}
     */
    public function render($request) {
        $lines = array(
            '<div class="portlet">',
            '  <div class="portlet-header">'.$this->getTitle().'</div>',
            '  <div class="portlet-content">',
            '    '.$this->getContents($request),
            '  </div>',
            '</div>'
        );
        return implode("\n", $lines);
    }

}
