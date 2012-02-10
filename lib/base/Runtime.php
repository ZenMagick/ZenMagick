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
namespace zenmagick\base;

use zenmagick\base\events\EventDispatcher;
use zenmagick\base\dependencyInjection\Container;

/**
 * Central place for runtime stuff.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Runtime {
    private static $container = null;
    private static $context = null;
    private static $environment = null;

    /**
     * Get context.
     *
     * @return string The current context or <code>null</code> if not set or supported.
     */
    public static function getContext() {
        return null != self::$context ? self::context : self::getSettings()->get('zenmagick.base.context', null);
    }

    /**
     * Test if the given context string matches.
     *
     * <p>The context string may be a single context name or a comma separated list of context strings.</p>
     * <p>If the context is found in the given context string it is considered as matched.</p>
     *
     * @param string s The context string to test for a match.
     * @param string context Optional context; default is <code>null</code> to use the current context.
     * @return boolean <code>true</code> if the current context is either <code>null</code> or matched inside the given string.
     */
    public static function isContextMatch($s, $context=null) {
        if (null === $context) {
            $context = self::getContext();
        }
        if (null === $context) {
            return true;
        }

        // string match, avoid whitespace before/after comma
        $cs = ','.str_replace(' ', '', $s).',';
        return false !== strpos($cs, ','.$context.',');
    }

    /**
     * Set context.
     *
     * @param string context The new context.
     */
    public static function setContext($context) {
        self::$context = $context;
    }

    /**
     * Get the full ZenMagick installation path.
     *
     * @return string The ZenMagick installation folder.
     */
    public static function getInstallationPath() {
        return defined('ZM_BASE_PATH') ? constant('ZM_BASE_PATH') : dirname(dirname(__FILE__));
    }

    /**
     * Get the full application path (if set).
     *
     * @return string The application base folder or <code>null</code>.
     */
    public static function getApplicationPath() {
        return defined('ZM_APP_PATH') ? self::getInstallationPath().'/'.ZM_APP_PATH : null;
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
                self::getInstallationPath().'/plugins',
                self::getApplicationPath().'/plugins'
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
        return self::getContainer()->get('loggingService');
    }

    /**
     * Get an event dispatcher instance.
     *
     * @return zenmagick\base\events\EventDispatcher A <code>zenmagick\base\events\EventDispatcher</code> instance.
     */
    public static function getEventDispatcher() {
        return self::getContainer()->get('eventDispatcher');
    }

    /**
     * Get the settings service.
     *
     * @return zenmagick\base\settings\Settings A <code>zenmagick\base\settings\Settings</code> instance.
     */
    public static function getSettings() {
        return self::getContainer()->get('settingsService');
    }

    /**
     * Get the pugins service.
     *
     * @return zenmagick\base\plugins\Plugins A <code>zenmagick\base\plugins\Plugins</code> instance.
     */
    public static function getPlugins() {
        return self::getContainer()->get('pluginService');
    }

    /**
     * Get the dependency injection container.
     *
     * @return Symfony\Component\DependencyInjection\ContainerInterface A <code>Symfony\Component\DependencyInjection\ContainerInterface</code> instance.
     */
    public static function getContainer() {
        if (null == self::$container) {
            self::$container = new Container();
        }

        return self::$container;
    }

    /**
     * Get environment.
     *
     * @return string The current environment or prod if not set.
     */
    public static function getEnvironment() {
        return null != self::$environment ? self::$environment : 'prod';
    }

    /**
     * Set environment.
     *
     * @param string environment The new environment.
     */
    public static function setEnvironment($environment) {
        self::$environment = $environment;
    }


}
