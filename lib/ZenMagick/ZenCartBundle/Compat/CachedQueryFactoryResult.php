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

/**
 * Wrapper around a DBAL provided statement object
 *
 * This class stores the entire result so it can be
 * cached by QueryCacheProfile.
 */
class CachedQueryFactoryResult extends AbstractQueryFactoryResult
{
    private $results;
    private $rowCount;

    /**
     * {inheritDoc}
     */
    public function __construct($stmt)
    {
        $this->stmt = $stmt;
        $this->results = $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->stmt->closeCursor();
        $this->rowCount = 0;
    }

    /**
     * Get the number of records in the result set.
     *
     * @return int number of rows
     */
    public function RecordCount()
    {
        return count($this->results);
    }

    /**
     * {@inheritDoc}
     */
    public function MoveNext()
    {
        $rowCount = $this->rowCount;
        if (isset($this->results[$rowCount])) {
            $this->fields = $this->results[$this->rowCount];
            $this->rowCount++;

            return;
        }
        $this->EOF = true;
    }

    /**
     * {@inheritDoc}
     */
    public function Move($row)
    {
        $row -= 1;
        $this->rowCount = $row;
        $this->MoveNext();
    }
}
