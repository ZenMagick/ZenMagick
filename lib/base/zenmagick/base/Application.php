<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\base;

/**
 * Central place for application/runtime stuff.
 *
 * @author DerManoMann
 * @package zenmagick.base
 */
class Application {
    private static $singletons_ = array();


    /**
     * Get a singleton instance of the named class.
     *
     * @param string name The class name.
     * @param string instance If set, register the given object, unless the name is already taken.
     * @param boolean force Optional flag to force replacement.
     * @return mixed A singleton object.
     */
    public static function singleton($name, $instance=null, $force=false) {
        if (null != $instance && ($force || !isset(self::$singletons_[$name]))) {
            self::$singletons_[$name] = $instance;
        } else if (!array_key_exists($name, self::$singletons_)) {
            self::$singletons_[$name] = Beans::getBean($name);
        }

        return self::$singletons_[$name];
    }

    /**
     * Get the full ZenMagick installation path.
     *
     * @return string The ZenMagick installation folder.
     */
    public static function getInstallationPath() { 
        return defined('ZM_BASE_PATH') ? constant('ZM_BASE_PATH') : dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
    }

    /**
     * Get the full application path (if set).
     *
     * @return string The application base folder or <code>null</code>.
     */
    public static function getApplicationPath() { 
        return defined('ZM_APP_PATH') ? ZM_BASE_PATH.ZM_APP_PATH : null;
    }

    /**
     * Return the plugin base directory.
     *
     * <p>May be configured via the setting <em></em>. Default is <em>../lib/plugins</em>.</p>
     *
     * @return array List of base directories for plugins.
     */
    public static function getPluginBasePath() { 
        if (null === ZMSettings::get('zenmagick.core.plugins.baseDir')) {
            ZMSettings::set('zenmagick.core.plugins.baseDir', self::getInstallationPath().'plugins'.DIRECTORY_SEPARATOR);
        }

        return explode(',', ZMSettings::get('zenmagick.core.plugins.baseDir'));
    }

}
