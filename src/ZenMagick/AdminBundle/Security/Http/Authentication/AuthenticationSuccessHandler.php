<?php
/*
 * ZenMagick - Another PHP framework.
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

namespace ZenMagick\AdminBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

/**
 * {@inheritDoc}
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    private $prefService;

    /**
     * Set the admin preferences service
     * @param AdminPrefService an instance of adminPrefService
     */
    public function setPrefService($prefService)
    {
        $this->prefService = $prefService;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $session = $request->getSession();
        $user = $token->getUser();
        $session->set('admin_id', $user->getId());
        if (null != ($uiLocale = $this->prefService->getPrefForName($user->getId(), 'uiLocale'))) {
            $session->set('_locale', $uiLocale);
        }

        return parent::onAuthenticationSuccess($request, $token);
    }
}
