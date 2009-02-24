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
 * Access class for phpBB3 data.
 *
 * <p>Methods prefixed with <em>v</em> are validation rules that are wrapped in a 
 * <copde>ZMWrapperRule</code>.</p>
 *
 * @package org.zenmagick.plugins.zm_phpbb3
 * @author DerManoMann
 * @version $Id$
 */
class ZMPhpBB3 extends ZMObject {
    private $database_;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        @define('IN_PHPBB', true);
        require ZM_PHPBB3_ROOT . 'config.php';
        require_once ZM_PHPBB3_ROOT . 'includes/constants.php';

        // init here, as table defines need to be done before creating SQL...
        //$this->getDatabase();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get database.
     *
     * @return ZMDatabase A database handle.
     */
    protected function getDatabase() {
        if (null == $this->database_) {
            // load phpBB3 config
            require ZM_PHPBB3_ROOT . 'config.php';

            // ZMDatabase config
            $dbconf = array(
                'host' => $dbhost,
                'database' => $dbname,
                'username' => $dbuser,
                'password' => $dbpasswd,
                'driver' => $dbms
            );

            $this->database_ = ZMRuntime::getDatabase($dbconf);

            // also setup database mapping
            ZMDbTableMapper::instance()->setMappingForTable(USERS_TABLE, 
                array(
                    'username' => 'column=username;type=string;',
                    'user_email' => 'column=user_email;type=string;'
                  )
            );
        }

        return $this->database_;
    }

    /**
     * Check for duplicate nickname.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the nickname is valid, <code>false</code> if not.
     */
    public function vDuplicateNickname($req) {
        $sql = "SELECT username FROM " . USERS_TABLE . "
                WHERE username = :username";
        return null == $this->getDatabase()->querySingle($sql, array('username' => $req['nick']), USERS_TABLE);
    }

    /**
     * Check for duplicate email address.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    public function vDuplicateEmail($req) {
        $sql = "SELECT user_email FROM " . USERS_TABLE . "
                WHERE user_email = :user_email";
        return null == $this->getDatabase()->querySingle($sql, array('user_email' => $req['email_address']), USERS_TABLE);
    }

    /**
     * Check for duplicate email address if different from current account email address.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    public function vDuplicateChangedEmail($req) {
        // the current account
        $account = ZMRequest::getAccount();

        if ($account->getEmail() != $req['email_address']) {
            // changed
            return $this->vDuplicateNickname($req);
        }

        return true;
    }

    /**
     * Get the default group id.
     *
     * @return int The default group id or <code>false</code>.
     */
    protected function getDefaultGroupId() {
        $sql = 'SELECT group_id
                FROM ' . GROUPS_TABLE . "
                WHERE group_name = 'REGISTERED'
                  AND group_type = " . GROUP_SPECIAL;

        $result = $this->getDatabase()->querySingle($sql, array(), null, ZMDatabase::MODEL_RAW);
        return null !== $result ? (int)$result['group_id'] : false;
    }

    /**
     * Create a new account.
     *
     * @param string nickName The nick name.
     * @param string password The clear text password.
     * @param string email The email address.
     */
    public function createAccount($nickName, $password, $email) {
        if (false !== ($groupId = $this->getDefaultGroupId())) {
            $authentication = ZMLoader::make('ZMPhpBB3Authentication');
            $data = array(
                    'username'      => $nickName,
                    'user_password' => $authentication->encryptPassword($password),
                    'user_email'    => strtolower($email),
                    'group_id'      => $groupId,
                    'user_type'     => USER_NORMAL,
            );
            var_dump($data);
        }

        //$user_id = user_add($data);
    }

}


?>
