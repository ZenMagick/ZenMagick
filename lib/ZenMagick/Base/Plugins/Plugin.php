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
namespace ZenMagick\Base\Plugins;

use ZenMagick\Base\ZMObject;

/**
 * Plugin base class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Plugin extends ZMObject
{
    private $messages;
    protected $config;

    /**
     * Create new plugin instance.
     *
     * @param array config The plugin configuration; default is an empty array.
     */
    public function __construct(array $config = array())
    {
        parent::__construct();
        $this->setConfig($config);
        $this->messages = array();
    }

    /**
     * Set plugin config.
     *
     * @param array config The configuration.
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        if ($options = $this->getMeta('options')) {
            if (isset($options['properties'])) {
                foreach ($options['properties'] as $name => $property) {
                    $this->set($name, $property['value']);
                }
            }
        }
    }

    /**
     * Get the meta data.
     *
     * @param string key Optional meta data key; default is <code>null</code> for all.
     * @return mixed Either the whole meta data or a single meta data value.
     */
    public function getMeta($key = null)
    {
        if (null == $key) {
            return $this->config['meta'];
        } elseif (isset($this->config['meta'][$key])) {
            return $this->config['meta'][$key];
        }
        return null;
    }

    /**
     * Get the id.
     *
     * @return string A unique id.
     */
    public function getId()
    {
        return $this->getMeta('id');
    }

   /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->getMeta('name');
    }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function getDescription()
    {
        return $this->getMeta('description');
    }

    /**
     * Get the version.
     *
     * @return string The version.
     */
    public function getVersion()
    {
        return $this->getMeta('version');
    }

    /**
     * Get the plugin directory.
     *
     * @return string The plugin directoryr.
     */
    public function getPluginDirectory()
    {
        return $this->getMeta('pluginDir');
    }

    /**
     * Check if this plugin is enabled.
     *
     * @return boolean <code>true</code> if the plugin is enabled, <code>false</code> if not.
     */
    public function isEnabled()
    {
        return $this->getMeta('enabled');
    }

    /**
     * Get the context flags.
     *
     * @return string The context string.
     */
    public function getContext()
    {
        return $this->getMeta('context');
    }

    /**
     * Get the sort order value.
     *
     * @return int The sort order value.
     */
    public function getSortOrder()
    {
        return $this->getMeta('sortOrder');
    }

    /**
     * Get the preferred sort order (if any).
     *
     * @return int The sort order value.
     */
    public function getPreferredSortOrder()
    {
        return (int) $this->getMeta('preferredSortOrder');
    }

    /**
     * Get the map of configuration options.
     *
     * @return array Map of configuration options.
     */
    public function getOptions()
    {
        return (array) $this->getMeta('options');
    }

    /**
     * Check if this plugin has options.
     *
     * @return boolean <code>true</code> if options are available.
     */
    public function hasOptions()
    {
        return 0 < count($this->getOptions());
    }

    /**
     * Get optional installation messages.
     *
     * @return array List of <code>ZenMagick\Http\Messages\Message</code> instances.
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Install this plugin.
     *
     * <p>This default implementation will check for a <code>sql/install.sql</code> script and run it if found.</p>
     */
    public function install()
    {
        $file = $this->getPluginDirectory()."/sql/install.sql";
        if (file_exists($file)) {
            $this->executePatch(file($file), $this->messages);
        }
    }

    /**
     * Remove this plugin.
     *
     * <p>This default implementation will check for a <code>sql/uninstall.sql</code> script and run it if found.</p>
     */
    public function remove()
    {
        $file = $this->getPluginDirectory()."/sql/uninstall.sql";
        if (file_exists($file)) {
            $this->executePatch(file($file), $this->messages);
        }
    }

    /**
     * Execute a SQL patch.
     *
     * @param string sql The sql.
     * @param array Result message list.
     * @param boolean Debug flag.
     * @return boolean <code>true</code> for success, <code>false</code> if the execution fails.
     */
    public function executePatch($sql, $messages, $debug=false)
    {
        if (!empty($sql)) {
            $results = \ZenMagick\AdminBundle\Utils\SQLRunner::execute_sql($sql, $debug);
            foreach (\ZenMagick\AdminBundle\Utils\SQLRunner::process_patch_results($results) as $msg) {
                $messages[] = $msg;
            }
            return empty($results['error']);
        }

        return true;
    }

    /**
     * Init this plugin.
     *
     * <p>This method is part of the lifecylce of a plugin during storefront request handling.</p>
     * <p>Code to set up internal resources should be placed here, rather than in the * constructor.</p>
     *
     * @deprecated Use event callbacks instead.
     */
    public function init()
    {
    }

    /**
     * Check if the plugin is installed.
     *
     * @return boolean <code>true</code> if the plugin is installed, <code>false</code> if not.
     */
    public function isInstalled()
    {
        return (boolean) $this->getMeta('installed');
    }

    /**
     * Return the path of the plugin's template directory.
     *
     * @return string A full path to the plugin's template folder.
     */
    public function getTemplatePath()
    {
        return $this->getPluginDirectory() . '/templates/views';
    }

    /**
     * Return the path of the resources directory.
     *
     * @return string A full path to the plugin's resources folder.
     */
    public function getResourcePath()
    {
        return $this->getPluginDirectory() . '/public';
    }

    /**
     * Resolve a plugin relative URI.
     *
     * <p>The given <code>uri</code> is assumed to be relative to the plugin folder.</p>
     *
     * @param string uri The relative URI.
     * @return string An absolute URI or <code>null</code>.
     */
    public function pluginURL($uri)
    {
        $path = $this->getPluginDirectory().'/'.$uri;
        $templateView = $this->container->get('defaultView');
        return $templateView->getResourceManager()->file2uri($path);
    }

}
