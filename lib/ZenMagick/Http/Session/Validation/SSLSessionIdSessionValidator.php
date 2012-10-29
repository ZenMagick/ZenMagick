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
namespace ZenMagick\Http\Session\Validation;

use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Request;
use ZenMagick\Http\Session\Session;
use ZenMagick\Http\Session\SessionValidator;

/**
 * SSL Session Id validator.
 *
 * @author Johnny Robeson <johnny@localmomentum.net>
 */
class SSLSessionIdSessionValidator extends ZMObject implements SessionValidator
{
    /**
     * Name of the user agent session key.
     */
    const SESSION_SSL_SESSION_ID_KEY = 'sslSessionId';

    private $enabled = false;

    /**
     * Enable/disable validation.
     *
     * @param boolean state The new state.
     */
    public function setEnabled($state)
    {
        $this->enabled = $state;
    }

    /**
     * {@inheritDoc}
     *
     * @todo move to request
     */
    public function isValidSession(Request $request, Session $session)
    {
        $valid = true;
        if ($this->enabled && $request->isSecure()) {
            $sslSessionId = $request->server->get('SSL_SESSION_ID');
            if (null == ($sessionSslSessionId = $session->get(self::SESSION_VALIDATOR_NAMESPACE.'.'.self::SESSION_SSL_SESSION_ID_KEY))) {
                $session->set(self::SESSION_VALIDATOR_NAMESPACE.'.'.self::SESSION_SSL_SESSION_ID_KEY, $sslSessionId);
            } else {
                $valid = $sslSessionId == $sessionSslSessionId;
            }
        }

        return $valid;
    }
}
