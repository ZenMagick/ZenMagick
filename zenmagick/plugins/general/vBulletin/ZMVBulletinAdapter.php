<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Adapter class for vBulletin data.
 *
 * <p>Methods prefixed with <em>v</em> are validation rules that are wrapped in a 
 * <copde>ZMWrapperRule</code>.</p>
 *
 * @package org.zenmagick.plugins.vbulletin
 * @author DerManoMann
 * @version $Id$
 */
class ZMVBulletinAdapter extends ZMObject {
    private $database_;
    private $dbConfig_;
    private $userTable_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        require ZM_VBULLETIN_ROOT.'includes'.DIRECTORY_SEPARATOR.'config.php';

        if (!isset($config)) {
            throw new ZMException('cannot find vBulletin config');
        }

        // ZMDatabase config
        $this->dbConfig_ = array(
            'host' => $config['MasterServer']['servername'],
            'port' => $config['MasterServer']['port'],
            'database' => $config['Database']['dbname'],
            'username' => $config['MasterServer']['username'],
            'password' => $config['MasterServer']['password'],
            'driver' => $config['Database']['dbtype'],
        );
        $this->userTable_ = $config['Database']['tableprefix'] . 'user';
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
            $this->database_ = ZMRuntime::getDatabase($this->dbConfig_);
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
        return null == $this->getAccountForNickName($req['nickName']);
    }

    /**
     * Check for duplicate email address.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    public function vDuplicateEmail($req) {
        return null == $this->getAccountForEmail($req['email']);
    }

    /**
     * Check for duplicate email address if different from current account email address.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    public function vDuplicateChangedEmail($req) {
        // the current account
        $account = ZMRequest::instance()->getAccount();
        if ($account->getEmail() != $req['email']) {
            // changed
            return $this->vDuplicateEmail($req);
        }
        return true;
    }

    /**
     * Check for duplicate nickname if different from current account nickname.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the nickname is valid, <code>false</code> if not.
     */
    public function vDuplicateChangedNickname($req) {
        // the current account
        $account = ZMRequest::instance()->getAccount();
        if ($account->getNickName() != $req['nickName']) {
            // changed
            return $this->vDuplicateNickname($req);
        }
        return true;
    }

    /**
     * Get account for nickname.
     *
     * @param string nickName The nick name.
     * @return mixed A data array or <code>null</code>.
     */
    public function getAccountForNickName($nickName) {
        $sql = "SELECT * FROM " . $this->userTable_ . "
                WHERE username = :username";
        return $this->getDatabase()->querySingle($sql, array('username' => $nickName), $this->userTable_);
    }

    /**
     * Get account for email.
     *
     * @param string email The email address.
     * @return mixed A data array or <code>null</code>.
     */
    public function getAccountForEmail($email) {
        $sql = "SELECT * FROM " . $this->userTable_ . "
                WHERE email = :email";
        // assum unique email address...
        return $this->getDatabase()->querySingle($sql, array('email' => $email), $this->userTable_);
    }

    /**
     * Create a new account.
     *
     * @param ZMAccount account The store account.
     * @param string password The clear text password.
     */
    public function createAccount($account, $password) {
        $salt = ZMSecurityUtils::random(3);
        $data = array(
            'customers_id' => $account->getId(),
            'username' => $account->getNickName(),
            'password' => md5(md5($password).$salt),
            'salt' => $salt,
            'email' => $account->getEmail()
        );

        $data = $this->getDatabase()->createModel($this->userTable_, $data);

        if (is_array($data) && array_key_exists('userid', $data)) {
            return true;
        }

        return false;
    }

    /**
     * Update an account.
     *
     * @param string nickName The nick name.
     * @param string password The clear text password.
     * @param string email The email address.
     * @return boolean <code>true</code> on success.
     */
    public function updateAccount($nickName, $password, $email) {
        $data = $this->getAccountForEmail($email);
        if (null !== $data) {
            $updates = array(
                'username' => $nickName,
                'email' => $email
            );
            if (null != $password) {
                $updates['password'] = md5($password);
            }
            $data = array_merge($data, $updates);
            $this->getDatabase()->updateModel($this->userTable_, $data);
            return true;
        }
        return false;
    }

    /**
     * Remove an account.
     *
     * @param string email The email address.
     * @return boolean <code>true</code> on success.
     */
    public function removeAccount($email) {
        $data = $this->getAccountForEmail($email);
        if (is_array($data) && array_key_exists('userid', $data)) {
            return $this->getDatabase()->removeModel($this->userTable_, array('userid' => $data['userid']));
        }

        return false;
    }

}
