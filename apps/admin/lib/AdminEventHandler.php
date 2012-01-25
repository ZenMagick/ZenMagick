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
namespace zenmagick\apps\store\admin;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;
use zenmagick\http\view\TemplateView;

use zenmagick\apps\store\menu\MenuElement;
use zenmagick\apps\store\menu\MenuLoader;


/**
 * Custom admin event handler for various things.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AdminEventHandler extends ZMObject {
    const DEFAULT_EDITOR_SERVICE_ID = 'plainEditorWidget';

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
        if ($view instanceof TemplateView) {
            $view->setVariable('currentLanguage', $request->getSelectedLanguage());
            $view->setVariable('currentEditor', $this->getCurrentEditor($request));
            $view->setVariable('buttonClasses', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only');
            $view->setVariable('adminMenu', $this->container->get('adminMenu'));

            // no layout for invoice/packaging slip
            if ('zc_admin' == $request->getRequestId() && in_array($request->getParameter('zpid'), Runtime::getSettings()->get('apps.store.zencart.skipLayout', array()))) {
                $view->setLayout(null);
            }
        }
    }

    /**
     * Get instance of the current editor.
     *
     * @param ZMRequest request The current request.
     * @return TextAreaFormWidget A text editor widget.
     */
    protected function getCurrentEditor($request) {
        $user = $request->getUser();
        if (null == $user || null == ($editorId = $this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'wysiwygEditor'))) {
            $editorId = $this->container->get('settingsService')->get('apps.store.admin.defaultEditor', self::DEFAULT_EDITOR_SERVICE_ID);
        }

        if (!$this->container->has($editorId)) {
            $editorId = self::DEFAULT_EDITOR_SERVICE_ID;
        }

        return $this->container->get($editorId);
    }

    /**
     * Init menu.
     */
    public function onBootstrapDone($event) {
        $adminMenu = $this->container->get('adminMenu');

        $menuLoader = new MenuLoader();
        $menuLoader->load(Runtime::getApplicationPath().'/config/menu.yaml', $adminMenu);

        $legacyConfig = $adminMenu->getElement('configuration-legacy');
        $configGroups = $this->container->get('configService')->getConfigGroups();
        foreach ($configGroups as $group) {
            if ($group->isVisible()) {
                $id = strtolower($group->getName());
                $id = str_replace(' ', '', $id);
                $id = str_replace('/', '-', $id);
                $element = new MenuElement($id, $group->getName());
                $element->setRequestId('legacy-config');
                $element->setParams('groupId='.$group->getId());
                $legacyConfig->addChild($element);
            }
        }

        if (null != ($timeLimit = $this->container->get('configService')->getConfigValue('GLOBAL_SET_TIME_LIMIT'))) {
            set_time_limit($timeLimit->getValue());
        }
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
        $themes = $request->getParameter('themes');
        if (Toolbox::asBoolean($themes)) {
            $language = $this->container->get('languageService')->getLanguageForCode(Runtime::getSettings()->get('defaultLanguageCode'));
            $theme = $this->container->get('themeService')->initThemes($language);
            $args = array_merge($event->all(), array('theme' => $theme, 'themeId' => $theme->getId()));
            //Runtime::getEventDispatcher()->dispatch('theme_resolved', new Event($this, $args));
        }
    }

}
