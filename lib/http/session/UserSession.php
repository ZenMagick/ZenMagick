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
namespace zenmagick\http\session;

use zenmagick\base\ZMObject;


/**
 * User session.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class UserSession extends ZMObject {
    protected $session;


    /**
     * Set the session.
     *
     * @param Session session The session.
     */
    public function setSession($session) {
        $this->session = $session;
    }

    /**
     * Get the locale code.
     *
     * @return string The locale code or <code>null</code>.
     */
    public function getLocaleCode() {
        return $this->session->getValue('uiLocale');
    }

}
