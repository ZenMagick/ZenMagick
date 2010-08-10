<?php
/*
 * ZenMagick - Smart e-commerce
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


/**
 * Request controller for updating own admin user details.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMUpdateUserController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $user = $request->getUser();
        $widgets = array();
        $currentEditor = ZMAdminUserPrefs::instance()->getPrefForName($user->getId(), 'wysiwygEditor');
        $widgets[] = ZMBeanUtils::getBean('EditorSelectFormWidget#title='._zm('Preferred Editor').'&value='.$currentEditor.'&name=wysiwygEditor');
        return array('widgets' => $widgets);
    }

    /**
     * Process prefs
     *
     * @param ZMRequest request The current request.
     */
    protected function processPrefs($request) {
        $user = $request->getUser();
        $viewData = $this->getViewData($request);
        $widgets = $viewData['widgets'];
        foreach ($widgets as $widget) {
            $name = $widget->getName();
            ZMAdminUserPrefs::instance()->setPrefForName($user->getId(), $name, $request->getParameter($name));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFormData($request) {
        $updateUser = parent::getFormData($request);
        if (!$this->isFormSubmit($request)) {
            // prepopulate with current data
            $user = $request->getUser();
            $updateUser->setEmail($user->getEmail());
            $updateUser->setName($user->getName());
        }
        return $updateUser;
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $user = $request->getUser();
        $updateUser = $this->getFormData($request);
        // assume validation is already done...

        // allow pref changes without password
        $this->processPrefs($request);

        if ($request->handleDemo()) {
            return $this->findView('success');
        }

        // validate password
        if (!ZMAuthenticationManager::instance()->validatePassword($updateUser->getCurrentPassword(), $user->getPassword())) {
            ZMMessages::instance()->error(_zm('Sorry, the entered password is not valid.'));
            return $this->findView();
        }
        $user->setName($updateUser->getName());
        $user->setEmail($updateUser->getEmail());
        $newPassword = $updateUser->getNewPassword();
        if (!empty($newPassword)) {
            $user->setPassword(ZMAuthenticationManager::instance()->encryptPassword($newPassword));
        }
        ZMAdminUsers::instance()->updateUser($user);

        // report success
        ZMMessages::instance()->success(_zm('Details updated.'));

        return $this->findView('success');
    }

}
