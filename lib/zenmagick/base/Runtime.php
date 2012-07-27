<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
 * Copyright (parts) (c) Fabien Potencier <fabien@symfony.com>
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
namespace zenmagick\base;

/**
 * Central place for runtime stuff.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Runtime {
    private static $container = null;
    private static $context = null;

    /**
     * Get the application we are running.
     *
     * @return Application The application or <code>null</code>.
     */
    public static function getApplication() {
        return self::getContainer()->get('kernel');
    }

    public static function setContext($context) {
        self::$context = $context;
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
        $context = $context ?: self::$context;
        if (null === $context) {
            return true;
        }

        // string match, avoid whitespace before/after comma
        $cs = ','.str_replace(' ', '', $s).',';
        return false !== strpos($cs, ','.$context.',');
    }

    /**
     * Get the full ZenMagick installation path.
     *
     * @return string The ZenMagick installation folder.
     */
    public static function getInstallationPath() {
        return dirname(dirname(dirname(__DIR__)));
    }

    /**
     * Get the full application path (if set).
     *
     * @deprecated
     * @todo move to container parameter.
     * @return string The application base folder or <code>null</code>.
     */
    public static function getApplicationPath() {
        return self::getInstallationPath().'/apps/'.self::$context;
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

    public static function setContainer($container) {
        self::$container = $container;
    }

    /**
     * Get the dependency injection container.
     *
     * @return Symfony\Component\DependencyInjection\ContainerInterface A <code>Symfony\Component\DependencyInjection\ContainerInterface</code> instance.
     */
    public static function getContainer() {
        return self::$container;
    }
}
