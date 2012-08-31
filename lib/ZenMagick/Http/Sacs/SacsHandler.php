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
namespace ZenMagick\http\sacs;


/**
 * Interface for Sacs handler.
 *
 * <p>If a handler is indifferent to a given request, the convention is to return <code>null</code> in order to allow
 * the next configured handler to evaluate the request.</p>
 *
 * <p>The result of the first handler to return either <code>true</code> or <code>false</code> will be taken as
 * the final result by the <code>SacsManager</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface SacsHandler {

    /**
     * Get the unique handler name.
     *
     * @return string The handler name.
     */
    public function getName();

    /**
     * Evaluate the given credentials in the context of the request.
     *
     * @param ZenMagick\http\Request request The current request.
     * @param mixed credentials The user credentials.
     * @param SacsManager manager The delegating manager.
     * @return mixed Either <code>null</code> to indicate that the given request can't be handled, or
     *  either <code>true</code> for valid credentials or <code>false</code> for invalid credentials.
     */
    public function evaluate($request, $credentials, $manager);

}
