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
namespace zenmagick\http\view;

/**
 * Container for viewId and model data, as returned by a controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ViewAndModel {
    private $viewId;
    private $model;

    /**
     * Create new instance.
     *
     * @param string viewId Optional viewId; default is <code>null</code>.
     * @param array model Optional model data; default is an empty array.
     */
    public function __construct($viewId=null, array $model=array()) {
        $this->viewId = $viewId;
        $this->model = $model;
    }

    /**
     * Set the view id.
     *
     * @param string viewId The viewId.
     */
    public function setViewId($viewId) {
        $this->viewId = $viewId;
    }

    /**
     * Get the view id.
     *
     * @return string The viewId.
     */
    public function getViewId() {
        return $this->viewId;
    }

    /**
     * Set the model data.
     *
     * @param array model The model data.
     */
    public function setModel(array $model) {
        $this->model = $model;
    }

    /**
     * Add model data.
     *
     * @param string name The name.
     * @param mxied value The model value.
     */
    public function addModel($name, $value) {
        $this->model[$name] = $value;
    }

    /**
     * Get the model data.
     *
     * @return array The model data.
     */
    public function getModel() {
        return $this->model;
    }

}
