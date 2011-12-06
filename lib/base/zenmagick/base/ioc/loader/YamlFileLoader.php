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
namespace zenmagick\base\ioc\loader;

use zenmagick\base\Toolbox;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\FileLocator;

/**
 * Yaml file loader for IoC definitions.
 *
 * <p>Based on the <em>symfony2</em> dependency injection component.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.base.ioc.loader
 */
class YamlFileLoader extends \Symfony\Component\DependencyInjection\Loader\YamlFileLoader {

    /**
     * {@inheritDoc}
     */
    public function __construct(ContainerBuilder $container, FileLocator $locator) {
        parent::__construct($container, $locator);
        // allow XML
        $this->setResolver(new LoaderResolver(array(new XmlFileLoader($container, $locator))));
    }


    /**
     * {@inheritDoc}
     */
    protected function loadFile($file) {
        $c = $this->validate(Toolbox::loadWithEnv($file), $file);
        return $c;
    }

    /**
     * {@inheritDoc}
     *
     * <p>Check for <em>.yml</em> <strong>and</strong> <em>.yaml</em> file extension.</p>
     */
    public function supports($resource, $type=null) {
        return parent::supports($resource) || (is_string($resource) && 'yaml' === pathinfo($resource, PATHINFO_EXTENSION));
    }

}
