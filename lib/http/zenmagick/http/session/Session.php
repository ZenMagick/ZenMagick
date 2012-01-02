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
namespace zenmagick\http\session;

use Serializable;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;


/**
 * A basic, cookies only, session class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.session
 * @todo allow to expire session after a given time (will need cookie update for each request)
 */
class Session extends ZMObject {
    /** The default session name. */
    const DEFAULT_NAME = 'zmid';
    /** A magic session key used to validate forms. */
    const SESSION_TOKEN_KEY = '__ZM_TOKEN__';
    /** The default namespace prefix for session keys. */
    const DEFAULT_NAMESPACE_PREFIX = '__ZM_NSP__';

    protected $internalStart_;
    protected $data_;
    protected $sessionHandler_;
    private $cookiePath_;
    private $closed_;
    private $domain_;
    private $useFqdn_;


    /**
     * Create new instance.
     *
     * <p>If an existing session is detected (via <code>isNew()</code>), the session is automatically started.</p>
     *
     * @param string domain Optional cookie domain; default is <code>null</code>.
     * @param string name Optional session name; default is <code>Session::DEFAULT_NAME</code>.
     * @param boolean secure Indicate whether the session cookie should be secure or not; default is <code>true</code>.
     */
    public function __construct($domain=null, $name=self::DEFAULT_NAME, $secure=false) {
        parent::__construct();
        $this->domain_ = $domain;
        $this->setName(null !== $name ? $name : self::DEFAULT_NAME);

        $this->internalStart_ = false;
        $this->useFqdn_ = true;
        $this->data_ = array();
        $this->sessionHandler_ = null;
        $this->closed_ = false;
        $this->cookiePath_ = '/';

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

            // session cookie
            ini_set('session.cookie_lifetime', 0);

            // XSS protection
            ini_set("session.cookie_httponly", true);

            // general protection
            $this->setSecureCookie($secure);
            ini_set("session.use_only_cookies", true);
        }
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
        foreach (Runtime::getContainer()->findTaggedServiceIds('zenmagick.http.session.persist') as $id => $args) {
            $service = $container->get($id);
            if ($service instanceof Serializable) {
                $this->setValue($id, serialize($service));
            }
        }
        $this->close();
    }


    /**
     * Adjust domain with respect to <em>useFqdn</em> flag.
     *
     * @param string domain The domain.
     * @return string The adjusted domain.
     */
    protected function adjustDomain($domain) {
        if (null == $domain) {
            return null;
        }

        $domainToken = explode('.', $domain);
        if (2 > count($domainToken) || $this->useFqdn_) {
            return $domain;
        } else {
            $tld = '';
            foreach ($domainToken as $dt) {
                if ('www' != $dt) {
                    $tld .= '.' . $dt;
                }
            }
            return substr($tld, 1);
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
        ini_set('session.cookie_path', $path);
        if ($this->isStarted()) {
            $this->container->get('logging')->warn(sprintf('session already started - ignoring; domain: %s, path: %s', $domain, $path));
            return;
        }
        session_set_cookie_params(0, $path, $domain);
        $this->cookiePath_ = $path;
    }

    /**
     * Control whether the cookie should be secure or not.
     *
     * @param boolean value The new value.
     */
    public function setSecureCookie($value) {
        if ($this->isStarted()) {
            throw new \RuntimeException('session already started');
        }
        ini_set("session.cookie_secure", $value);
    }

    /**
     * Set the use <em>fqdn</em> flag.
     *
     * @param boolean value The new value.
     */
    public function setUseFqdn($value) {
        $this->useFqdn_ = $value;
    }

    /**
     * Check if we have a session yet.
     *
     * @return boolean <code>true<code> if the session has been already started.
     */
    public function isStarted() {
        $id = session_id();
        $isStarted = !empty($id);

        if ($isStarted && !$this->internalStart_) {
            // started elsewhere, so sync data
            $this->data_ = array_merge($_SESSION, $this->data_);
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
            $this->setCookieParams($this->adjustDomain($this->domain_), $this->cookiePath_);
            $this->internalStart_ = true;
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
        //XXX:TODO: bad hack to avoid zc admin breakage
        $isZCAdmin = defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG && defined('EMAIL_ENCODING_METHOD');
        if (!$isZCAdmin && $this->isStarted() && !$this->closed_) {
            foreach ($this->data_ as $name => $value) {
                $_SESSION[$name] = $value;
            }
            if (0 == count($_SESSION)) {
                // get a new token
                $this->getToken(true);
            }
            session_write_close();
            $this->closed_ = true;
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
     * @param mixed default Optional default value if <code>$name</code> doesn't exist; default is <code>null</code>.
     * @return mixed The value or <code>$default</code>.
     */
    public function getValue($name, $namespace=null, $default=null) {
        if (!$this->isStarted() && !$this->isNew()) {
            // start only if not a new session
            $this->start();
        }
        if (null === $namespace) {
            return isset($this->data_[$name]) ? $this->data_[$name] : $default;
        } else {
            $namespace = self::DEFAULT_NAMESPACE_PREFIX.$namespace;
            if (isset($this->data_[$namespace])) {
                return isset($this->data_[$namespace][$name]) ? $this->data_[$namespace][$name] : $default;
            } else {
                return $default;
            }
        }
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
            $this->sessionHandler_ = $sessionHandler;
        }
    }

    /**
     * Get the current session handler.
     *
     * @return SessionHandler A session handler or <code>null</code>.
     */
    public function getSessionHandler() {
        return $this->sessionHandler_;
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
