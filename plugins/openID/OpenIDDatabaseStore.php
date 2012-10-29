<?php
/**
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * A <code>OpenIDStore</code> implementation for the PHP OpenID library by
 * JanRain (http://www.openidenabled.com/).
 *
 * Based on the code http://www.saeven.net/openid.htm.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, using version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package org.zenmagick.plugins.openID
 * @author S. Alexandre Lemaire, saeven.net consulting inc. saeven@saeven.net
 * @author DerManoMann <mano@zenmagick.org>
 */

namespace ZenMagick\plugins\openID;

use ZMRuntime;
use Auth_OpenID_Association;
use Auth_OpenID_OpenIDStore;

class OpenIDDatabaseStore extends Auth_OpenID_OpenIDStore {
    private $nonceLifetime;

    /**
     * Create new instance.
     *
     * @param int nonceLifetime Optional nonce lifetime; default is <em>0</em> to use the OpenID default.
     */
    public function __construct($nonceLifetime=0) {
        if (0 == $nonceLifetime) {
            global $Auth_OpenID_SKEW;
            $this->nonceLifetime = $Auth_OpenID_SKEW;
        } else {
            $this->nonceLifetime = $nonceLifetime;
        }

        ZMRuntime::getDatabase()->getMapper()->setMappingForTable('zm_openid_associations',
            array(
                'server_url' => array('column' => 'server_url', 'type' => 'string', 'key' => true),
                'handle' => array('column' => 'handle', 'type' => 'string', 'key' => true),
                'secret' => array('column' => 'secret', 'type' => 'blob'),
                'issued' => array('column' => 'issued', 'type' => 'integer'),
                'lifetime' => array('column' => 'lifetime', 'type' => 'integer'),
                'type' => array('column' => 'assoc_type', 'type' => 'string'),
            )
        );
        ZMRuntime::getDatabase()->getMapper()->setMappingForTable('zm_openid_nonces',
            array(
                'server_url' => array('column' => 'server_url', 'type' => 'string', 'key' => true),
                'issued' => array('column' => 'issued', 'type' => 'integer', 'key' => true),
                'salt' => array('column' => 'salt', 'type' => 'string', 'key' => true),
            )
        );
    }

    /**
     * Store an association
     *
     * @param string $server_url
     * @param Auth_OpenID_Association $association
     */
    public function storeAssociation($server_url, $association) {
        $sql = "REPLACE INTO %table.zm_openid_associations%
                (server_url, handle, secret, issued, lifetime, assoc_type)
                VALUES (:server_url, :handle, :secret, :issued, :lifetime, :type)";
        $args = array(
            'server_url' => $server_url,
            'handle' => $association->handle,
            'secret' => $association->secret,
            'issued' => $association->issued,
            'lifetime' => $association->lifetime,
            'type' => $association->assoc_type
        );
        ZMRuntime::getDatabase()->updateObj($sql, $args, 'zm_openid_associations');
    }

    /**
     * Get either a specific association or the newest available.
     *
     * @param string server_url The server url.
     * @param string handle Optional handle; default is <code>null</code> to retreive the newest association available.
     * @return Auth_OpenID_Association The association or <code>null</code>.
     */
    public function getAssociation($server_url, $handle=null) {
        $associations = array();
        if ($handle != null) {
            $sql = "SELECT server_url, handle, secret, issued, lifetime, assoc_type
                    FROM %table.zm_openid_associations%
                    WHERE server_url = :server_url AND handle = :handle";
            $row = ZMRuntime::getDatabase()->querySingle($sql, array('server_url' => $server_url, 'handle' => $handle), 'zm_openid_associations');
            if (null != $row) {
                $associations[] = new Auth_OpenID_Association($row['handle'], $row['secret'], $row['issued'], $row['lifetime'], $row['type']);
            }
        } else {
            $sql = "SELECT server_url, handle, secret, issued, lifetime, assoc_type
                    FROM %table.zm_openid_associations%
                    WHERE server_url = :server_url";
            $rows = ZMRuntime::getDatabase()->fetchAll($sql, array('server_url' => $server_url), 'zm_openid_associations');
            foreach ($rows as $row) {
                $associations[] = new Auth_OpenID_Association($row['handle'], $row['secret'], $row['issued'], $row['lifetime'], $row['type']);
            }
        }

        $newest = null;
        if (0 < count($associations)) {
            foreach ($associations as $assoc) {
                if (!$assoc->getExpiresIn()) {
                    $this->removeAssociation($server_url, $assoc->handle);
                    continue;
                }

                if ($newest == null) {
                    $newest = $assoc;
                } else {
                    if ($newest->issued < $assoc->issued) {
                        $newest = $assoc;
                    }
                }
            }
        }

        return $newest;
    }

    /**
     * Delete an association
     *
     * @param string $server_url
     * @param string $handle
     */
    public function removeAssociation($server_url, $handle) {
        $sql = "DELETE FROM %table.zm_openid_associations%
                WHERE server_url = :server_url AND handle = :handle";
        $args = array('server_url' => $server_url, 'handle' => $handle);
        ZMRuntime::getDatabase()->updateObj($sql, $args, 'zm_openid_associations');
        return true;
    }

    /**
     * Use nonce.
     */
    public function useNonce($server_url, $issued, $salt) {
        if (abs($issued - time()) > $this->nonceLifetime) {
            return false;
        }

        $sql = "INSERT INTO %table.zm_openid_nonces%
                (server_url, issued, salt)
                VALUES (:server_url, :issued, :salt)";
        $args = array('server_url' => $server_url, 'issued' => $issued, 'salt' => $salt);
        ZMRuntime::getDatabase()->updateObj($sql, $args, 'zm_openid_nonces');
        return true;
    }

    /**
     * Cleanup nonces.
     */
    public function cleanupNonces() {
        $timestamp = time() - $this->nonceLifetime;

        $sql = "DELETE FROM %table.zm_openid_nonces%
                WHERE issued < :issued";
        $args = array('issued' => $timestamp);
        return ZMRuntime::getDatabase()->updateObj($sql, $args, 'zm_openid_nonces');
    }

    /**
     * Cleanup associations.
     */
    public function cleanupAssociations() {
        $sql = "DELETE FROM %table.zm_openid_associations%
                WHERE (issued + lifetime) < :lifetime";
        // use lifetime mapping to compare times...
        $args = array('lifetime' => time());
        return ZMRuntime::getDatabase()->updateObj($sql, $args, 'zm_openid_associations');
    }

    /**
     * Reset.
     */
    public function reset() {
        ZMRuntime::getDatabase()->updateObj("DELETE FROM %table.zm_openid_associations%", array(), 'zm_openid_associations');
        ZMRuntime::getDatabase()->updateObj("DELETE FROM %table.zm_openid_nonces%", array(), 'zm_openid_nonces');
    }

}
