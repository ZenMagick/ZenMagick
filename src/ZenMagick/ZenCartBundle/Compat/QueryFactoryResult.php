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
class QueryFactoryResult extends AbstractQueryFactoryResult
{
    /**
     * {@inheritDoc}
     */
    public function RecordCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function Move($row)
    {
        $row -= 1;
        while (0 < $row) {
            $this->MoveNext();
            if ($this->EOF) break;
        }
    }
}
