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
namespace zenmagick\http\templating;

use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\TemplateReferenceInterface;
use zenmagick\http\view\ResourceResolver;
use zenmagick\http\view\View;


/**
 * A resource resolver loader.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ResourceResolverLoader implements LoaderInterface {
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
    public function load(TemplateReferenceInterface $template) {
        if (!$this->resourceResolver->exists($template)) {
            return false;
        }

        $filename = $this->resourceResolver->findResource($template, View::TEMPLATE);
        return new FileStorage($filename);
    }

    /**
     * {@inheritDoc}
     */
    public function isFresh(TemplateReferenceInterface $template, $time) {
        return true;
    }

}
