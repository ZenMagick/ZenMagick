<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\base\dependencyInjection\loader;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

/**
 * Yaml loader for dependency injection container definitions.
 *
 * <p>Based on the <em>symfony2</em> dependency injection component.</p>
 *
 * <p>This class allows to load YAML strings into the container by <em>abusing</em> the <code>FileLocator</code> and a few other
 * bits of the symfony DI loader system.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.base.dependencyInjection.loader
 */
class YamlLoader extends YamlFileLoader {

    /**
     * {@inheritDoc}
     */
    public function __construct(ContainerBuilder $container, FileLocator $locator=null) {
        parent::__construct($container, new EchoFileLocator(dirname(__FILE__)));
    }

    /**
     * {@inheritDoc}
     */
    protected function addResource($path) {
        // nothing
    }

    /**
     * {@inheritDoc}
     *
     * <p>Just return the given yaml.</p>
     */
    public function loadFile($yaml) {
        return $yaml;
    }

    /**
     * {@inheritDoc}
     */
    public function findFile($file) {
        return $file;
    }

    /**
     * {@inheritDoc}
     *
     * <p>Accept <code>array</code> resources with a '<em>services</em>' key.</p>
     */
    public function supports($resource, $type=null) {
        return is_array($resource) && array_key_exists('services', $resource);
    }

}

/**
 * Fake class to allow loading yaml from strings.
 */
class EchoFileLocator extends FileLocator {

    public function locate($name, $currentPath=null, $first=true) {
        return $name;
    }
}
