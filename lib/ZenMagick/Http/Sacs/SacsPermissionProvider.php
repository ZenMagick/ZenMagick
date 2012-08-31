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
namespace ZenMagick\Http\Sacs;


/**
 * Permission provider.
 *
 * <p>Implementing this interface allows to feed permissions from custom storage into the scas manager.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface SacsPermissionProvider {

    /**
     * Get mappings.
     *
     * <p>Each returned line needs at least the following key/value pairs:</p>
     * <dl>
     *  <dt>rid</dt>
     *  <dd>The request id</dd>
     *  <dt>type</dt>
     *  <dd>The mapping type; allowed values are <em>user</em> and <em>role</em>.</dd>
     *  <dt>name</dt>
     *  <dd>The type name; depending on the role value this is assumed to be either the user or role name.</dd>
     * </dl>
     *
     * @return array List of mappings.
     */
    public function getMappings();

}
