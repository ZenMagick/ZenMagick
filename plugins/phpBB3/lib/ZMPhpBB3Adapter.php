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

use zenmagick\base\ZMObject;

/**
 * Access class for phpBB3 data.
 *
 * <p>Methods prefixed with <em>v</em> are validation rules that are wrapped in a
 * <copde>ZMWrapperRule</code>.</p>
 *
 * @package org.zenmagick.plugins.phpbb3
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMPhpBB3Adapter extends ZMObject {
    private $database_;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        @define('IN_PHPBB', true);
        require ZM_PHPBB3_ROOT . 'config.php';
        require_once ZM_PHPBB3_ROOT . 'includes/constants.php';
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
            if (isset($dbport)) {
                $dbconf['port'] = $dbport;
            }

            $this->database_ = ZMRuntime::getDatabase($dbconf);
        }

        return $this->database_;
    }

    /**
     * Check for duplicate nickname.
     *
     * @param Request request The current request.
     * @param array data The request data.
     * @return boolean <code>true</code> if the nickname is valid, <code>false</code> if not.
     */
    public function vDuplicateNickname($request, $data) {
        $sql = "SELECT username FROM " . USERS_TABLE . "
                WHERE username = :username";
        return null == $this->getDatabase()->querySingle($sql, array('username' => $data['nickName']), USERS_TABLE);
    }

    /**
     * Check for duplicate email address.
     *
     * @param Request request The current request.
     * @param array req The request data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    public function vDuplicateEmail($request, $data) {
        return null == $this->getAccountForEmail($data['email']);
    }

    /**
     * Check for duplicate email address if different from current account email address.
     *
     * @param Request request The current request.
     * @param array req The request data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    public function vDuplicateChangedEmail($request, $data) {
        // the current account
        $account = $request->getAccount();
        if ($account->getEmail() != $data['email']) {
            // changed
            return $this->vDuplicateEmail($data);
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
     * Get account for email.
     *
     * @param string email The email address.
     * @return mixed A data array or <code>null</code>.
     */
    protected function getAccountForEmail($email) {
        $sql = "SELECT * FROM " . USERS_TABLE . "
                WHERE user_email_hash = :user_email_hash";
        $email_hash = crc32(strtolower($email)) . strlen($email);
        return $this->getDatabase()->querySingle($sql, array('user_email_hash' => $email_hash), USERS_TABLE);
    }

    /**
     * Create a new account.
     *
     * @param ZMAccount account The store account.
     * @param string password The clear text password.
     */
    public function createAccount($account, $password) {
        $nickName = $account->getNickName();
        $email = $account->getEmail();
        if (false !== ($groupId = $this->getDefaultGroupId())) {
            $authentication = new ZMPhpBB3Authentication();
            $data = array(
                'username'          => $nickName,
                'username_clean'    => strtolower($nickName),
                'user_password'     => $authentication->encryptPassword($password),
                'user_pass_convert' => 0,
                'user_email'        => strtolower($email),
                'user_email_hash'   => crc32(strtolower($email)) . strlen($email),
                'group_id'          => $groupId,
                'user_type'         => USER_NORMAL,
            );

            // These are the additional vars able to be specified (functions_user.php user_add()
            $additional_vars = array(
                'user_permissions' => '',
                //'user_timezone'   => $config['board_timezone'],
                //'user_dateformat' => $config['default_dateformat'],
                //'user_lang'       => $config['default_lang'],
                //'user_style'      => (int) $config['default_style'],
                'user_actkey'       => '',
                'user_ip'           => '',
                'user_regdate'      => time(),
                'user_passchg'      => time(),
                'user_options'      => 895,

                'user_inactive_reason'  => 0,
                'user_inactive_time'    => 0,
                'user_lastmark'         => time(),
                'user_lastvisit'        => 0,
                'user_lastpost_time'    => 0,
                'user_lastpage'         => '',
                'user_posts'            => 0,
                //'user_dst'            => (int) $config['board_dst'],
                'user_colour'           => '',
                'user_occ'              => '',
                'user_interests'        => '',
                'user_avatar'           => '',
                'user_avatar_type'      => 0,
                'user_avatar_width'     => 0,
                'user_avatar_height'    => 0,
                'user_new_privmsg'      => 0,
                'user_unread_privmsg'   => 0,
                'user_last_privmsg'     => 0,
                'user_message_rules'    => 0,
                'user_full_folder'      => PRIVMSGS_NO_BOX,
                'user_emailtime'        => 0,

                'user_notify'           => 0,
                'user_notify_pm'        => 1,
                'user_notify_type'      => NOTIFY_EMAIL,
                'user_allow_pm'         => 1,
                'user_allow_viewonline' => 1,
                'user_allow_viewemail'  => 1,
                'user_allow_massemail'  => 1,

                'user_sig'                 => '',
                'user_sig_bbcode_uid'      => '',
                'user_sig_bbcode_bitfield' => '',

                'user_form_salt'           => 'zm',
            );

            $data = array_merge($additional_vars, $data);
            $data = $this->getDatabase()->createModel(USERS_TABLE, $data);
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
            $authentication = new ZMPhpBB3Authentication();
            $updates = array(
                'username' => $nickName,
                'username_clean' => strtolower($nickName),
                'user_email' => strtolower($email),
                'user_email_hash' => crc32(strtolower($email)) . strlen($email),
            );
            if (null != $password) {
                $updates['user_password'] = $authentication->encryptPassword($password);
            }
            $data = array_merge($data, $updates);
            $this->getDatabase()->updateModel(USERS_TABLE, $data);
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
        if (null !== $data) {
            $this->getDatabase()->removeModel(USERS_TABLE, $data);
            return true;
        }

        return false;
    }

}
