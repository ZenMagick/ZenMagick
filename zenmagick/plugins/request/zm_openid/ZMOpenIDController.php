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
 * OpenID authentication controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_openid
 * @version $Id$
 */
class ZMOpenIDController extends ZMController {
    private $plugin;
    private $returnTo;
    private $sRegRequired;
    private $sRegOptional;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->plugin = ZMPlugins::instance()->getPluginForId('zm_openid');
        $this->returnTo = str_replace('&amp;', '&', ZMToolbox::instance()->net->url(FILENAME_OPEN_ID, 'action=finishAuth', true, false));
        $this->sRegRequired = array('email');
        $this->sRegOptional = array('fullname', 'nickname');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $action = ZMRequest::getParameter('action');
        if ('finishAuth' == $action) {
            $info = $this->finishAuthentication($openid);
            if (null !== $info) {
                $session = ZMRequest::getSession();
                if ($session->getValue('openid') == $info['openid']) {
                    $account = $this->plugin->getAccountForOpenID($info['openid']);

                    if (!$session->registerAccount($account, $this)) {
                        return $this->findView('login');
                    }

                    $followUpUrl = $session->getLoginFollowUp();
                    return $this->findView('success', array(), array('url' => $followUpUrl));
                }
            }
        }

        return $this->findView('login');
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (!$this->validate('openid_login')) {
            return $this->findView('login');
        }

        $action = ZMRequest::getParameter('action');
        $openid = ZMRequest::getParameter('openid');

        $account = $this->plugin->getAccountForOpenID($openid);
        if (null != $account) {
            $session = ZMRequest::getSession();
            if ('initAuth' == $action && null != $openid) {
                // save to compare with response
                $session->setValue('openid', $openid);
                return $this->initAuthentication($openid);
            } else {
                ZMMessages::instance()->error(zm_l10n_get('The provided OpenID does not seem to be valid'));
            }
        } else {
            ZMMessages::instance()->error(zm_l10n_get('The provided OpenID does not seem to be valid'));
        }

        return $this->findView('login');
    }


    /**
     * Initiate OpenID authentication.
     *
     * @param string openid The OpenID to authenticate.
     */
    private function initAuthentication($openid) {
        $store = new ZMDatabaseOpenIDStore();
        $consumer = new Auth_OpenID_Consumer($store);
        $auth_request = $consumer->begin($openid);

        if (!$auth_request) {
            ZMMessages::instance()->error(zm_l10n_get('The provided OpenID does not seem to be valid'));
            return $this->findView('login');
        }

        // required, optional
        $sreg_request = Auth_OpenID_SRegRequest::build($this->sRegRequired, $this->sRegOptional);
        if ($sreg_request) {
            $auth_request->addExtension($sreg_request);
        }

        $papePolicyUris = array(PAPE_AUTH_MULTI_FACTOR_PHYSICAL, PAPE_AUTH_MULTI_FACTOR, PAPE_AUTH_PHISHING_RESISTANT);
        $pape_request = new Auth_OpenID_PAPE_Request($papePolicyUris);
        if ($pape_request) {
            $auth_request->addExtension($pape_request);
        }

        //TODO: make configurable
        //$pape_request->addPolicyURI(ZMToolbox::instance()->net->staticPage(FILENAME_PRIVACY));

        // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
        // form to send a POST request to the server.
        $realm = Runtime::getBaseURL(true);
        if ($auth_request->shouldSendRedirect()) {
            $redirect_url = $auth_request->redirectURL($realm, $this->returnTo);

            // If the redirect URL can't be built, display an error message.
            if (Auth_OpenID::isFailure($redirect_url)) {
                ZMMessages::instance()->error(zm_l10n_get('Could not redirect to server: %s', $redirect_url->message));
                return $this->findView('login');
            } else {
                // send redirect.
                header("Location: ".$redirect_url);
            }
        } else {
            // generate form markup and render it
            $form_id = 'openid_message';
            $form_html = $auth_request->htmlMarkup($realm, $this->returnTo, false, array('id' => $form_id));

            if (Auth_OpenID::isFailure($form_html)) {
                ZMMessages::instance()->error(zm_l10n_get('Could not redirect to server: %s', $form_html->message));
                return $this->findView('login');
            } else {
                // render the HTML form
                print $form_html;
            }
        }

        return null;
    }

    /**
     * Finish authentication.
     *
     * @return array OpenID details map or <code>null</code>.
     */
    private function finishAuthentication() {
        $store = new ZMDatabaseOpenIDStore();
        $consumer = new Auth_OpenID_Consumer($store);

        // Complete the authentication process using the server's response.
        $response = $consumer->complete($this->returnTo);

        if ($response->status == Auth_OpenID_SUCCESS) {
            $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
            $sreg = $sreg_resp->contents();

            $sreg['openid'] = $response->getDisplayIdentifier();
            if ($response->endpoint->canonicalID) {
                $sreg['xri'] = $response->endpoint->canonicalID;
            }

            return $sreg;            
        } else if ($response->status == Auth_OpenID_CANCEL) {
            ZMMessages::instance()->msg('Verification cancelled.');
        } else if ($response->status == Auth_OpenID_FAILURE) {
            ZMMessages::instance()->msg('OpenID authentication failed: ' . $response->message);
            $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
            $sreg = $sreg_resp->contents();

            $sreg['openid'] = $response->getDisplayIdentifier();
            if ($response->endpoint->canonicalID) {
                $sreg['xri'] = $response->endpoint->canonicalID;
            }
        }

        return null;
    }

}

?>
