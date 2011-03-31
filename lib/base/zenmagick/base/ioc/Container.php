<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
namespace zenmagick\base\ioc;

use zenmagick\base\ClassLoader;
use zenmagick\base\ioc\extension\ZenMagickExtension;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Dependency injection container.
 *
 * <p>Based on the <em>symfony2</em> dependency injection component.</p>
 *
 * @author DerManoMann
 * @package zenmagick.base.ioc
 */
class Container extends \Symfony\Component\DependencyInjection\ContainerBuilder {
    private $services_;

    /**
     * Create new instance.
     */
    public function __construct(ParameterBagInterface $parameterBag=null) {
        parent::__construct($parameterBag);
        $this->services_ = array();
    }

    /**
     * {@inheritDoc}
     */
    public function get($id, $invalidBehavior=self::EXCEPTION_ON_INVALID_REFERENCE) {
        if ($this->has($id)) {
            return parent::get($id, $invalidBehavior);
        }

        // try to default to the id as class name (with scope prototype)
        if (ClassLoader::classExists($id) && class_exists($id)) {
            return new $id();
        }

        //TODO: remove
        if (null != ($realid = \ZMLoader::resolve($id))&& class_exists($realid)) {
            return new $realid();
        }
        if (null != ($realid = \ZMLoader::resolve('ZM'.$id))&& class_exists($realid)) {
            return new $realid();
        }
        if (self::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {
            throw new \InvalidArgumentException(sprintf('The service "%s" does not exist.', $id));
        }
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
     */
    public function getService($id, $invalidBehavior=self::EXCEPTION_ON_INVALID_REFERENCE) {
        if (!array_key_exists($id, $this->services_)) {
            $this->services_[$id] = $this->get($id, $invalidBehavior);
        }

        if (array_key_exists($id, $this->services_)) {
            return $this->services_[$id];
        }

        return null;
    }

}
