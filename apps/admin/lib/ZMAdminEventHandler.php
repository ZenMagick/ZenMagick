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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Custom admin event handler for various things.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin
 */
class ZMAdminEventHandler extends ZMObject {

    /**
     * Display message about invalid/insufficient credentional
     */
    public function onInsufficientCredentials($event) {
        $request = $event->get('request');
        if (null != $request->getUser()) {
            // only if we still have a valid session
            $this->container->get('messageService')->warn(sprintf(_zm('You are not allowed to access the page with id: <em>%s</em>'), $request->getRequestId()));
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
        $view->setVar('adminMenu', $this->container->get('adminMenu'));

        // no layout for invoice/packaging slip
        if ('zc_admin' == $request->getRequestId() && in_array($request->getParameter('zpid'), Runtime::getSettings()->get('apps.store.zencart.skipLayout', array()))) {
            $view->setLayout(null);
        }
    }

    /**
     * Get instance of the current editor.
     *
     * @param ZMRequest request The current request.
     * @return ZMTextAreaFormWidget A text editor widget.
     */
    protected function getCurrentEditor($request) {
        $user = $request->getUser();
        if (null == $user || null == ($editor = $this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'wysiwygEditor'))) {
            $editor = ZMSettings::get('apps.store.admin.defaultEditor', 'plainEditorWidget');
        }

        return $this->container->get($editor);
        if (null != ($obj = $this->container->get($editor))) {
            return $obj;
        }

        return $this->container->get('plainEditorWidget');
    }

    /**
     * Init locale.
     */
    public function onInitDone($event) {
        $request = $event->get('request');
        $user = $request->getUser();
        if (null != $user && null != ($uiLocale = $this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'uiLocale'))) {
            $this->container->get('localeService')->getLocale(true, $uiLocale);
        }
    }

    /**
     * Load themes.
     */
    public function onInitRequest($event) {
        $request = $event->get('request');
        $language = $request->getSession()->getLanguage();
        $theme = $this->container->get('themeService')->initThemes($language);
        $args = array_merge($event->all(), array('theme' => $theme, 'themeId' => $theme->getId()));
        //Runtime::getEventDispatcher()->dispatch('theme_resolved', new Event($this, $args));
    }

}
