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

/**
 * Dependency injection container.
 *
 * <p>Based on the <em>symfony2</em> dependency injection component.</p>
 *
 * @author DerManoMann
 * @package zenmagick.base.ioc
 */
class Container extends \Symfony\Component\DependencyInjection\ContainerBuilder {

    /**
     * {@inheritDoc}
     */
    public function get($id, $invalidBehavior=self::EXCEPTION_ON_INVALID_REFERENCE) {
        if ($this->has($id)) {
            return parent::get($id, $invalidBehavior);
        }

        // try to default to the id as class name
        if (ClassLoader::classExists($id)) {
            return new $id();
        }

        if (self::EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior) {
            throw new \InvalidArgumentException(sprintf('The service "%s" does not exist.', $id));
        }
    }

}
