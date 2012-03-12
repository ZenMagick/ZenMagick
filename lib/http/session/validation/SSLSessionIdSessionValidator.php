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
namespace zenmagick\http\session\validation;

use ZMRequest;
use zenmagick\base\ZMObject;
use zenmagick\http\session\Session;
use zenmagick\http\session\SessionValidator;

/**
 * SSL Session Id validator.
 *
 * @author Johnny Robeson <johnny@localmomentum.net>
 */
class SSLSessionIdSessionValidator extends ZMObject implements SessionValidator {
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
    public function setEnabled($state) {
        $this->enabled = $state;
    }

    /**
     * {@inheritDoc}
     *
     * @todo move to request
     */
    public function isValidSession(ZMRequest $request, Session $session) {
        $valid = true;
        if ($this->enabled && $request->isSecure()) {
            $sslSessionId = array_key_exists('SSL_SESSION_ID', $_SERVER) ? $_SERVER['SSL_SESSION_ID'] : null;
            if (null == ($sessionSslSessionId = $session->getValue(self::SESSION_SSL_SESSION_ID_KEY, self::SESSION_VALIDATOR_NAMESPACE))) {
                $session->setValue(self::SESSION_SSL_SESSION_ID_KEY, $sslSessionId, self::SESSION_VALIDATOR_NAMESPACE);
            } else {
                $valid = $sslSessionId == $sessionSslSessionId;
            }
        }
        return $valid;
    }
}
