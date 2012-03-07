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
?>
<?php


/**
 * A source of result list results.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.resultlist
 */
interface ZMResultSource {

    /**
     * Set the corresponding result list.
     *
     * @param ZMResultList resultList The *parent* result list.
     */
    public function setResultList($resultList);

    /**
     * Get the results.
     *
     * @param boolean reload Optional reload flag; default is <code>false</code>.
     *
     * @return array List of results.
     */
    public function getResults($reload=false);

    /**
     * Get the class name of the results.
     *
     * @return string The class name of the results.
     */
    public function getResultClass();

    /**
     * Total number of results.
     *
     * @return int The total number if results.
     */
    public function getTotalNumberOfResults();

    /**
     * Indicates whether the returned results are final or not.
     *
     * <p>Sources may opt to filter and sort results already (for example for performance
     * reasons. In that case, no further action is required by the result list.</p>
     *
     * <p>As a side effect, the method <code>getAllResults()</code> may then return the same
     * results (number and sort order) as <code>getResults()</code>, even if the source
     * reports more than one page.</p>
     *
     * @return boolean <code>true</code> if the result source is handling all sorting and filtering, too.
     */
    public function isFinal();

}
