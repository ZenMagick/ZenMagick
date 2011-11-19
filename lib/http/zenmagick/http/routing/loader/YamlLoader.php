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
namespace zenmagick\http\routing\loader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Routing\RouteCollection;

use zenmagick\base\config\EchoFileLocator;
use zenmagick\base\Toolbox;

/**
 * Yaml loader for routing mappings.
 *
 * <p>Assume <code>$file</code> to be the actual content.</p>
 *
 * @author DerManoMann
 * @package zenmagick.http.routing.loader
 */
class YamlLoader extends \Symfony\Component\Routing\Loader\YamlFileLoader {

    /**
     * {@inheritDoc}
     */
    public function __construct(FileLocatorInterface $locator=null) {
        parent::__construct(null != $locator ? $locator : new EchoFileLocator());
    }


    /**
     * {@inheritDoc}
     */
    public function load($file, $type=null) {
        if (empty($file)) {
            return new RouteCollection();
        }
        return parent::load($file, $type);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadYaml($path) {
        if (is_array($path)) {
            return $path;
        }
        return parent::loadYaml($path);
    }

}
