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

use Doctrine\DBAL\Connection;

/**
 * Reimplemented ZenCart Sniffer class.
 *
 * It relies on QueryFactory to get the
 * column meta in a more portable fashion
 */
class Sniffer
{
    /**
     * @var ZenMagick\ZenCartBundle\Compat\QueryFactory
     */
    private $queryFactory;

    /**
     * Constructor
     *
     * @param Doctrine\DBAL\Connection $conn
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Check if a table exists.
     *
     * @param  string $tableName
     * @return bool
     */
    public function table_exists($tableName)
    {
        $meta = $this->queryFactory->metaColumns($tableName);

        return !empty($meta);
    }

    /**
     * Check if a table has a column (field)
     *
     * @param  string $tableName
     * @param  string $columnName
     * @return bool
     */
    public function field_exists($tableName, $columnName)
    {
        return (bool) $this->queryFactory->metaColumn($tableName, $columnName);
    }

    /**
     * Get the type of the column (field)
     *
     * This uses queryFactory's metaColumn method
     * to get the raw column type since that is what we
     * expect to match our $columnType against.
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $ColumnType
     * @param bool   $returnFound return type if found
     *
     * @return string|bool
     */
    public function field_type($tableName, $columnName, $columnType, $returnFound = false)
    {
        $meta = $this->queryFactory->metaColumn($tableName, $columnName);

        if ($columnType == $meta->getSqlType()) return true;

        return $returnFound ? $meta->getSqlType() : false;
    }
}
