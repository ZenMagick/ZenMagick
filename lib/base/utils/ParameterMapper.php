<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright 2006-2007 by Dick Munroe, Cottage Software Works, Inc.
 * Copyright (C) 2011 zenmagick.org
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
namespace zenmagick\base\utils;

/**
 * Interface to implement for <code>Executor</code> method parameter mapping.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
interface ParameterMapper {

    /**
     * Map parameter.
     *
     * @param mixed callback The callback.
     * @param array parameter The given parameter.
     */
    public function mapParameter($callback, array $parameter);

}
