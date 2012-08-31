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
namespace ZenMagick\Base\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Set up a tag service that can be used instead of <code>ContainerBuilder::findTaggedServiceIds()</code>.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ConfigureContainerTagServicePass implements CompilerPassInterface {

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container) {
        $tags = array();
        foreach ($container->getDefinitions() as $id => $definition) {
            $tags[$id] = $definition->getTags();
        }

        $container->setDefinition('containerTagService', new Definition(
            'ZenMagick\Base\DependencyInjection\Tags\ContainerTagService',
            array($tags)
        ));
    }

}
