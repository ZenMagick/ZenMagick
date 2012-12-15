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
 */
class QueryFactoryResult
{
    public $EOF = false;
    public $fields = array();
    private $stmt;

    /**
     * Initialize result set.
     *
     * @param object $stmt a doctrine dbal provided statement object
     */
    public function __construct($stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * Get the number of records in the result set.
     * @return int number of rows
     */
    public function RecordCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Move the pointer to the next row in the result set.
     *
     * if there are no results then then <code>$this->EOF</code> is set to true
     * and <code>$this->fields</code> is not populated.
     */
    public function MoveNext()
    {
        $result = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $this->fields = $result;
        } else {
            $this->EOF = true;
            $this->stmt->closeCursor();
        }
    }

    /**
     * Iterate over a result set that has already been randomized.
     *
     * This is different behaviour than the original class, but this should
     * be much faster since it relies on ORDER BY RAND().
     */
     public function MoveNextRandom()
    {
        $this->MoveNext();
    }

    /**
     * Move to a specified row in the result set.
     *
     * This cursor only moves forward. There is only one caller
     * (<code>zen_random_row</code>) of this method in ZenCart
     * and all callers of that are commented out as of ZenCart 1.5.0
     * so it doesn't seem worth it to implement a scrollable cursor here.
     *
     * This method also silently stops when it reaches the last result
     *
     * @param int $row. Which row to scroll to
     */
    public function Move($row)
    {
        $row -=1;
        while (0 < $row) {
            $this->MoveNext();
        }
    }
}
