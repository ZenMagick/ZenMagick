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
namespace ZenMagick\AdminBundle\EventListener;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\View\TemplateView;

use ZenMagick\StoreBundle\Menu\MenuElement;
use ZenMagick\StoreBundle\Menu\MenuLoader;


/**
 * Custom admin event handler for various things.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AdminListener extends ZMObject {
    const DEFAULT_EDITOR_SERVICE_ID = 'plainEditorWidget';

    /**
     * Display message about invalid/insufficient credentional
     */
    public function onInsufficientCredentials($event) {
        $request = $event->getArgument('request');
        if (null != $request->getAccount()) {
            // only if we still have a valid session
            $request->getSession()->getFlashBag()->warn(sprintf(_zm('You are not allowed to access the page with id: <em>%s</em>'), $request->getRequestId()));
        }
    }

    /**
     * Add <em>currentLanguage</em> to all views.
     */
    public function onViewStart($event) {
        $request = $event->getArgument('request');
        $view = $event->getArgument('view');

        $this->initMenu();
        $adminMenu = $this->container->get('adminMenu');
        $legacyConfig = $adminMenu->getElement('configuration-legacy');
        $configGroups = $this->container->get('configService')->getConfigGroups();
        foreach ($configGroups as $group) {
            if ($group->isVisible()) {
                $id = strtolower($group->getName());
                $id = str_replace(' ', '', $id);
                $id = str_replace('/', '-', $id);
                $element = new MenuElement($id, $group->getName());
                $element->setRequestId('legacy_config');
                $element->setParams('groupId='.$group->getId());
                $legacyConfig->addChild($element);
            }
        }

        if ($view instanceof TemplateView) {
            $view->setVariable('currentLanguage', $request->getSelectedLanguage());
            $view->setVariable('currentEditor', $this->getCurrentEditor($request));
            $view->setVariable('buttonClasses', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only');
            $view->setVariable('adminMenu', $this->container->get('adminMenu'));
        }
    }

    /**
     * Get instance of the current editor.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return TextAreaFormWidget A text editor widget.
     */
    protected function getCurrentEditor($request) {
        $user = $request->getAccount();
        if (null == $user || null == ($editorId = $this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'wysiwygEditor'))) {
            $editorId = self::DEFAULT_EDITOR_SERVICE_ID;
        }

        if (!$this->container->has($editorId)) {
            $editorId = self::DEFAULT_EDITOR_SERVICE_ID;
        }

        return $this->container->get($editorId);
    }

    /**
     * Init menu.
     */
    public function initMenu() {
        $settingsService = $this->container->get('settingsService');

        $menuLoader = new MenuLoader();
        $adminMenu = $this->container->get('adminMenu');
        $menus = $settingsService->get('apps.store.admin.menus');
        // @todo support relative and absolute paths (and also placeholder paths)
        foreach ($menus as $menu) {
            $menuLoader->load(Runtime::getInstallationPath().'/'.$menu, $adminMenu);
        }
        $contextConfigLoader = $this->container->get('contextConfigLoader');
        foreach ($contextConfigLoader->getMenus() as $menu) {
            $menuLoader->load($menu, $adminMenu);
        }

        if ($settingsService->get('zenmagick.http.request.secure')) {
            // make all of ZM admin secure
            $settingsService->set('zenmagick.http.request.allSecure', true);
        }
    }

    /**
     * Final init.
     */
    public function onContainerReady($event) {
        $request = $event->getArgument('request');

        // @todo languages setting not really supposed to be here
        $session = $request->getSession();
        if ($request->query->has('languageId')) {
            $session->set('languages_id', $request->query->get('languageId'));
        }
        $user = $request->getAccount();
        if (null != $user && null != ($uiLocale = $this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'uiLocale'))) {
            $request->setLocale($uiLocale);
        }

    }
}
