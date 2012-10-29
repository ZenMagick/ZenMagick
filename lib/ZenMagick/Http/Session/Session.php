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
namespace ZenMagick\Http\Session;

use Serializable;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session as BaseSession;

/**
 * {@inheritDoc}
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo allow to expire session after a given time (will need cookie update for each request)
 */
class Session extends BaseSession implements ContainerAwareInterface {
    /** A magic session key used to validate forms. */
    const SESSION_TOKEN_KEY = 'securityToken';
    /** The auto save namespace prefix for session keys. */
    const AUTO_SAVE_KEY = '__ZM_AUTO_SAVE_KEY__';

    protected $container;

    /**
     * {@inheritDoc}
     *
     * This method restores persisted services.
     */
    public function start() {
        $started = parent::start();
        if ($started) {
            $this->restorePersistedServices();
        }
        return $started;
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
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
        $this->set(self::AUTO_SAVE_KEY, $autoSave);
    }

    /**
     * Restore persisted services.
     */
    public function restorePersistedServices() {
        // restore persisted services
        foreach ((array) $this->get(self::AUTO_SAVE_KEY) as $id => $serdat) {
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
     * {@inheritDoc}
     *
     * This method also regenerates a session token and persists
     * session container services
     *
     * @see persistServices()
     * @see parent::migrate()
     */
    public function migrate($destroy = false, $lifetime = null) {
        $lastSessionId = session_id();
        if (!$destroy) {
            $this->persistServices();
        }
        parent::migrate();
        if (!$destroy && !empty($lastSessionId)) {
            // regenerate token too
            $this->getToken(true);
            // keep old session id for reference
            $this->set('lastSessionId', $lastSessionId);
        }
    }

    /**
     * {@inheritDoc}
     *
     * Also persists container services.
     *
     * @see peristServices
     */
    public function save() {
        $this->persistServices();
        parent::save();
    }

    /**
     * @see parent:set()
     */
    public function setValue($name, $value=null) {
        $this->set($name, $value);
    }

    /**
     * @see parent::get()
     */
    public function getValue($name, $default=null) {
        return $this->get($name, $default);
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
        if ($renew || null == $this->get($tokenKey)) {
            // in this case we really want a session!
            if (!$this->isStarted()) {
                $this->start();
            }
            $this->set($tokenKey, md5(uniqid(rand(), true)));
        }

        return $this->get($tokenKey);
    }

    /**
     * Get the user (if any) for authentication.
     *
     * <p>Creation of the user object is delegated to the configured <code>ZenMagick\Http\Session\UserFactory</code> instance.
     * The factory may be configured as bean defintion via the setting 'zenmagick.http.session.userFactory'.</p>
     *
     * @return mixed A user/credentials object. Default is <code>null</code>.
     */
    public function getAccount() {
        if ($this->container->has('userFactory') && null != ($userFactory = $this->container->get('userFactory'))) {
            return $userFactory->getUser($this);
        }

        return null;
    }

    /**
     * Get the selected language.
     *
     * <p>Determine the currently active language, with respect to potentially selected language from a dropdown in admin UI.</p>
     *
     * @return ZMLanguage The selected language.
     * @todo REMOVE! very temporary
     */
    public function getSelectedLanguage() {
        $language = null;
        if (null != ($id = $this->get('languages_id'))) {
            $languageService = $this->container->get('languageService');
            // try session language code
            if (null == ($language = $languageService->getLanguageForId($id))) {
                // try store default
                $language = $languageService->getLanguageForId($this->container->get('settingsService')->get('storeDefaultLanguageId'));
            }
        }

        if (null == $language) {
            $this->container->get('logger')->warn('no default language found - using en as fallback');
            $language = Beans::getBean('apps\\store\\entities\\locale\\Language');
            $language->setId(1);
            $language->setDirectory('english');
            $language->setCode('en');
        }
        return $language;
    }



}
