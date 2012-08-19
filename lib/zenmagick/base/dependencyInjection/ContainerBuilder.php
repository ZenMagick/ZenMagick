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
namespace zenmagick\base\dependencyInjection;

use zenmagick\base\ZMException;
use zenmagick\base\dependencyInjection\parameterBag\SettingsParameterBag;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as BaseContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Dependency injection container.
 *
 * <p>Based on the <em>symfony2</em> dependency injection component.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ContainerBuilder extends BaseContainerBuilder {

    /**
     * {@inheritDoc}
     */
    public function get($id, $invalidBehavior=self::EXCEPTION_ON_INVALID_REFERENCE) {
        $service = null;
        if ($this->has($id)) {
            $service = parent::get($id, $invalidBehavior);
        } else if (class_exists($id)) {
            // try to default to the id as class name (with scope prototype)
            $service = new $id();
        }

        if (null != $service && $service instanceof ContainerAwareInterface) {
            $service->setContainer($this);
        }

        if (null == $service && self::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {
            throw new \InvalidArgumentException(sprintf('The service "%s" does not exist.', $id));
        }

        return $service;
    }

}
