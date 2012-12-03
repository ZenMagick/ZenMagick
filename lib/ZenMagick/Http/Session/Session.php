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
use ZenMagick\StoreBundle\Entity\Account\Account;
use ZenMagick\StoreBundle\Services\Account\Accounts;

use Symfony\Component\EventDispatcher\GenericEvent;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session as BaseSession;

/**
 * {@inheritDoc}
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo allow to expire session after a given time (will need cookie update for each request)
 * @todo remove this class altogether.
 */
class Session extends BaseSession implements ContainerAwareInterface
{
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
    public function start()
    {
        $started = parent::start();
        if ($started) {
            $this->restorePersistedServices();
        }

        return $started;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * persist tagged services.
     */
    protected function persistServices()
    {
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
    public function restorePersistedServices()
    {
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
    public function migrate($destroy = false, $lifetime = null)
    {
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
    public function save()
    {
        $this->persistServices();
        parent::save();
    }

   /**
     * {@inheritDoc}
     * @todo: drop
     */
    public function set($name, $value=null)
    {
        parent::set($name, $value);
        if (Runtime::isContextMatch('storefront')) {
            if (isset($_SESSION)) {
                $_SESSION[$name] = $value;
            }
        }
    }
    /**
     * {@inheritDoc}
     * @todo: drop
     */
    public function get($name, $default=null)
    {
        if (null != ($value = parent::get($name, $default))) {
            return $value;
        }
        if (Runtime::isContextMatch('storefront')) {
            if (isset($_SESSION) && array_key_exists($name, $_SESSION)) {
                return $_SESSION[$name];
            }
        }
        return $default;
    }

    /**
     * @see set()
     */
    public function setValue($name, $value=null)
    {
        $this->set($name, $value);
    }

    /**
     * @see get()
     */
    public function getValue($name, $default=null)
    {
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
    public function getToken($renew=false, $tokenKey=self::SESSION_TOKEN_KEY)
    {
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
     * @return mixed A user/credentials object. Default is <code>null</code>.
     */
    public function getAccount()
    {
        if (Runtime::isContextMatch('admin')) {
            if (null != ($adminId = $this->get('admin_id'))) {
                return $this->container->get('adminUserService')->getUserForId($adminId);
            }
        } else {
            if (null != ($accountId = $this->get('customer_id'))) {
                return $this->container->get('accountService')->getAccountForId($accountId);
            }
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
    public function getSelectedLanguage()
    {
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

    /**
     * Get the account id.
     *
     * @return int The account id for the currently logged in user or <code>0</code>.
     */
    public function getAccountId()
    {
        $accountId = $this->get('customer_id');

        return null !== $accountId ? $accountId : 0;
    }

    /**
     * Returns the current session type.
     *
     * <p>This type corresponds with the account type.</p>
     *
     * @return char The session type.
     */
    public function getType()
    {
        $type = $this->get('account_type');

        return null === $type ? Account::ANONYMOUS : $type;
    }

    /**
     * Returns <code>true</code> if the user is not logged in at all.
     *
     * <p>This is the lowest level of identity.</p>
     *
     * @return boolean <code>true</code> if the current user is anonymous, <code>false</code> if not.
     */
    public function isAnonymous() { return $this->getType() == Account::ANONYMOUS; }

    /**
     * Returns <code>true</code> if the user is a guest user.
     *
     * <p>This status level is in the middle between <em>registered</em> and <em>anonymous</em>.</p>
     *
     * @return boolean <code>true</code> if the current user is an guest, <code>false</code> if not.
     */
    public function isGuest() { return $this->getType() == Account::GUEST; }

    /**
     * Returns <code>true</code> if the user is a registered user.
     *
     * <p>This is the highest status level.</p>
     *
     * @return boolean <code>true</code> if the current user is registered, <code>false</code> if not.
     */
    public function isRegistered() { return $this->getType() == Account::REGISTERED; }

    /**
     * Set the account for the current session.
     *
     * @param ZenMagick\StoreBundle\Entity\Account\Account account The account.
     */
    public function setAccount($account)
    {
        if (null == $account) {
            $this->set('customer_id', '');
        } else {
            $this->set('customer_id', $account->getId());
            $this->set('customer_default_address_id', $account->getDefaultAddressId());
            $this->set('customers_authorization', $account->getAuthorization());
            $this->set('customer_first_name', $account->getFirstName());
            $this->set('account_type', $account->getType());
            $address = $this->container->get('addressService')->getAddressForId($account->getDefaultAddressId());
            if (null != $address) {
                $this->set('customer_country_id', $address->getCountryId());
                $this->set('customer_zone_id', $address->getZoneId());
            }
        }
    }

    /**
     * Restore the shopping cart contents.
     */
    public function restoreCart()
    {
        $cart = $this->get('cart');
        if (null != $cart) {
            //TODO:
            $cart->restore_contents();
        }
    }

    /**
     * Get the language.
     *
     * @return Language The language or <code>null</code>.
     */
    public function getLanguage()
    {
        $languageCode = $this->get('languages_code');
        $languageService = $this->container->get('languageService');

        return $languageService->getLanguageForCode($languageCode);
    }

    /**
     * Get the language id.
     *
     * @return int The current language id.
     */
    public function getLanguageId()
    {
        $languageId = $this->get('languages_id');

        return (null !== $languageId ? (int) $languageId : (int) Runtime::getSettings()->get('storeDefaultLanguageId'));
    }

    /**
     * Get the currency code.
     *
     * @return string The current currency code.
     */
    public function getCurrencyCode()
    {
        return $this->get('currency');
    }

    /**
     * Get the current language code.
     *
     * @return string The language code or <code>null</code>.
     */
    public function getLanguageCode()
    {
        if (null != ($language = $this->getLanguage())) {
            return $language->getCode();
        }

        return null;
    }

    /**
     * Register an account as user for this session.
     *
     * <p>This operation will fail, for example, if the account is blocked/disabled.</p>
     *
     * @param ZenMagick\StoreBundle\Entity\Account\Account account The account.
     * @param ZenMagick\Http\Request request The current request.
     * @param mixed source The event source; default is <code>null</code>.
     * @return boolean <code>true</code> if ok, <code>false</code> if not.
     */
    public function registerAccount($account, $request, $source=null)
    {
        if (Accounts::AUTHORIZATION_BLOCKED == $account->getAuthorization()) {
            $this->getFlashBag()->error(_zm('Access denied.'));

            return false;
        }

        // info only
        $this->container->get('event_dispatcher')->dispatch('login_success', new GenericEvent($this, array('controller' => $this, 'account' => $account, 'request' => $request)));

        // update session with valid account
        $this->setAccount($account);

        // update login stats
        $this->container->get('accountService')->updateAccountLoginStats($account->getId());

        // restore cart contents
        $this->container->get('shoppingCart')->setAccountId($account->getId());
        $this->restoreCart();

        return true;
    }
}
