<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Admin menu item.
 *
 * <p>This may be either a ZenMagick system page or a plugin options page or other.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.admin.menu
 * @deprecated
 */
class ZMAdminMenuItem extends ZMObject {
    private $parent_;
    private $id_;
    private $title_;
    private $function_;


    /**
     * Create new item.
     *
     * @param string parent The id of the parent.
     * @param string id The page id.
     * @param string title The page title.
     * @param string function The function to render the contents.
     */
    function __construct($parent, $id, $title, $function) {
        parent::__construct();
        $this->parent_ = $parent;
        // make it less likely to have name collisions...
        $this->id_ = (null != $parent ? $parent.'-' : '').$id;
        $this->title_ = $title;
        $this->function_ = $function;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the parent.
     *
     * @return string The parent id.
     */
    public function getParent() { return $this->parent_; }

    /**
     * Set the parent.
     *
     * @param, string parent The parent id.
     */
    public function setParent($parent) { $this->parent_ = $parent; }

    /**
     * Get the id.
     *
     * @return string The id.
     */
    public function getId() { return $this->id_; }

    /**
     * Get the title.
     *
     * @return string The title.
     */
    public function getTitle() { return $this->title_; }

    /**
     * Get the url.
     *
     * @return string The URL.
     */
    public function getURL() {
        if (null !== $this->function_) {
            //TODO: remove again...
            if (ZMLangUtils::endsWith($this->function_, '.php')) {
                return $this->function_;
            } else {
                $params = 'fkt='.$this->function_;
                return $this->container->get('request')->getToolbox()->admin->url('plugin_page',$params, false, false);
            }
        }
        return null;
    }

}
