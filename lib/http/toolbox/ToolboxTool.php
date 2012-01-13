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
namespace zenmagick\http\toolbox;

use zenmagick\base\ZMObject;

/**
 * Toolbx tool base class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ToolboxTool extends ZMObject {
    protected $request_;
    protected $toolbox_;
    protected $view_;

    /**
     * Set the request.
     *
     * @param ZMRequest request The current request.
     */
    public function setRequest($request) {
        $this->request_ = $request;
    }

    /**
     * Get the request.
     *
     * @return ZMRequest The current request.
     */
    public function getRequest() {
        return $this->request_;
    }

    /**
     * Set the toolbox itself.
     *
     * @param Toolbox toolbox The toolbox.
     */
    public function setToolbox($toolbox) {
        $this->toolbox_ = $toolbox;
    }

    /**
     * Get the toolbox.
     *
     * @return Toolbox The toolbox.
     */
    public function getToolbox() {
        return $this->toolbox_;
    }

    /**
     * Set the current view.
     *
     * @param View view The view.
     */
    public function setView($view) {
        $this->view_ = $view;
    }

    /**
     * Get the view (if any).
     *
     * @return View The view.
     */
    public function getView() {
        return $this->view_;
    }

}
