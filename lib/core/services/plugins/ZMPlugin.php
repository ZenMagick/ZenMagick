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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Abstract base class for plugins.
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
 * @package org.zenmagick.core.services.plugins
 */
abstract class ZMPlugin extends ZMObject {
    /** Do not load any plugin files (except, of course, the plugin itself). */
    const LP_NONE = 'NONE';
    /** Load files from the plugin folder, but ignore subfolder. */
    const LP_FOLDER = 'FOLDER';
    /** Load all files from a lib subfolder, including subfolder. */
    const LP_LIB = 'LIB';
    /** Load all files including subfolder. */
    const LP_ALL = 'ALL';

    private $id_;
    private $name_;
    private $description_;
    private $version_;
    private $enabled_;
    private $pluginDirectory_;
    private $loaderPolicy_;
    private $context_;


    /**
     * Create new plugin with some defaults.
     */
    function __construct() {
        parent::__construct();
        // default
        $this->id_ = get_class($this);
        $this->name_ = '';
        $this->description_ = '';
        $this->version_ = '0.0';
        $this->enabled_ = null;
        $this->pluginDirectory_ = null;
        $this->loaderPolicy_ = self::LP_LIB;
        $this->context_ = null;
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
     * @param string directory The installation folder.
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
        return null !== $this->enabled_ ? $this->enabled_ : Runtime::getSettings()->get('zenmagick.core.plugins.'.$this->getId().'.enabled', false);
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
     * <dl>
     *   <dt>ZMPlugin::LP_NONE</dt><dd>Not supported.</dd>
     *   <dt>ZMPlugin::LP_FOLDER</dt><dd>Everything in the plugin folder, excluding all subfolder and their contents.</dd>
     *   <dt>ZMPlugin::LP_ALL</dt><dd>All (<code>.php</code>) files can be added to <code>core.php</code>.</dd>
     * </dl>
     *
     * @return string The loader policy.
     * @deprecated this is no longer supported
     */
    public function getLoaderPolicy() {
        return $this->loaderPolicy_;
    }

    /**
     * Set the loader policy for this plugin.
     *
     * @param string loaderPolicy The loader policy.
     * @deprecated this is no longer supported
     */
    public function setLoaderPolicy($loaderPolicy) {
        $this->loaderPolicy_ = $loaderPolicy;
    }

    /**
     * Get the context flags.
     *
     * @return string The context string.
     */
    public function getContext() {
        return $this->context_;
    }

    /**
     * Set the context string.
     *
     * @param string s The context string.
     */
    public function setContext($s) {
        $this->context_ = $s;
    }

}
