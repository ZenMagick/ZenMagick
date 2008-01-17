<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Init plugin to set up the session.
 *
 * @package org.zenmagick.plugins.init
 * @author DerManoMann
 * @version $Id$
 */
class zm_init_session extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct('Session', 'Set up the session');
        $this->setPreferredSortOrder(5);
    }

    /**
     * Default c'tor.
     */
    function zm_init_session() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    function init() {
    global $zm_request;

        parent::init();

        //TODO:
        /**
         * set the session name and save path
         */
        zen_session_name('zenid');
        zen_session_save_path(SESSION_WRITE_DIRECTORY);
        /**
         * set the session cookie parameters
         */
        $http_domain = zen_get_top_level_domain(HTTP_SERVER);
        $https_domain = zen_get_top_level_domain(HTTPS_SERVER);
        $current_domain = (($request_type == 'NONSSL') ? $http_domain : $https_domain);
        if (SESSION_USE_FQDN == 'False') $current_domain = '.' . $current_domain;
        session_set_cookie_params(0, '/', (zen_not_null($current_domain) ? $current_domain : ''));
        /**
         * set the session ID if it exists
         */
        if (isset($_POST[zen_session_name()])) {
          zen_session_id($_POST[zen_session_name()]);
        } elseif ($zm_request->isSecure() && isset($_GET[zen_session_name()]) ) {
          zen_session_id($_GET[zen_session_name()]);
        }
        /**
         * need to tidy up $_SERVER['REMOTE_ADDR'] here before we use it any where else
         * one problem we don't address here is if $_SERVER['REMOTE_ADDRESS'] is not set to anything at all
         */
        $ipAddressArray = explode(',', $_SERVER['REMOTE_ADDR']);
        $ipAddress = (sizeof($ipAddressArray) > 0) ? $ipAddressArray[0] : '';
        $_SERVER['REMOTE_ADDR'] = $ipAddress;

        /**
         * start the session
         */
        $_SERVER['session_started'] = false;
        if (SESSION_FORCE_COOKIE_USE == 'True') {
          zen_setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30, '/', (zen_not_null($current_domain) ? $current_domain : ''));

          if (isset($_COOKIE['cookie_test'])) {
            zen_session_start();
            $_SERVER['session_started'] = true;
          }
        } elseif (SESSION_BLOCK_SPIDERS == 'True') {
          $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
          $spider_flag = false;
          if (zen_not_null($user_agent)) {
            $spiders = file(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'spiders.txt');
            for ($i=0, $n=sizeof($spiders); $i<$n; $i++) {
              if (zen_not_null($spiders[$i])) {
                if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
                  $spider_flag = true;
                  break;
                }
              }
            }
          }
          if ($spider_flag == false) {
            zen_session_start();
            $_SERVER['session_started'] = true;
          }
        } else {
          zen_session_start();
          $_SERVER['session_started'] = true;
        }

        /**
         * set host_address once per session to reduce load on server
         */
        if (!isset($_SESSION['customers_host_address'])) {
          if (SESSION_IP_TO_HOST_ADDRESS == 'true') {
            $_SESSION['customers_host_address']= @gethostbyaddr($_SERVER['REMOTE_ADDR']);
          } else {
            $_SESSION['customers_host_address'] = OFFICE_IP_TO_HOST_ADDRESS;
          }
        }
        /**
         * verify the ssl_session_id if the feature is enabled
         */
        if ( ($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == 'true') && ($_SERVER['session_started'] == true) ) {
          $ssl_session_id = $_SERVER['SSL_SESSION_ID'];
          if (!$_SESSION['SSL_SESSION_ID']) {
            $_SESSION['SSL_SESSION_ID'] = $ssl_session_id;
          }
          if ($_SESSION['SSL_SESSION_ID'] != $ssl_session_id) {
            zen_session_destroy();
            zen_redirect(zen_href_link(FILENAME_SSL_CHECK));
          }
        }
        /**
         * verify the browser user agent if the feature is enabled
         */
        if (SESSION_CHECK_USER_AGENT == 'True') {
          $http_user_agent = $_SERVER['HTTP_USER_AGENT'];
          if (!$_SESSION['SESSION_USER_AGENT']) {
            $_SESSION['SESSION_USER_AGENT'] = $http_user_agent;
          }
          if ($_SESSION['SESSION_USER_AGENT'] != $http_user_agent) {
            zen_session_destroy();
            zen_redirect(zen_href_link(FILENAME_LOGIN));
          }
        }
        /**
         * verify the IP address if the feature is enabled
         */
        if (SESSION_CHECK_IP_ADDRESS == 'True') {
          $ip_address = zen_get_ip_address();
          if (!$_SESSION['SESSION_IP_ADDRESS']) {
            $_SESSION['SESSION_IP_ADDRESS'] = $ip_address;
          }
          if ($_SESSION['SESSION_IP_ADDRESS'] != $ip_address) {
            zen_session_destroy();
            zen_redirect(zen_href_link(FILENAME_LOGIN));
          }
        }

    }

}

?>
