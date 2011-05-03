<?php
/*
 * ZenMagick - Smart e-commerce
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


/**
 * Custom admin event handler for various things.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin
 */
class ZMAdminEventHandler {

    /**
     * Display message about invalid/insufficient credentional
     */
    public function onInsufficientCredentials($event) {
        $request = $event->get('request');
        if (null != $request->getUser()) {
            // only if we still have a valid session
            ZMMessages::instance()->warn(sprintf(_zm('You are not allowed to access the page with id: <em>%s</em>'), $request->getRequestId()));
        }
    }

    /**
     * Add <em>currentLanguage</em> to all views.
     */
    public function onViewStart($event) {
        $request = $event->get('request');
        $view = $event->get('view');
        $view->setVar('currentLanguage', $request->getSelectedLanguage());
        $view->setVar('currentEditor', $this->getCurrentEditor($request));
        $view->setVar('buttonClasses', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only');
    }

    /**
     * Get instance of the current editor.
     *
     * @param ZMRequest request The current request.
     * @return ZMTextAreaFormWidget A text editor widget.
     */
    protected function getCurrentEditor($request) {
        $user = $request->getUser();
        if (null == $user || null == ($editor = ZMAdminUserPrefs::instance()->getPrefForName($user->getId(), 'wysiwygEditor'))) {
            $editor = ZMSettings::get('apps.store.admin.defaultEditor', 'ZMTextAreaFormWidget');
        }

        if (null != ($obj = ZMBeanUtils::getBean($editor))) {
            return $obj;
        }

        return ZMBeanUtils::getBean('ZMTextAreaFormWidget');
    }

    /**
     * Init locale.
     */
    public function onInitDone($event) {
        $request = $event->get('request');
        $user = $request->getUser();
        if (null != $user && null != ($uiLocale = ZMAdminUserPrefs::instance()->getPrefForName($user->getId(), 'uiLocale'))) {
            ZMLocales::instance()->getLocale(true, $uiLocale);
        }
    }

    /**
     * Load zen cart style define configs
     *
     * @todo: remove and load individual values as required
     */
    public function onBootstrapDone($event) {
        //** load all config values if not set **//
        if (!defined('STORE_NAME')) {
            foreach (ZMConfig::instance()->loadAll() as $key => $value) {
                define($key, $value);
            }
            require_once ZMRuntime::getInstallationPath() . '/shared/defaults.php';
            // set shared defaults again as some settings depend on zencart settings...
            ZMSettings::addAll(zm_get_default_settings());
        }
    }

}
