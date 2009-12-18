<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * @package org.zenmagick.mvc
 */
class ZMSession extends ZMObject {
    /** The default session name. */
    const DEFAULT_NAME = 'zmid';
    /** A magic session key used to identify new sessions. */
    const SESSION_TAG_KEY = '__ZM_TAG__';
    /** A magic session key used to validate forms. */
    const SESSION_TOKEN_KEY = '__ZM_TOKEN__';
    /** The default namespace prefix for session keys. */
    const DEFAULT_NAMESPACE_PREFIX = '__ZM_NSP__';

    private $data_;
    private $new_;
    private $cookiePath_;
    private $secureCookie_;
    private $sessionHandler_;


    /**
     * Create new instance.
     *
     * @param string name Optional session name; default is <code>ZMSession::DEFAULT_NAME</code>.
     * @param boolean secure Indicate whether the session cookie should be secure or not; default is <code>true</code>.
     */
    function __construct($name=self::DEFAULT_NAME, $secure=true) {
        parent::__construct();
        $name = null !== $name ? $name : self::DEFAULT_NAME;
        session_name($name);

        $this->data_ = array();
        $this->new_ = true;
        $this->cookiePath_ = '/';
        $this->secureCookie_ = $secure;
        $this->sessionHandler_ = null;

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
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
        $this->close();
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
     * Check if this is a new session or resumed.
     *
     * <p>This will return <code>true</code> in the following cases:</p>
     * <ul>
     *  <li>There hasn't been a session started for the current request</li>
     *  <li>A session has been started but the session really is new</li>
     * </ul>
     *
     * @return boolean <code>true</code> if this is a new session.
     */
    public function isNew() {
        return $this->new_;

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
            $this->new_ = !isset($_SESSION[self::SESSION_TAG_KEY]);
            $_SESSION[self::SESSION_TAG_KEY] = time();
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
            setcookie(session_name(), '', time()-42000, $this->cookiePath_);
        }

        session_unset();
        session_destroy();
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
                setcookie(session_name(), '', time()-42000, $this->cookiePath_);
            }
            session_regenerate_id();
            $newId = session_id();

            // persist new session
            session_write_close();

            // make sure the old session is gone
            session_id($oldId);
            $this->registerSessionHandler($this->sessionHandler_);
            session_start();
            session_destroy();

            // switch back to new session id
            session_id($newId);
            $this->registerSessionHandler($this->sessionHandler_);
            session_start();
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
     * @return string The session name or <code>null</code>.
     */
    public function getName() {
        return $this->isStarted() ? session_name() : null;
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
            if (isset($this->data_[self::SESSION_TAG_KEY])) {
                $this->data_ = array(self::SESSION_TAG_KEY => $this->data_[self::SESSION_TAG_KEY]);
            } else {
                $this->data_ = array();
            }
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
        if (null === $namespace) {
            return isset($this->data_[$name]) ? $this->data_[$name] : null;
        } else {
            //$namespace = '__'.$namespace;
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
     * Check if the given sid is valid.
     *
     * @param string sid The sid.
     * @return boolean <code>true</code> if the sid is valid.
     */
    public static function isValidSID($sid) {
        $sid = trim($sid);
        if (empty($sid) || !ereg("^[0-9]{1,11}$", $sid)) {
            return false;
        }

        return true;
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
            $this->setValue(self::SESSION_TOKEN_KEY, md5(uniqid(rand(), true)));
        }

        return $this->getValue(self::SESSION_TOKEN_KEY);
    }

}

?>
