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
namespace zenmagick\apps\store\admin\controller;


/**
 * Admin controller for login.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class LoginController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (null != $request->getUser()) {
            return $this->findView('logged-in');
        }

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $name = $request->getParameter('name');

        if (null == ($user = $this->container->get('adminUserService')->getUserForName($name))) {
            $this->messageService->error(_zm('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        $password = $request->getParameter('password');
        if (!$this->container->get('authenticationManager')->validatePassword($password, $user->getPassword())) {
            $this->messageService->error(_zm('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        $session = $request->getSession();
        $session->setValue('admin_id', $user->getId());
        if (null != ($uiLocale = $this->container->get('adminUserPrefService')->getPrefForName($user->getId(), 'uiLocale'))) {
            $session->setValue('uiLocale', $uiLocale);
        }
        $session->regenerate();

        return $this->findView('success', array(), array('url' => $request->getFollowUpUrl()));
    }

}
