<?php

define('ZM_TABLE_OPENID_ASSOCIATIONS', ZM_DB_PREFIX . 'zm_openid_associations');
define('ZM_TABLE_OPENID_NONCES', ZM_DB_PREFIX . 'zm_openid_nonces');


/**
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
 * @author S. Alexandre Lemaire, saeven.net consulting inc. saeven@saeven.net
 * @author DerManoMann
 * @version $Id$
 */
class ZMDatabaseOpenIDStore extends Auth_OpenID_OpenIDStore {
    private $nonceLifetime;

    // database table mapping
    private static $OPENID_ASSOCIATION_MAPPING = array(
              'server_url' => 'column=server_url;type=string;key=true;',
              'handle' => 'column=handle;type=string;key=true',
              'secret' => 'column=secret;type=blob',
              'issued' => 'column=issued;type=integer',
              'lifetime' => 'column=lifetime;type=integer',
              'type' => 'column=assoc_type;type=string'
    );
    // database table mapping
    private static $OPENID_NONCES_MAPPING = array(
              'server_url' => 'column=server_url;type=string;key=true',
              'issued' => 'column=issued;type=integer;key=true',
              'salt' => 'column=assoc_type;type=string;key=true'
    );


    /**
     * Create new instance.
     *
     * @param int nonceLifetime Optional nonce lifetime; default is <em>0</em> to use the OpenID default.
     */
    function __construct($nonceLifetime=0) {
        if (0 == $nonceLifetime) {
		        global $Auth_OpenID_SKEW;
            $this->nonceLifetime = $Auth_OpenID_SKEW;
        } else {
            $this->nonceLifetime = $nonceLifetime;
        }
    }


    /**
     * Store an association
     *
     * @param string $server_url
     * @param Auth_OpenID_Association $association
     */
    public function storeAssociation($server_url, $association) {
        $sql = "REPLACE INTO ".ZM_TABLE_OPENID_ASSOCIATIONS."
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
        ZMRuntime::getDatabase()->update($sql, $args, self::$OPENID_ASSOCIATION_MAPPING);
    }
	
    /**
     * Get either a specific association or the newest available.
     *
     * @param string server_url The server url.
     * @param string handle Optional handle; default is <code>null</code> to retreive the newest association available.
     * @return Auth_OpenID_Association The association or <code>null</code>.
     */
    public function getAssociation($server_url, $handle=null) {
        $associations	= array();		
        if ($handle != null) {
            $sql = "SELECT server_url, handle, secret, issued, lifetime, assoc_type 
                    FROM ".ZM_TABLE_OPENID_ASSOCIATIONS."
                    WHERE server_url = :server_url AND handle = :handle";
            $row = ZMRuntime::getDatabase()->querySingle($sql, array('server_url' => $server_url, 'handle' => $handle), self::$OPENID_ASSOCIATION_MAPPING);
            if (null != $row) {
                $associations[] = new Auth_OpenID_Association($row['handle'], $row['secret'], $row['issued'], $row['lifetime'], $row['type']);
            }
        } else {
            $sql = "SELECT server_url, handle, secret, issued, lifetime, assoc_type 
                    FROM ".ZM_TABLE_OPENID_ASSOCIATIONS."
                    WHERE server_url = :server_url";
            $rows = ZMRuntime::getDatabase()->query($sql, array('server_url' => $server_url), self::$OPENID_ASSOCIATION_MAPPING);
            foreach ($rows as $row) {
                $associations[] = new Auth_OpenID_Association($row['handle'], $row['secret'], $row['issued'], $row['lifetime'], $row['type']);
            }
        }

        $newest = null;
        if (count($associations)) {
            foreach ($associations as $assoc) {
                if (!$assoc->getExpiresIn()) {
                    $this->removeAssociation($server_url, $assoc->handle);
                    continue;
                }				

                if ( $newest == null) {
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
        $sql = "DELETE FROM ".ZM_TABLE_OPENID_ASSOCIATIONS."
                WHERE server_url = :server_url AND handle = :handle";
        $args = array('server_url' => $server_url, 'handle' => $handle);
        ZMRuntime::getDatabase()->update($sql, $args, self::$OPENID_ASSOCIATION_MAPPING);
        return true;
    }
	
    /**
     * Use nonce.
     */
    public function useNonce($server_url, $issued, $salt) {
        if (abs($issued - mktime()) > $this->nonceLifetime) {
            return false;
        }

        $sql = "INSERT INTO ".ZM_TABLE_OPENID_NONCES."
                (server_url, issued, salt) 
                VALUES (:server_url, :issued, :salt)";
        $args = array('server_url' => $server_url, 'issued' => $issued, 'salt' => $salt);
        ZMRuntime::getDatabase()->update($sql, $args, self::$OPENID_NONCES_MAPPING);
        return true;
    }
	
    /**
     * Cleanup nonces.
     */
    public function cleanupNonces() {
        $timestamp = time() - $this->nonceLifetime;

        $sql = "DELETE FROM ".ZM_TABLE_OPENID_NONCES."
                WHERE issued < :issued";
        $args = array('issued' => $timestamp);
        return ZMRuntime::getDatabase()->update($sql, $args, self::$OPENID_NONCES_MAPPING);
    }
	
    /**
     * Cleanup associations.
     */
    public function cleanupAssociations() {
        $sql = "DELETE FROM ".ZM_TABLE_OPENID_ASSOCIATIONS."
                WHERE issued + lifetime < :timestamp";
        $args = array('timestamp' => time());
        return ZMRuntime::getDatabase()->update($sql, $args, self::$OPENID_ASSOCIATION_MAPPING);
    }
	
    /**
     * Reset.
     */
    public function reset() {
        ZMRuntime::getDatabase()->update("DELETE FROM ".ZM_TABLE_OPENID_ASSOCIATIONS, array(), self::$OPENID_ASSOCIATION_MAPPING);
        ZMRuntime::getDatabase()->update("DELETE FROM ".ZM_TABLE_OPENID_NONCES, array(), self::$OPENID_NONCES_MAPPING);
    }

}

?>
