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
namespace ZenMagick\apps\admin\controller;

use ZenMagick\Base\Beans;
use Symfony\Component\Locale\Locale;

/**
 * Request controller for updating own admin user details.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class UpdateUserController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $user = $this->getUser();
        $widgets = array();

        // WYSIWYG
        $currentEditor = $this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'wysiwygEditor');
        $widgets[] = Beans::getBean('EditorSelectFormWidget#title='._zm('Preferred Editor').'&value='.$currentEditor.'&name=wysiwygEditor');

        // uiLocale  (@todo fold into widget)
        $currentLocale = $this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'uiLocale');
        $locales = Locale::getDisplayLocales($currentLocale);
        $uiLocaleWidget = Beans::getBean('selectFormWidget#name=uiLocale&title='._zm('Admin Locale').'&value='.$currentLocale);
        foreach ($locales as $locale => $name) { // @todo decide whether to show the localized names here at all.
            $uiLocaleWidget->addOption('('.$locale.') '.$name, $locale);
        }
        $uiLocaleWidget->setValue($this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'uiLocale'));
        $widgets[] = $uiLocaleWidget;

        return array('widgets' => $widgets);
    }

    /**
     * Process prefs
     *
     * @param ZenMagick\Http\Request request The current request.
     */
    protected function processPrefs($request) {
        $user = $this->getUser();
        $viewData = $this->getViewData($request);
        $widgets = $viewData['widgets'];
        foreach ($widgets as $widget) {
            $name = $widget->getName();
            $this->container->get('adminUserPrefService')->setPrefForName($user->getId(), $name, $request->request->get($name));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFormData($request, $formDef=null, $formId=null) {
        $updateUser = parent::getFormData($request, $formDef, $formId);
        if (!$this->isFormSubmit($request)) {
            // pre-populate with current data
            $user = $this->getUser();
            $updateUser->setEmail($user->getEmail());
            $updateUser->setName($user->getName());
        }
        return $updateUser;
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $user = $this->getUser();
        $updateUser = $this->getFormData($request);
        // assume validation is already done...

        // allow pref changes without password
        $this->processPrefs($request);

        if ($this->handleDemo()) {
            return $this->findView('success');
        }

        $authenticationManager = $this->container->get('authenticationManager');
        // validate password
        if (!$authenticationManager->validatePassword($updateUser->getCurrentPassword(), $user->getPassword())) {
            $this->messageService->error(_zm('Sorry, the entered password is not valid.'));
            return $this->findView();
        }
        $user->setName($updateUser->getName());
        $user->setEmail($updateUser->getEmail());
        $newPassword = $updateUser->getNewPassword();
        if (!empty($newPassword)) {
            $user->setPassword($authenticationManager->encryptPassword($newPassword));
        }
        $this->container->get('adminUserService')->updateUser($user);

        if (null != ($uiLocale = $this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'uiLocale'))) {
            $request->getSession()->setValue('uiLocale', $uiLocale);
        }

        // report success
        $this->messageService->success(_zm('Details updated.'));

        return $this->findView('success');
    }

}
