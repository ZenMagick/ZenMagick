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
namespace zenmagick\http\templating;

use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Twig_LoaderInterface;
use zenmagick\http\view\ResourceResolver;
use zenmagick\http\view\View;


/**
 * A resource resolver loader for twig.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ResourceResolverTwigLoader implements Twig_LoaderInterface {
    protected $resourceResolver;


    /**
     * Create new instance.
     *
     * @param ResourceResolver resourceResolver The resource resolver used to resolve templates.
     */
    public function __construct(ResourceResolver $resourceResolver) {
        $this->resourceResolver = $resourceResolver;
    }


    /**
     * {@inheritDoc}
     */
    public function getSource($name) {
        if (!$this->resourceResolver->exists($name)) {
            throw new \InvalidArgumentException(sprintf('not found: %s', $name));
        }

        return file_get_contents($this->resourceResolver->findResource($name, View::TEMPLATE));
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey($name) {
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function isFresh($name, $time) {
        return true;
    }

}
