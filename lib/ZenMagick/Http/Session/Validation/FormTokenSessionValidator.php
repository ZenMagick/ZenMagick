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
namespace ZenMagick\http\session\validation;

use ZenMagick\Base\ZMObject;
use ZenMagick\http\Request;
use ZenMagick\http\session\Session;
use ZenMagick\http\session\SessionValidator;

/**
 * Form token validator.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class FormTokenSessionValidator extends ZMObject implements SessionValidator {
    /**
     * Name of the session token form field.
     */
    const SESSION_TOKEN_NAME = 'securityToken';

    private $requestIds;

    /**
     * Set a list of request ids to be validated.
     *
     * @param array requestIds The request ids to validate.
     */
    public function setRequestIds(array $requestIds) {
        $this->requestIds = $requestIds;
    }

    /**
     * Check if this request needs validation at all.
     *
     * <p>This default implementation will validate <em>POST</em> requests only.
     */
    protected function qualifies(Request $request) {
        return 'POST' == $request->getMethod();
    }

    /**
     * {@inheritDoc}
     */
    public function isValidSession(Request $request, Session $session) {
        $valid = true;
        if ($this->qualifies($request) && in_array($request->getRequestId(), $this->requestIds)) {
            $valid = false;
            if (null != ($token = $request->request->get(self::SESSION_TOKEN_NAME))) {
                $valid = ($session->getToken() == $token);
            }
        }
        return $valid;
    }

}
