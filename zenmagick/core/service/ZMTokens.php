<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Token service.
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id: ZMTokens.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
class ZMTokens extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Tokens');
    }


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
     * @return ZMToken A token.
     */
    public function getNewToken($resource, $lifetime) {
        $token = ZMLoader::make('Token');
        $token->setHash($this->createToken());
        $token->setResource($resource);
        $now = mktime();
        $token->setIssued(date(ZMDatabase::DATETIME_FORMAT, $now));
        $token->setExpires(date(ZMDatabase::DATETIME_FORMAT, $now+$lifetime));
        return Runtime::getDatabase()->createModel(ZM_TABLE_TOKEN, $token);
    }

    /**
     * Update a given token.
     *
     * @param ZMToken token The token.
     * @param int lifetime The lifetime of the token (in seconds).
     */
    public function updateToken($token, $lifetime) {
        $now = mktime();
        $token->setExpires(date(ZMDatabase::DATETIME_FORMAT, $now+$lifetime));
        Runtime::getDatabase()->updateModel(ZM_TABLE_TOKEN, $token);
    }

    /**
     * Check if <em>hash</em> is valid in context of the <em>resource</em>.
     *
     * @param string resource The resource.
     * @param string hash The hash code.
     * @param boolean expire Optional flag to invalidate a matching token; default is <code>true</code>.
     * @return ZMToken A valid token or <code>null</code>.
     */
    public function validateHash($resource, $hash, $expire=true) {
        $sql = "SELECT * FROM " . ZM_TABLE_TOKEN . "
                WHERE hash = :hash AND resource = :resource AND expires >= now()";
        $token = Runtime::getDatabase()->querySingle($sql, array('hash'=>$hash, 'resource'=>$resource), ZM_TABLE_TOKEN, 'Token');
        if ($expire && null !== $token) {
            $sql = "DELETE FROM " . ZM_TABLE_TOKEN . "
                    WHERE hash = :hash AND resource = :resource";
            Runtime::getDatabase()->update($sql, array('hash'=>$hash, 'resource'=>$resource), ZM_TABLE_TOKEN);
        }
        return $token;
    }

    /**
     * Get all token for a given resource.
     *
     * @param string resource The resource.
     * @return array A list of <code>ZMToken</code>.
     */
    public function getTokenForResource($resource) {
        $sql = "SELECT * FROM " . ZM_TABLE_TOKEN . "
                WHERE resource = :resource AND expires >= now()";
        return Runtime::getDatabase()->query($sql, array('resource'=>$resource), ZM_TABLE_TOKEN, 'Token');
    }

    /**
     * Get a token for the given hash.
     *
     * @param string hash The hash.
     * @return ZMToken A <code>ZMToken</code> or <code>null</code>.
     */
    public function getTokenForHash($hash) {
        $sql = "SELECT * FROM " . ZM_TABLE_TOKEN . "
                WHERE hash = :hash AND expires >= now()";
        $results = Runtime::getDatabase()->query($sql, array('hash'=>$hash), ZM_TABLE_TOKEN, 'Token');
        if (1 < count($results)) {
            throw new ZMException('duplicate hash');
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
            $sql = "DELETE FROM " . ZM_TABLE_TOKEN;
        } else {
            $sql = "DELETE FROM " . ZM_TABLE_TOKEN . "
                    WHERE expires < now()";
        }
        Runtime::getDatabase()->update($sql);
    }

}

?>
