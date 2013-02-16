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

namespace ZenMagick\ZenCartBundle\Compat;

class QueryFactoryMeta
{
    const TYPE_PATTERN = '/(?<type>\w+)($|\((?<max_length>(\d+|(.*)))\))/';

    public $max_length;
    public $type;

    /**
     * Constructor
     *
     * @param string $type
     */
    public function __construct($type)
    {
        $typeInfo = $this->parseType($type);
        $this->type = $typeInfo['type'];
        $this->max_length = $typeInfo['max_length'];
    }

    /**
     * Get max_length and type from column type definition.
     *
     * @param string $type
     *
     * @return array array with type and max_length
     */
    private function parseType($type)
    {
        preg_match(self::TYPE_PATTERN, $type, $matches);
        if (!isset($matches['max_length'])) {
            $matches['max_length'] = '';
        }

        return $matches;
    }
}
