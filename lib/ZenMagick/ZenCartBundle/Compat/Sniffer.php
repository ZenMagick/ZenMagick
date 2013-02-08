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
 * It relies on Doctrine DBAL SchemaManager.
 *
 */
class Sniffer
{
    private $conn;

    /**
     * Constructor
     *
     * @param Doctrine\DBAL\Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Check if a table exists.
     *
     * @param  string $tableName
     * @return bool
     */
    public function table_exists($tableName)
    {
        $sm = $this->conn->getSchemaManager();

        return $sm->tablesExist(array($tableName));
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
        if (!$this->table_exists($tableName)) return false;

        $sm = $this->conn->getSchemaManager();
        $tableDetails = $sm->listTableDetails($tableName);

        return $tableDetails->hasColumn($columnName);
    }

    /**
     * Get the type of the column (field)
     *
     * This uses mysql information schema table
     * to get the raw column type since that is what we
     * expect to match our $columnType against.
     *
     * @todo remove mysql specific query.
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
        $query = "SELECT COLUMN_TYPE FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $args = array(
            $this->conn->getDatabase(),
            $tableName,
            $columnName,
        );
        $type = $this->conn->fetchColumn($query, $args, 0);

        if ($type == $columnType) return true;

        return $returnFound ? $type : false;
    }
}
