<?php
/*
 * ZenMagick - Another PHP framework.
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


/**
 * A basic, cookies only, session class.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.session
 * @todo allow to expire session after a given time (will need cookie update for each request)
 */
class ZMSession extends ZMObject {
    /** The default session name. */
    const DEFAULT_NAME = 'zmid';
    /** A magic session key used to validate forms. */
    const SESSION_TOKEN_KEY = '__ZM_TOKEN__';
    /** The default namespace prefix for session keys. */
    const DEFAULT_NAMESPACE_PREFIX = '__ZM_NSP__';

    private $data_;
    private $cookiePath_;
    private $cookieDomain_;
    private $secureCookie_;
    private $sessionHandler_;


    /**
     * Create new instance.
     *
     * <p>If an existing session is detected (via <code>isNew()</code>), the session is automatically started.</p>
     *
     * @param string name Optional session name; default is <code>ZMSession::DEFAULT_NAME</code>.
     * @param boolean secure Indicate whether the session cookie should be secure or not; default is <code>true</code>.
     */
    function __construct($domain=null, $name=self::DEFAULT_NAME, $secure=false) {
        parent::__construct();
        $this->setName(null !== $name ? $name : self::DEFAULT_NAME);

        $this->data_ = array();
        $this->cookiePath_ = '/';
        $this->secureCookie_ = $secure;
        $this->cookieDomain_ = $domain;
        $this->sessionHandler_ = null;

        if (!$this->isStarted()) {
            // disable transparent sid support
            ini_set('session.use_trans_sid', false);

            // no rewrite
            ini_set('url_rewriter.tags', '');

            // do not automatically start a session (just in case)
            ini_set('session.auto_start', 0);

            // set up gc
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', 2);

            // just in case
            ini_set('session.cookie_path', $this->cookiePath_);

            // session cookie
            ini_set('session.cookie_lifetime', 0);

            // XSS protection
            ini_set("session.cookie_httponly", true);

            // general protection
            ini_set("session.cookie_secure", $this->secureCookie_);
            ini_set("session.use_only_cookies", true);

            session_set_cookie_params(0, $this->cookiePath_, $this->cookieDomain_);
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
        $this->close();
    }

    /**
     * Set the session name.
     *
     * @param string name The session name.
     */
    public function setName($name) {
        session_name($name);
    }

    /**
     * Check if we have a session yet.
     *
     * @return boolean <code>true<code> if the session has been already started.
     */
    public function isStarted() {
        $id = session_id();
        return !empty($id);
    }

    /**
     * Check if starting this session would create a new session or if a session exists.
     *
     * <p>This will just check for a cookie with the configured session name.</p>
     *
     * @return boolean <code>true</code> if starting this session would result in a new session.
     */
    public function isNew() {
        return !isset($_COOKIE[session_name()]);
    }

    /**
     * Get the current session data.
     */
    public function getData() {
        return $this->data_;
    }

    /**
     * Start session.
     *
     * @return boolean <code>true</code> if a session was started, <code>false</code> if not.
     */
    public function start() {
        session_cache_limiter('must-revalidate');
        $id = session_id();
        if (empty($id)) {
            session_start();
            // allow setting / getting data before/without starting session
            $this->data_ = array_merge($_SESSION, $this->data_);
            return true;
        }

        return false;
    }

    /**
     * Destroy the current session.
     */
    public function destroy() {
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', 0, $this->cookiePath_);
            unset($_COOKIE[session_name()]);
        }

        session_unset();
        if ($this->isStarted()) {
            session_destroy();
        }

        $this->data_ = array();
    }

    /**
     * Regenerate session.
     *
     * <p>This will create a new session id while keeping existing session data.</p>
     */
    public function regenerate() {
        $oldId = session_id();
        if (!empty($oldId)) {
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', 0, $this->cookiePath_);
                unset($_COOKIE[session_name()]);
            }

            session_regenerate_id(false);
            $newId = session_id();

            // persist old session
            $this->close();

            // switch back to new session id
            session_id($newId);
            $this->registerSessionHandler($this->sessionHandler_);
            // and start
            $this->start();
            // regenerate token too
            $this->getToken(true);
        }
    }

    /**
     * Close session rather than wait for the end of request handling.
     */
    public function close() {
        if ($this->isStarted()) {
            // sync with internal data
            foreach ($_SESSION as $name => $value) {
                unset($_SESSION[$name]);
            }
            foreach ($this->data_ as $name => $value) {
                $_SESSION[$name] = $value;
            }
            if (0 == count($_SESSION)) {
                // get a new token
                $this->getToken(true);
            }
            session_write_close();
        }
    }

    /**
     * Get the session id.
     *
     * @return string The session id or <code>null</code>.
     */
    public function getId() {
        return $this->isStarted() ? session_id() : null;
    }

    /**
     * Get the session name.
     *
     * @return string The session name.
     */
    public function getName() {
        return session_name();
    }

    /**
     * Set a session value.
     *
     * @param string name The name; default is <code>null</code> to clear all data.
     * @param mxied value The value; use <code>null</code> to remove; default is <code>null</code>.
     * @param string namespace Optional namespace; default is <code>null</code> for none.
     * @return mixed The old value or <code>null</code>.
     */
    public function setValue($name, $value=null, $namespace=null) {
        $old = null;
        if (null !== $name) {
            if (null === $namespace) {
                $old = isset($this->data_[$name]) ? $this->data_[$name] : null;
                if (null === $value) {
                    unset($this->data_[$name]);
                } else {
                    $this->data_[$name] = $value;
                }
            } else {
                $namespace = self::DEFAULT_NAMESPACE_PREFIX.$namespace;
                if (isset($this->data_[$namespace])) {
                    $old = isset($this->data_[$namespace][$name]) ? $this->data_[$namespace][$name] : null;
                    if (null === $value) {
                        unset($this->data_[$namespace][$name]);
                        if (0 == count($this->data_[$namespace])) {
                            unset($this->data_[$namespace]);
                        }
                    } else {
                        $this->data_[$namespace][$name] = $value;
                    }
                } else {
                    if (null !== $value) {
                        $this->data_[$namespace] = array($name => $value);
                    }
                }
            }
        } else {
            // clear all
            $this->data_ = array();
        }

        return $old;
    }

    /**
     * Get a session value.
     *
     * @param string name The name.
     * @param string namespace Optional namespace; default is <code>null</code> for none.
     * @return mixed The value or <code>null</code>.
     */
    public function getValue($name, $namespace=null) {
        if (!$this->isStarted() && !$this->isNew()) {
            // start only if not a new session
            $this->start();
        }
        if (null === $namespace) {
            return isset($this->data_[$name]) ? $this->data_[$name] : null;
        } else {
            $namespace = self::DEFAULT_NAMESPACE_PREFIX.$namespace;
            if (isset($this->data_[$namespace])) {
                return isset($this->data_[$namespace][$name]) ? $this->data_[$namespace][$name] : null;
            } else {
                return null;
            }
        }
    }

    /**
     * Register a session handler.
     *
     * @param mixed A session handler instance.
     */
    public function registerSessionHandler($handler) {
        if (null !== $handler && is_object($handler) && $handler instanceof ZMSessionHandler) {
            ini_set('session.save_handler', 'user');
            session_set_save_handler(array($handler, 'open'), array($handler, 'close'), array($handler, 'read'),
                array($handler, 'write'), array($handler, 'destroy'), array($handler, 'gc'));
            $this->sessionHandler_ = $handler;
        }
    }

    /**
     * Get the session token.
     *
     * <p>A new token will be created if none exists.</p>
     *
     * @param boolean renew If <code>true</code> a new token will be generated; default is <code>false</code>.
     * @return string The token.
     */
    public function getToken($renew=false) {
        if ($renew || null == $this->getValue(self::SESSION_TOKEN_KEY)) {
            // in this case we really want a session!
            if (!$this->isStarted()) {
                $this->start();
            }
            $this->setValue(self::SESSION_TOKEN_KEY, md5(uniqid(rand(), true)));
        }

        return $this->getValue(self::SESSION_TOKEN_KEY);
    }

}
