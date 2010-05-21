<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Ajax response interface.
 *
 * <p>De-couples the actually returned structure from the business logic generating the data load.</p>
 *
 * <p>A response as defined by this interface defines the following high level data types:</p>
 * <dl>
 *  <dt>messages</dt>
 *  <dd>A variable number of messages. Messages are grouped by type.</dd>
 *  <dt>status</dt>
 *  <dt>The overall status of the response with <code>true</code> indicating success and <code>false</code> failure.</dt>
 *  <dd>properties</dd>
 *  <dd>Optional place to store supporting <em>name/value<em> pairs. For simple calls this could be
 *   used instead of the <em>data</em> element.</dd>
 *  <dd>data</dd>
 *  <dd>The actual data returned.</dd>
 * </dl>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.ajax
 * @version $Id$
 */
interface ZMAjaxResponse {

    /**
     * Add a message for the given type.
     *
     * @param string msg The message.
     * @param string type The message type.
     */
    public function addMessage($msg, $type);

    /**
     * Set the status.
     *
     * @param boolean status The status.
     */
    public function setStatus($status);

    /**
     * Get the status.
     *
     * @return boolean The status.
     */
    public function getStatus();

    /**
     * Set the data.
     *
     * @param mixed data The data.
     */
    public function setData($data);

    /**
     * Create the response.
     *
     * @return string The response.
     */
    public functiong createResponse();

}
