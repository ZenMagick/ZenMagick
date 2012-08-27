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
namespace zenmagick\base\dependencyInjection\loader;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Yaml loader for context dependent container configuration.
 *
 * <p>Based on the <em>symfony2</em> dependency injection component.</p>
 *
 *
 */
class ContextYamlLoader extends YamlFileLoader {

    /**
     * {@inheritDoc}
     *
     * Ignores everything but 'context'
     */
    public function loadFile($file) {
        $context = $this->container->getParameter('kernel.context');
        $config = array();
        $yaml = Yaml::parse($file);
        foreach ($yaml as $key => $data) {
            if ('meta' == $key) continue;
            // context key
            if (Runtime::isContextMatch($key, $context)) {
                $config = Toolbox::arrayMergeRecursive($config, $data);
            }
        }
die(var_dump(__FILE__, $yaml));
        return $this->validate($config, $file);
    }

    public function supports($resource, $type = null) {
        return is_string($resource) && 'yaml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}

