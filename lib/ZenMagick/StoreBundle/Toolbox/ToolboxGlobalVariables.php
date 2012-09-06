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
namespace ZenMagick\StoreBundle\Toolbox;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Beans;
use ZenMagick\Http\Toolbox\ToolboxTool;

/**
 * Impersonate FrameworkBundle's Templating\GlobalVariables
 *
 * @see Symfony\Bundle\FrameworkBundle\
 *
 */
class ToolboxGlobalVariables extends ToolboxTool {

    /**
     * Returns the current user.
     *
     * @return mixed|void
     *
     */
    public function getUser()
    {
        $user = $this->getRequest()->getAccount();
        if (!is_object($user)) {
            return;
        }

        return $user;
    }

    /**
     * Returns the current session.
     *
     */
    public function getSession()
    {
        if ($request = $this->getRequest()) {
            return $request->getSession();
        }
    }

    /**
     * Returns the current app environment.
     *
     * @return string The current environment string (e.g 'dev')
     */
    public function getEnvironment()
    {
        return $this->container->get('kernel')->getEnvironment();
    }
}
