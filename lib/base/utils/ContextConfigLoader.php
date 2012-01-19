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
namespace zenmagick\base\utils;

use zenmagick\base\ZMObject;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\dependencyInjection\loader\YamlLoader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * Loader for multi-content config files.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ContextConfigLoader extends ZMObject {
    private $config;


    /**
     * Create new instance.
     *
     * @param mixed config Either a filename or array (already loaded YAML); default is <code>null</code>.
     */
    public function __construct($config=null) {
        parent::__construct();
        $this->setConfig($config);
    }


    /**
     * Set config.
     *
     * @param mixed config Either a filename or array (already loaded YAML); default is <code>null</code>.
     */
    public function setConfig($config) {
        if (null != $config) {
            $this->config = is_array($config) ? $config : Yaml::parse($config);
        }
    }

    /**
     * Get config.
     *
     * @return array The complete configuration as loaded.
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Resolve config for the given context.
     *
     * @param string context The contex; default is <code>null</code> to use the current context.
     * @return array The complete configuration for the chosen context.
     */
    public function resolve($context=null) {
        $context = null === $context ? Runtime::getContext() : $context;

        $cconfig = array();
        foreach ($this->config as $key => $data) {
            if ('meta' == $key) {
                $cconfig[$key] = $data;
                // meta is special and is context independent
            } else {
                // context key
                if (Runtime::isContextMatch($key, $context)) {
                    $cconfig = Toolbox::arrayMergeRecursive($cconfig, $data);
                }
            }
        }

        return $cconfig;
    }

    /**
     * Process config for the given context.
     *
     * @param string context The contex; default is <code>null</code> to use the current context.
     * @return array The complete configuration for the chosen context.
     */
    public function process($context=null) {
        $config = $this->resolve($context);
        $this->apply($config);
        return $config;
    }

    /**
     * Apply the given config.
     *
     * @param array config The configuration to process.
     */
    public function apply($config) {
        // php
        if (array_key_exists('php', $config) && is_array($config['php'])) {
            $phpConfig = $config['php'];
            if (array_key_exists('ini', $phpConfig) && is_array($phpConfig['ini'])) {
                foreach ($phpConfig['ini'] as $key => $value) {
                    if (null === $value) {
                        ini_restore($key);
                    } else {
                        ini_set($key, $value);
                    }
                }
            }
            if (array_key_exists('define', $phpConfig) && is_array($phpConfig['define'])) {
                foreach ($phpConfig['define'] as $name => $value) {
                    if (!defined($name)) {
                        define($name, $value);
                    }
                }
            }
        }

        // settings
        if (array_key_exists('settings', $config) && is_array($config['settings'])) {
            Runtime::getSettings()->setAll($config['settings']);
        }

        if (array_key_exists('container', $config) && is_array($config['container'])) {
            if (!$this->container->isFrozen()) {
                $containerYamlLoader = new YamlLoader($this->container, new FileLocator(dirname(__FILE__)));
                $containerYamlLoader->load($config['container']);
            } else {
                Runtime::getLogging()->warn('skipping container config - container is frozen');
            }
        }
    }

}
