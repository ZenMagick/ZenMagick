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
namespace ZenMagick\ZenMagickBundle\Tests;

/**
 * ZenMagick test case base class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZenMagickTestCase extends \PHPUnit_Framework_TestCase
{
    private $app;

    /**
     * Get $app.
     *
     * @param boolean $fresh Optional flag to force a fresh instance; default is <code>false</code>.
     */
    protected function getApp($fresh = false)
    {
        if (!$this->app || $fresh) {
            $appDir = realpath(__DIR__.'/../../../../app');
            require_once $appDir.'/bootstrap.php.cache';
            require_once $appDir.'/AppKernel.php';
            $this->app = new \AppKernel('test', false, 'storefront');
            $this->app->loadClassCache();
            $this->app->boot();
        }

        return $this->app;
    }

    /**
     * Get a service from the container of the current $app.
     *
     * @param string serviceId The service id.
     * @return mixed The service.
     */
    public function get($serviceId)
    {
        return $this->getApp()->getContainer()->get($serviceId);
    }

}
