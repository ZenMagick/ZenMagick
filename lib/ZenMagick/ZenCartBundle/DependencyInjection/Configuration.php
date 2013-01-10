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

namespace ZenMagick\ZenCartBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        // @todo we can autodetect!
        $zcRoot = realpath(dirname(dirname(dirname(dirname(dirname(__DIR__))))));

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zencart');
        $rootNode
            ->children()
                ->scalarNode('root_dir')->defaultValue($zcRoot)->end()
                ->scalarNode('admin_dir')->defaultNull()->end()
                ->arrayNode('admin')
                    ->children()
                        ->arrayNode('hide_layout')
                            ->performNoDeepMerging()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('storefront')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable_counter')->defaultFalse()->end()
                    ->end()
                ->end()
        ->end();

        return $treeBuilder;
    }

}
