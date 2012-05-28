<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\base\plugins;

use zenmagick\base\ZMObject;

/**
 * Base class for plugins.
 *
 * <p>Plugins are a simple way to add custom code to ZenMagick.</p>
 *
 * <p>This base class comes with the following defaults:</p>
 * <dl>
 *  <dt>id</dt>
 *  <dd>The plugin's class name.</dd>
 *  <dt>name</dt>
 *  <dd>Empty string.</dd>
 *  <dt>description</dt>
 *  <dd>Empty string.</dd>
 *  <dt>version</dt>
 *  <dd><em>0.0</em>.</dd>
 *  <dt>enabled</dt>
 *  <dd><code>null<code>; unless the status is explicitely set, the setting
 *   <em>zenmagick.base.plugins.[id].enabled</em> will be checked instead.</dd>
 *  <dt>pluginDirectory</dt>
 *  <dd>Location of the plugin class file.</dd>
 *  <dt>context</dt>
 *  <dd>Generic code to allow to configure different context values where the plugin allowed; default is <code>null</code>.</dd>
 * </dl>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class Plugin extends ZMObject {
    private $id;
    private $name;
    private $description;
    private $version;
    private $enabled_;
    private $pluginDirectory;
    private $context;
    private $sortOrder;
    private $preferredSortOrder;


    /**
     * Create new plugin with some defaults.
     */
    public function __construct() {
        parent::__construct();
        // default
        $this->id = get_class($this);
        $this->name = '';
        $this->description = '';
        $this->version = '0.0';
        $this->enabled_ = null;
        $this->pluginDirectory = null;
        $this->context = null;
        $this->sortOrder = 0;
        $this->preferredSortOrder = 0;
    }


    /**
     * Get the id.
     *
     * @return string A unique id.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param string id A unique id.
     */
    public function setId($id) {
        $this->id = $id;
    }

   /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set the description.
     *
     * @param string description The description.
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Get the version.
     *
     * @return string The version.
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * Set the version.
     *
     * @param string version The version.
     */
    public function setVersion($version) {
        $this->version = $version;
    }

    /**
     * Get the plugin directory.
     *
     * @return string The plugin directoryr.
     */
    public function getPluginDirectory() {
        return $this->pluginDirectory;
    }

    /**
     * Set the plugin directory.
     *
     * @param string directory The installation folder.
     */
    public function setPluginDirectory($directory) {
        $this->pluginDirectory = $directory;
    }

    /**
     * Check if this plugin is enabled.
     *
     * @return boolean <code>true</code> if the plugin is enabled, <code>false</code> if not.
     */
    public function isEnabled() {
        return null !== $this->enabled_ ? $this->enabled_ : $this->container->get('settingsService')->get('zenmagick.base.plugins.'.$this->getId().'.enabled', false);
    }

    /**
     * Enable/disable this plugin.
     *
     * @param boolean status The new status.
     */
    public function setEnabled($status) {
        $this->enabled_ = $status;
    }

    /**
     * Init this plugin.
     *
     * <p>Code to set up internal resources, etc. should be called here, rather than in the * constructor.</p>
     */
    public function init() {}

    /**
     * Get the context flags.
     *
     * @return string The context string.
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * Set the context string.
     *
     * @param string s The context string.
     */
    public function setContext($s) {
        $this->context = $s;
    }

    /**
     * Get the preferred sort order value.
     *
     * @return int The preferred sort order value.
     */
    public function getPreferredSortOrder() {
        return $this->preferredSortOrder;
    }

    /**
     * Set the preferred sort order value.
     *
     * @param int preferredSortOrder The preferred sort order value.
     */
    public function setPreferredSortOrder($preferredSortOrder) {
        $this->preferredSortOrder = $preferredSortOrder;
    }


    /**
     * Get the sort order value.
     *
     * @return int The sort order value.
     */
    public function getSortOrder() {
        return $this->sortOrder;
    }

    /**
     * Set the sort order value.
     *
     * @param int sortOrder The sort order value.
     */
    public function setSortOrder($sortOrder) {
        $this->sortOrder = $sortOrder;
    }

}
