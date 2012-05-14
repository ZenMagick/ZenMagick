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
use zenmagick\base\classloader\ClassLoader;
use zenmagick\base\dependencyInjection\parameterBag\SettingsParameterBag;
use zenmagick\base\dependencyInjection\compiler\ResolveMergeDefinitionsPass;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Dependency injection container.
 *
 * <p>Based on the <em>symfony2</em> dependency injection component.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Container extends ContainerBuilder {

    /**
     * Create new instance.
     */
    public function __construct(ParameterBagInterface $parameterBag=null) {
        parent::__construct(null == $parameterBag? new SettingsParameterBag() : new SettingsParameterBag($parameterBag->all()));
        $this->getCompilerPassConfig()->addPass(new ResolveMergeDefinitionsPass());
    }

    /**
     * {@inheritDoc}
     */
    public function get($id, $invalidBehavior=self::EXCEPTION_ON_INVALID_REFERENCE) {
        $service = null;
        if ($this->has($id)) {
            $service = parent::get($id, $invalidBehavior);
        } else if (ClassLoader::classExists($id) && class_exists($id)) {
            // try to default to the id as class name (with scope prototype)
            $service = new $id();
        } else if ('Z' == $id[0] && 'M' == $id[1]) {
            // possibly old class name that now exists as service id without the prefix
            $npid = substr($id, 2);
            if ($this->has($npid)) {
                $service = parent::get($npid, $invalidBehavior);
            }
        }

        if (null != $service && $service instanceof ContainerAwareInterface) {
            $service->setContainer($this);
        }

        if (null == $service && self::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {
            throw new \InvalidArgumentException(sprintf('The service "%s" does not exist.', $id));
        }

        return $service;
    }

    /**
     * {@inheritDoc}
     */
    protected function createService(Definition $definition, $id){
        $service = parent::createService($definition, $id);
        if (null != $service && $service instanceof ContainerAwareInterface) {
            $service->setContainer($this);
        }
        return $service;
    }

    /**
     * Get a singleton service.
     *
     * <p>This method keeps its own map of references because instances returned by <code>get(..)</code> might be of
     * scope <em>prototype</em>.</p>
     *
     * @param string id The service id.
     * @param  int invalidBehavior The behavior when the service does not exist.
     * @return mixed The service instance.
     * @deprecated
     */
    public function getService($id, $invalidBehavior=self::EXCEPTION_ON_INVALID_REFERENCE) {
        throw new ZMException('method not supported any more; id = '.$id);
    }

}
