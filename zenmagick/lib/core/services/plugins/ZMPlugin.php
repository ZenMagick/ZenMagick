<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Abstract base class for plugins.
 *
 * <p>Plugins are a simple way to add custom code to ZenMagick. In addition,
 * the <code>init()</code> method provides an easy way to also execute custom
 * code on each request.</p>
 *
 * <p>This base class comes with the following defaults:</p>
 * <dl>
 *  <dt>id</dt>
 *  <dd>The plugin's class name.</dd>
 *  <dt>group</dt>
 *  <dd>The group this plugin belongs to.</dd>
 *  <dt>name</dt>
 *  <dd>Empty string.</dd>
 *  <dt>description</dt>
 *  <dd>Empty string.</dd>
 *  <dt>version</dt>
 *  <dd><em>0.0</em>.</dd>
 *  <dt>enabled</dt>
 *  <dd><code>null<code>; unless the status is explicitely set, the setting 
 *   <em>zenmagick.plugins.[id].enabled</em> will be checked instead.</dd>
 *  <dt>pluginDirectory</dt>
 *  <dd>Location of the plugin class file.</dd>
 *  <dt>loaderPolicy</dt>
 *  <dd><code>ZMPlugin::LP_ALL</code>.</dd>
 * </dl>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.plugins
 * @version $Id$
 */
abstract class ZMPlugin extends ZMObject {
    // loader policy flags
    const LP_NONE = 'NONE';
    const LP_PLUGIN = 'PLUGIN';
    const LP_FOLDER = 'FOLDER';
    const LP_ALL = 'ALL';

    private $id_;
    private $group_;
    private $name_;
    private $description_;
    private $version_;
    private $enabled_;
    private $pluginDirectory_;
    private $loaderPolicy_;


    /**
     * Create new plugin with some defaults.
     */
    function __construct() {
        parent::__construct();
        $this->id_ = get_class($this);
        $this->group_ = null;
        $this->name_ = '';
        $this->description_ = '';
        $this->version_ = '0.0';
        $this->enabled_ = null;
        $this->pluginDirectory_ = dirname(__FILE__).DIRECTORY_SEPARATOR;
        $this->loaderPolicy_ = ZMPlugin::LP_ALL;
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
     * @return string A unique id.
     */
    public function getId() {
        return $this->id_;
    }

    /**
     * Set the id.
     *
     * @param string id A unique id.
     */
    public function setId($id) {
        $this->id_ = $id;
    }

    /**
     * Get the group.
     *
     * @return string The group.
     */
    public function getGroup() {
        return $this->group_;
    }

    /**
     * Set the group.
     *
     * @param string group The group.
     */
    public function setGroup($group) {
        $this->group_ = $group;
    }

   /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->name_;
    }

    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) {
        $this->name_ = $name;
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
     * Set the description.
     *
     * @param string description The description.
     */
    public function setDescription($description) {
        $this->description_ = $description;
    }

    /**
     * Get the version.
     *
     * @return string The version.
     */
    public function getVersion() {
        return $this->version_;
    }

    /**
     * Set the version.
     *
     * @param string version The version.
     */
    public function setVersion($version) {
        $this->version_ = $version;
    }

    /**
     * Get the plugin directory.
     *
     * @return string The plugin directoryr.
     */
    public function getPluginDirectory() {
        return $this->pluginDirectory_;
    }

    /**
     * Set the plugin directory.
     *
     * @param string The installation folder.
     */
    public function setPluginDirectory($directory) {
        $this->pluginDirectory_ = $directory;
    }

    /**
     * Check if this plugin is enabled.
     *
     * @return boolean <code>true</code> if the plugin is enabled, <code>false</code> if not.
     */
    public function isEnabled() {
        return null !== $this->enabled_ ? $this->enabled_ : ZMSettings::get('zenmagick.plugins.'.$this->getId().'.enabled', false);
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
    public abstract function init();

    /**
     * Get this plugin's loader policy.
     *
     * <p>The policy controlls the extend of automatic <code>ZMLoader</code> support this plugin requests.</p>
     * <p>It also affects the code included in <code>core.php</code>. Valid values are:</p>
     * <dl>
     *   <dt>ZMPlugin::LP_NONE</dt><dd>Not supported.</dd>
     *   <dt>ZMPlugin::LP_PLUGIN</dt><dd>Only the plugin class may be added; this is the default.</dd>
     *   <dt>ZMPlugin::LP_FOLDER</dt><dd>Everything in the plugin folder, excluding all subfolder and their contents.</dd>
     *   <dt>ZMPlugin::LP_ALL</dt><dd>All (<code>.php</code>) files can be added to <code>core.php</code>.</dd>
     * </dl>
     *
     * @return string The loader policy.
     */
    public function getLoaderPolicy() {
        return $this->loaderPolicy_;
    }

    /**
     * Set the loader policy for this plugin.
     *
     * @param string loaderPolicy The loader policy.
     */
    public function setLoaderPolicy($loaderPolicy) {
        $this->loaderPolicy_ = $loaderPolicy;
    }

}

?>
