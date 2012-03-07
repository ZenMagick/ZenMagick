<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace zenmagick\apps\store\services;

use DateTime;
use ZMRuntime;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Token service.
 *
 * @author DerManoMann
 */
class TokenService extends ZMObject {

    /**
     * Generate a random token.
     *
     * @param int length Optional length; default is <em>32</em>.
     * @return string The token.
     */
    protected function createToken($length=32) {
        static $chars	=	'0123456789abcdef';
        $max=	strlen($chars) - 1;
        $token = '';
        $name = session_name();
        for ($ii=0; $ii < $length; ++$ii) {
            $token .=	$chars[(rand(0, $max))];
        }

        return md5($token.$name);
    }

    /**
     * Get a new token for the given resource.
     *
     * @param string resource The resource.
     * @param int lifetime The lifetime of the new token (in seconds).
     * @return Token A token.
     */
    public function getNewToken($resource, $lifetime) {
        $token = Beans::getBean('zenmagick\apps\store\model\Token');
        $token->setHash($this->createToken());
        $token->setResource($resource);
        $now = new DateTime();
        $later = clone $now;
        $token->setIssued($now);
        $token->setExpires($later->setTimestamp($now->getTimestamp() + $lifetime));
        return ZMRuntime::getDatabase()->createModel(DB_PREFIX.'token', $token);
    }

    /**
     * Update a given token.
     *
     * @param Token token The token.
     * @param int lifetime The lifetime of the token (in seconds).
     */
    public function updateToken($token, $lifetime) {
        $now = new DateTime();
        $token->setExpires($now->setTimestamp(time() + $lifetime));
        ZMRuntime::getDatabase()->updateModel(DB_PREFIX.'token', $token);
    }

    /**
     * Check if <em>hash</em> is valid in context of the <em>resource</em>.
     *
     * @param string resource The resource.
     * @param string hash The hash code.
     * @param boolean expire Optional flag to invalidate a matching token; default is <code>true</code>.
     * @return Token A valid token or <code>null</code>.
     */
    public function validateHash($resource, $hash, $expire=true) {
        $sql = "SELECT * FROM " . DB_PREFIX.'token' . "
                WHERE hash = :hash AND resource = :resource AND expires >= now()";
        $token = ZMRuntime::getDatabase()->querySingle($sql, array('hash' => $hash, 'resource' => $resource), DB_PREFIX.'token', 'zenmagick\apps\store\model\Token');
        if ($expire && null !== $token) {
            $sql = "DELETE FROM " . DB_PREFIX.'token' . "
                    WHERE hash = :hash AND resource = :resource";
            ZMRuntime::getDatabase()->updateObj($sql, array('hash' => $hash, 'resource' => $resource), DB_PREFIX.'token');
        }
        return $token;
    }

    /**
     * Get all token for a given resource.
     *
     * @param string resource The resource.
     * @return array A list of <code>Token</code>.
     */
    public function getTokenForResource($resource) {
        $sql = "SELECT * FROM " . DB_PREFIX.'token' . "
                WHERE resource = :resource AND expires >= now()";
        return ZMRuntime::getDatabase()->fetchAll($sql, array('resource' => $resource), DB_PREFIX.'token', 'zenmagick\apps\store\model\Token');
    }

    /**
     * Get a token for the given hash.
     *
     * @param string hash The hash.
     * @return Token A <code>Token</code> or <code>null</code>.
     */
    public function getTokenForHash($hash) {
        $sql = "SELECT * FROM " . DB_PREFIX.'token' . "
                WHERE hash = :hash AND expires >= now()";
        $results = ZMRuntime::getDatabase()->fetchAll($sql, array('hash' => $hash), DB_PREFIX.'token', 'zenmagick\apps\store\model\Token');
        if (1 < count($results)) {
            Runtime::getLogging()->warn('duplicate token for hash: '.$hash);
            // expire all
            foreach ($results as $token) {
                $this->updateToken($token, 0);
            }
            return null;
        }
        return 1 == count($results) ? $results[0] : null;
    }

    /**
     * Clear all expired token.
     *
     * @param boolean all Optional flag to clear all token; default is false.
     */
    public function clear($all) {
        if ($all) {
            $sql = "DELETE FROM " . DB_PREFIX.'token';
        } else {
            $sql = "DELETE FROM " . DB_PREFIX.'token' . "
                    WHERE expires < now()";
        }
        ZMRuntime::getDatabase()->updateObj($sql);
    }

}
