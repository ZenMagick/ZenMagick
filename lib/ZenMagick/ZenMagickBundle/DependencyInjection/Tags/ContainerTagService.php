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
namespace ZenMagick\ZenMagickBundle\DependencyInjection\Tags;

/**
 * Tag service to allow to query container tags.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ContainerTagService
{
    /**
     * Create new instance.
     *
     * @param array tags All tags; default is an empty array.
     */
    public function __construct(array $tags = array())
    {
        $this->tags = $tags;
    }

    /**
     * Set all tags.
     *
     * @param array tags All tags; default is an empty array.
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * Returns service ids for a given tag.
     *
     * @param string name The tag name.
     * @return array An array of tags.
     */
    public function findTaggedServiceIds($name)
    {
        $services = array();
        foreach ($this->tags as $id => $tags) {
            if (isset($tags[$name])) {
                $services[$id] = $tags[$name];
            }
        }

        return $services;
    }

}
