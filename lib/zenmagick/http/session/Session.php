<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\http\session;

use RuntimeException;
use Serializable;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;


/**
 * A basic, cookies only, session class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo allow to expire session after a given time (will need cookie update for each request)
 */
class Session extends ZMObject {
    /** The default session name. */
    const DEFAULT_NAME = 'zmid';
    /** A magic session key used to validate forms. */
    const SESSION_TOKEN_KEY = '__ZM_TOKEN__';
    /** The auto save namespace prefix for session keys. */
    const AUTO_SAVE_KEY = '__ZM_AUTO_SAVE_KEY__';

    protected $internalStart;
    protected $data;
    protected $sessionHandler;
    private $cookiePath;
    private $closed;
    private $domain;
    private $useFqdn;


    /**
     * Create new instance.
     *
     * <p>If an existing session is detected (via <code>isNew()</code>), the session is automatically started.</p>
     *
     * @param string domain Optional cookie domain; default is <code>null</code>.
     * @param string name Optional session name; default is <code>Session::DEFAULT_NAME</code>.
     */
    public function __construct($domain=null, $name=self::DEFAULT_NAME) {
        parent::__construct();
        $this->domain = null != $domain ? $domain : $_SERVER['HTTP_HOST'];
        $this->setName(null !== $name ? $name : self::DEFAULT_NAME);

        $this->internalStart = false;
        $this->useFqdn = true;
        $this->data = array();
        $this->sessionHandler = null;
        $this->closed = false;

        if (!$this->isStarted()) {

            ini_set('session.cookie_path', '/');
            // disable transparent sid support
            ini_set('session.use_trans_sid', false);

            // no rewrite
            ini_set('url_rewriter.tags', '');

            // do not automatically start a session (just in case)
            ini_set('session.auto_start', 0);

            // set up gc
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', 2);

            // session cookie
            ini_set('session.cookie_lifetime', 0);

            // XSS protection
            ini_set('session.cookie_httponly', true);

            // general protection
            ini_set('session.cookie_secure', false);
            ini_set('session.use_only_cookies', true);
        }
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        $this->close();
    }


    /**
     * Set the domain.
     *
     * @param string domain The domain to use.
     */
    public function setDomain($domain) {
        if (null === $domain || !empty($domain)) {
            $this->domain = $domain;
        }
    }

    /**
     * Get the domain.
     *
     * @param boolean fqdn Optional flag to request either the fully qualified domain name or a shortened version; default is <code>true</code>.
     * @return string The domain to use.
     */
    public function getDomain($fqdn=true) {
        return $this->adjustDomain($this->domain, $fqdn);
    }

    /**
     * Adjust domain with respect to <em>useFqdn</em> flag.
     *
     * @param string domain The domain.
     * @param boolean fqdn Optional flag to request either the fully qualified domain name or a shortened version; default is <code>true</code>.
     * @return string The adjusted domain.
     */
    protected function adjustDomain($domain, $fqdn) {
        if (null == $domain) {
            return null;
        }

        $domainToken = explode('.', $domain);
        if (2 > count($domainToken) || $fqdn) {
            return $domain;
        } else {
            $tld = '';
            foreach ($domainToken as $ii => $dt) {
                if (!in_array($dt, array('www'))) {
                    $tld .= '.'.$dt;
                }
            }
            return substr($tld, $fqdn ? 1 : 0);
        }
    }

    /**
     * Set the session name.
     *
     * @param string name The session name.
     */
    public function setName($name) {
        if ($this->isStarted()) {
            Runtime::getLogging()->warn(sprintf('session already started - ignoring; name: %s', $name));
            return;
        }
        session_name($name);
    }

    /**
     * Set the session cookie params name.
     *
     * @param string domain The cookie domain name.
     * @param string path The cookie path.
     */
    public function setCookieParams($domain, $path) {
        if ($this->isStarted()) {
            $this->container->get('loggingService')->warn(sprintf('session already started - ignoring; domain: %s, path: %s', $domain, $path));
            return;
        }
        session_set_cookie_params(0, $path, $domain);
        $this->cookiePath = $path;
    }

    /**
     * Set the use <em>fqdn</em> flag.
     *
     * @param boolean value The new value.
     */
    public function setUseFqdn($value) {
        $this->useFqdn = $value;
    }

    /**
     * Check if we have a session yet.
     *
     * @return boolean <code>true<code> if the session has been already started.
     */
    public function isStarted() {
        $id = session_id();
        $isStarted = !empty($id);

        if ($isStarted && !$this->internalStart) {
            // started elsewhere, so sync data
            $this->data = array_merge($_SESSION, $this->data);
        }

        return $isStarted;
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
        return $this->data;
    }

    /**
     * Start session.
     *
     * @param boolean force Optional flag to force a start; default is <code>false</code>.
     * @return boolean <code>true</code> if a session was started, <code>false</code> if not.
     */
    public function start($force=false) {
        session_cache_limiter('must-revalidate');
        $id = session_id();
        if (empty($id) || $force) {
            $this->setCookieParams($this->adjustDomain($this->domain, $this->useFqdn), $this->cookiePath);
            $this->internalStart = true;
            session_start();
            // allow setting / getting data before/without starting session
            $this->data = array_merge($_SESSION, $this->data);
            $this->closed = false;
            $this->restorePersistedServices();
            return true;
        }

        return false;
    }

    /**
     * persist tagged services.
     */
    protected function persistServices() {
        $autoSave = array();
        foreach ($this->container->get('containerTagService')->findTaggedServiceIds('zenmagick.http.session.persist') as $id => $args) {
            // list of services to restore on instance
            $restore = array();
            $context = null;
            $type = 'service';
            foreach ($args as $elem) {
                foreach ($elem as $key => $value) {
                    if ('restore' == $key && $value) {
                        $restore = explode(',', $value);
                    }
                    if ('context' == $key && $value) {
                        $context = $value;
                    }
                    if ('type' == $key && $value) {
                        $type = $value;
                    }
                }
            }

            if (Runtime::isContextMatch($context)) {
                $service = $this->container->get($id);
                if ($service instanceof Serializable) {
                    $autoSave[$id] = array('ser' => serialize($service), 'restore' => $restore, 'type' => $type);
                }
            }
        }
        $this->setValue(self::AUTO_SAVE_KEY, $autoSave);
    }

    /**
     * Restore persisted services.
     */
    protected function restorePersistedServices() {
        // restore persisted services
        foreach ((array)$this->getValue(self::AUTO_SAVE_KEY) as $id => $serdat) {
            $obj = unserialize($serdat['ser']);
            $isService = !isset($serdat['type']) || 'service' == $serdat['type'];
            if ($isService) {
                $service = $this->container->get($id);
                Beans::setAll($service, $obj->getSerializableProperties());
                $obj = $service;
            }
            foreach ($serdat['restore'] as $rid) {
                if ($this->container->has($rid)) {
                    $rid = trim($rid);
                    $method = 'set'.ucwords($rid);
                    $obj->$method($this->container->get($rid));
                }
            }
            if (!$isService) {
                // preserve definition
                $definition = $this->container->getDefinition($id);
                $this->container->set($id, $obj);
                $this->container->setDefinition($id, $definition);
            }
        }
    }

    /**
     * Destroy the current session.
     */
    public function destroy() {
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', 0, $this->cookiePath);
            unset($_COOKIE[session_name()]);
        }

        $this->data = array();
        $_SESSION = array();

        session_unset();
        if ($this->isStarted()) {
            $this->close(false);
            session_destroy();
        }
    }

    /**
     * Regenerate session.
     *
     * <p>This will create a new session id while keeping existing session data.</p>
     */
    public function regenerate() {
        $lastSessionId = session_id();
        if (!empty($lastSessionId)) {
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', 0, $this->cookiePath);
                unset($_COOKIE[session_name()]);
            }

            session_regenerate_id(false);
            $newId = session_id();

            // persist old session
            $this->close(false);

            // switch back to new session id
            session_id($newId);
            $this->registerSessionHandler($this->sessionHandler);
            // and start
            $this->start(true);
            // regenerate token too
            $this->getToken(true);
            // keep old session id for reference
            $this->setValue('lastSessionId', $lastSessionId);
        }
    }

    /**
     * Close session rather than wait for the end of request handling.
     *
     * @param boolean final Optional flag to indicate whether this is a final close; default is <code>true</code>.
     */
    public function close($final=true) {
        if ($this->isStarted() && !$this->closed) {
            foreach ($this->data as $name => $value) {
                $_SESSION[$name] = $value;
            }
            if (0 == count($_SESSION)) {
                // get a new token
                $this->getToken(true);
            }
            if ($final) {
                $this->persistServices();
            }
            session_write_close();
            $this->closed = true;
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
     * @return mixed The old value or <code>null</code>.
     */
    public function setValue($name, $value=null) {
        $old = null;
        if (null !== $name) {
            $old = isset($this->data[$name]) ? $this->data[$name] : null;
            if (null === $value) {
                unset($this->data[$name]);
            } else {
                $this->data[$name] = $value;
            }
        } else {
            // clear all
            $this->data = array();
        }
        return $old;
    }

    /**
     * Get a session value.
     *
     * @param string name The name; if <code>null</code> and namespace set, return all namespace data.
     * @param mixed default Optional default value if <code>$name</code> doesn't exist;
     * @return mixed The value or <code>$default</code>.
     */
    public function getValue($name, $default=null) {
        if (!$this->isStarted() && !$this->isNew()) {
            // start only if not a new session
            $this->start();
        }
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    /**
     * Register a session handler.
     *
     * @param SessionHandler sessionHandler A session handler instance.
     */
    public function registerSessionHandler(SessionHandler $sessionHandler) {
        if (null !== $sessionHandler && is_object($sessionHandler) && $sessionHandler instanceof SessionHandler) {
            ini_set('session.save_handler', 'user');
            session_set_save_handler(array($sessionHandler, 'open'), array($sessionHandler, 'close'), array($sessionHandler, 'read'),
                array($sessionHandler, 'write'), array($sessionHandler, 'destroy'), array($sessionHandler, 'gc'));
            $this->sessionHandler = $sessionHandler;
            register_shutdown_function('session_write_close');
        }
    }

    /**
     * Get the current session handler.
     *
     * @return SessionHandler A session handler or <code>null</code>.
     */
    public function getSessionHandler() {
        return $this->sessionHandler;
    }

    /**
     * Get the session token.
     *
     * <p>A new token will be created if none exists.</p>
     *
     * @param boolean renew If <code>true</code> a new token will be generated; default is <code>false</code>.
     * @param string tokenKey Optional token key; default is <code>SESSION_TOKEN_KEY</code>.
     * @return string The token.
     */
    public function getToken($renew=false, $tokenKey=self::SESSION_TOKEN_KEY) {
        if ($renew || null == $this->getValue($tokenKey)) {
            // in this case we really want a session!
            if (!$this->isStarted()) {
                $this->start();
            }
            $this->setValue($tokenKey, md5(uniqid(rand(), true)));
        }

        return $this->getValue($tokenKey);
    }


    /**
     * Get user session.
     *
     * <p>Get a custmizable object wrapping user session values.</p>.
     *
     * @return UserSession The user session object or <code>null</code>.
     */
    public function getUserSession() {
        return $this->container->has('userSession') ? $this->container->get('userSession') : null;
    }

}
