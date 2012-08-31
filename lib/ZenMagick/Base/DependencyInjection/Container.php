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
namespace ZenMagick\Base\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Container as BaseContainer;

/**
 * Temporary subclass of the container to keep code
 * that relies on an automatic container (usually via
 * ZMObject) working.
 */
class Container extends BaseContainer {

    /**
     * {@inheritDoc}
     */
    public function get($id, $invalidBehavior=self::EXCEPTION_ON_INVALID_REFERENCE) {
        $service = parent::get($id, $invalidBehavior);

        if (null == $service && self::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {
            throw new \InvalidArgumentException(sprintf('The service "%s" does not exist.', $id));
        }

        if ($service instanceof ContainerAwareInterface) {
            $service->setContainer($this);
        }

        return $service;
    }
}
