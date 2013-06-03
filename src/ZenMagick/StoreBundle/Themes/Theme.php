<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\StoreBundle\Themes;

use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Utils\ContextConfigLoader;

/**
 * A theme.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Theme extends ZMObject
{
    private $id;
    private $config;
    private $basePath;
    private $locales;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->id = null;
        $this->config = array();
        $this->basePath = null;
        $this->locales = array();
    }

    /**
     * Set the theme id.
     *
     * @param string id The theme id.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the themes id.
     *
     * @return string The theme id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the full path to the themes base directory.
     *
     * @param string path The path.
     */
    public function setBasePath($path)
    {
        $this->basePath = $path;;
    }

    /**
     * Add a locale code supported by this theme.
     *
     * @param string code The locale code.
     */
    public function addLocale($code)
    {
        $this->locales[$code] = $code;
    }

    /**
     * Set locale codes for all locale supported by this theme.
     *
     * @param array locales The locale codes.
     */
    public function setLocales(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * Get locale codes for all locale supported by this theme.
     *
     * @return array The locale codes.
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
    /**
     * Return the full path to the themes base directory.
     *
     * @return string The theme base directory.
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Get theme config.
     *
     * @param string key Optional config key; default is <code>null</code> to return the full map.
     * @return mixed Theme config map, the value of a specific key or <code>null</code> for unknown keys.
     */
    public function getConfig($key=null)
    {
        if (null == $key) {
            return $this->config;
        }

        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        return null;
    }

    /**
     * Set theme name.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->getMeta('name') ?: '??';
    }

    /**
     * Get the meta data.
     *
     * @param string key Optional meta  config key; default is <code>null</code> to return the full meata map.
     * @return mixed Theme config meta map, the value of a specific key or <code>null</code> for unknown keys.
     */
    public function getMeta($key=null)
    {
        $meta = $this->getConfig('meta');
        if (null == $key) {
            return $meta;
        }
        if (array_key_exists($key, $meta)) {
            return $meta[$key];
        }

        return null;
    }

    /**
     * Set full theme config.
     *
     * @param array config The new config map.
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Set theme config value.
     *
     * @param mixed key The config key or an array to set all.
     * @param mixed value The value.
     */
    public function setConfigValue($key, $value)
    {
        if (is_array($key)) {
            $this->config = $key;

            return;
        }
        $this->config[$key] = $value;
    }

    /**
     * Return the path of the boxes directory.
     *
     * @return string A full filename denoting the themes boxes directory.
     */
    public function getBoxesDir()
    {
        return $this->getBasePath() . '/templates/boxes';
    }

    /**
     * Return the path of the views directory.
     *
     * @return string A full path to the theme views  folder.
     */
    public function getViewsPath()
    {
        return $this->getBasePath() . '/templates/views';
    }

    /**
     * Return the path of the template directory.
     *
     * @return string A full path to the theme's template folder.
     */
    public function getTemplatePath()
    {
        return $this->getBasePath() . '/templates';
    }

    /**
     * Return the path of the resources directory.
     *
     * @return string A full path to the theme's resources folder.
     */
    public function getResourcePath()
    {
        return $this->getBasePath() . '/public';
    }

    /**
     * Load Translations (l10n/i18n).
     *
     * @param Language language The language.
     */
    public function loadTranslations()
    {
        // @todo bring back theme translations when we have some.
    }

    /**
     * Load additional theme config settins from <em>theme.yaml</em>.
     */
    public function loadSettings()
    {
        $configLoader = $this->container->get('contextConfigLoader');
        $configLoader->apply($this->config);
    }

}
