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
 * Central place for runtime stuff.
 *
 * @author DerManoMann
 * @package zenmagick.base
 */
class Runtime {
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
            if (null == (self::$singletons_[$name] = Beans::getBean($name))) {
                self::$singletons_[$name] = \ZMBeanUtils::getBean($name);
            }
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
        $settings = self::getSettings();
        if (null === $settings->get('zenmagick.base.plugins.dirs')) {
            // set default
            $settings->set('zenmagick.base.plugins.dirs', array(
                self::getInstallationPath().'plugins'.DIRECTORY_SEPARATOR,
                self::getApplicationPath().'plugins'.DIRECTORY_SEPARATOR
            ));
        }

        return $settings->get('zenmagick.base.plugins.dirs');
    }

    /**
     * Get the currently elapsed page execution time.
     *
     * @param string time Optional execution timestamp to be used instead of the current time.
     * @return long The execution time in milliseconds.
     */
    public static function getExecutionTime($time=null) {
        $startTime = explode (' ', ZM_START_TIME);
        $endTime = explode (' ', (null!=$time?$time:microtime()));
        // $time might be float
        if (1 == count($endTime)) { $endTime[] = 0;}
        $executionTime = $endTime[1]+$endTime[0]-$startTime[1]-$startTime[0];
        return round($executionTime, 4);
    }

    /**
     * Get a logging instance.
     *
     * <p>The scope is for future use.</p>
     *
     * @param mixed scope The scope of the logging instance; default is <code>null</code>.
     * @return zenmagick\base\logging\Logging A <code>zenmagick\base\logging\Logging</code> instance.
     */
    public static function getLogging($scope=null) {
        return self::singleton('zenmagick\base\logging\Logging');
    }

    /**
     * Get an event dispatcher instance.
     *
     * @return zenmagick\base\events\EventDispatcher A <code>zenmagick\base\events\EventDispatcher</code> instance.
     */
    public static function getEventDispatcher() {
        return self::singleton('zenmagick\base\events\EventDispatcher');
    }

    /**
     * Get the settings service.
     *
     * @return zenmagick\base\settings\Settings A <code>zenmagick\base\settings\Settings</code> instance.
     */
    public static function getSettings() {
        return self::singleton('zenmagick\base\settings\Settings');
    }

    /**
     * Get the pugins service.
     *
     * @return zenmagick\base\plugins\Plugins A <code>zenmagick\base\plugins\Plugins</code> instance.
     */
    public static function getPlugins() {
        return self::singleton('zenmagick\base\plugins\Plugins');
    }

    /**
     * Get the dependency injection container.
     *
     * @return Symfony\Component\DependencyInjection\ContainerInterface A <code>Symfony\Component\DependencyInjection\ContainerInterface</code> instance.
     */
    public static function getContainer() {
        return null;
    }

}
