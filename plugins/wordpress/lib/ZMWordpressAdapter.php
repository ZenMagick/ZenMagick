<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\ZMObject;

/**
 * Wordpress adapter.
 *
 * <p>Methods prefixed with <em>v</em> are validation rules that are wrapped in a
 * <copde>ZMWrapperRule</code>.</p>
 *
 * @package org.zenmagick.plugins.zm_wordpress
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMWordpressAdapter extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        // use WP registration functions
        require ZM_WORDPRESS_ROOT . 'wp-includes/registration.php';
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }
    /**
     * Check for duplicate nickname.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the nickname is valid, <code>false</code> if not.
     */
    public function vDuplicateNickname($request, $data) {
        return validate_username($data['nickName']);
    }

    /**
     * Check for duplicate email address.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    public function vDuplicateEmail($request, $data) {
		    return ZMLangUtils::isEmpty(email_exists($data['email']));
    }

    /**
     * Check for duplicate email address if different from current account email address.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the email is valid, <code>false</code> if not.
     */
    public function vDuplicateChangedEmail($request, $data) {
        // the current account
        $account = $this->container->get('request')->getAccount();
        if ($account->getEmail() != $data['email']) {
            // changed
            return $this->vDuplicateEmail($request, $data);
        }
        return true;
    }

    /**
     * Create a new account.
     *
     * @param ZMAccount account The store account.
     * @param string password The clear text password.
     * @return boolean <code>true</code> if the account was created.
     */
    public function createAccount($account, $password) {
        $email = $account->getEmail();
		    $userId = username_exists($email);
        if (!$userId) {
            $userId = email_exists($email);
        }
        if (!$userId) {
            $userId = wp_create_user($email, $password, $email);
            update_usermeta($userId, $account->getFirstName());
            update_usermeta($userId, $account->getLastName());

            // and login
            $user = wp_authenticate($email, $password);
            wp_set_auth_cookie($user->ID, true);
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
        $userData = get_userdata(email_exists($email));
        if ($userData) {
            $userId = $userData->ID;
            $_POST['pass1'] = $password;
            $_POST['pass2'] = $password;
            $_POST['email'] = $userData->user_login;
            foreach ($userData as $key => $value) {
              $_POST[$key] = $value;
            }
            edit_user($userId);
        }
    }

    /**
     * Remove an account.
     *
     * @param string email The email address.
     * @return boolean <code>true</code> on success.
     */
    public function removeAccount($email) {
        //TODO
    }

}
