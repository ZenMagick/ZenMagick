<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\plugins\whoIsOnline;

use Plugin;
use zenmagick\base\Runtime;


/**
 * Provide information about current site users.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo create an admin interface and widget.
 */
class WhoIsOnlinePlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Who\'s online', 'Provide inormation about current site users', '${plugin.version}');
    }


    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        \ZMRuntime::getDatabase()->getMapper()->setMappingForTable('whos_online', array(
            'accountId' => array('column' => 'customer_id', 'type' => 'integer'),
            'fullName' => array('column' => 'full_name', 'type' => 'string'),
            'sessionId' => array('column' => 'session_id', 'type' => 'string'),
            'ipAddress' => array('column' => 'ip_address', 'type' => 'string'),
            'sessionStartTime' => array('column' => 'time_entry', 'type' => 'string'),
            'lastRequestTime' => array('column' => 'time_last_click', 'type' => 'string'),
            'lastUrl' => array('column' => 'last_page_url', 'type' => 'string'),
            'hostAddress' => array('column' => 'host_address', 'type' => 'string'),
            'userAgent' => array('column' => 'user_agent', 'type' => 'string'),
        ));
        Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Get stats about currently online users.
     *
     * <p>The returned array map has three elements. One for the number of anonymous users (<em>anonymous</em>),
     on for the number of registered users online (<em>registered</em>) and the third the total number of online users (<em>total</em>).</p>
     *
     * @return array Online user stats.
     */
    public function getStats() {
        $sql = "SELECT customer_id FROM " . TABLE_WHOS_ONLINE;
        $results = \ZMRuntime::getDatabase()->fetchAll($sql, array(), TABLE_WHOS_ONLINE, \ZMDatabase::MODEL_RAW);
        $anonymous = 0;
        $registered = 0;
        foreach ($results as $result) {
            if (0 != $result['customer_id']) {
                ++$registered;
            } else {
                ++$anonymous;
            }
        }

        return array('anonymous' => $anonymous, 'registered' => $registered, 'total' => ($anonymous+$registered));
    }

    /**
     * Remove expired entries.
     *
     * @todo cron it!
     * @todo configurable expiry time.
     */
    public function removeExpired() {
            $timeAgo = (time() - 1200);
            $sql = "DELETE FROM " . TABLE_WHOS_ONLINE . "
                    WHERE time_last_click < :lastRequestTime";
            \ZMRuntime::getDatabase()->updateObj($sql, array('lastRequestTime' => $timeAgo), TABLE_WHOS_ONLINE);
    }

    /**
     * {@inheritDoc}
     *
     * @todo Should spiders be detected and marked here or output?
     *       Probably marked on output, but that's not how the current admin works
     * @todo Can we differentiate between spiders and regular guest users without using HTTP_USER_AGENT?
     */
    public function onContainerReady($event) {
        $this->removeExpired(); // @todo cron!

        if (Runtime::isContextMatch('admin')) return;

        $request = $event->get('request');
        $session = $request->getSession();
        $accountId = $request->getAccountId();
        $sessionId = $session->getId();

        $ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

        $fullName = 'Guest'; // used for zc admin page
        if (!empty($accountId) && !empty($sessionId)) {
            $account = $request->getAccount();
            $fullName = $account->getLastName().', '.$account->getFirstName();
        }

        if (empty($sessionId)) {
            // create hash
            $token = array(
                $ipAddress,
                (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '')
            );
            $sessionId = md5(implode(':', $token));
        }

        $conn = \ZMRuntime::getDatabase();
        $sql = "SELECT customer_id FROM " . TABLE_WHOS_ONLINE . "
                WHERE session_id = :sessionId AND ip_address = :ipAddress";
        $result = $conn->querySingle($sql, array('sessionId' => $sessionId, 'ipAddress' => $ipAddress), TABLE_WHOS_ONLINE);

        $now = time();
        $data = array();
        $data['customer_id'] = $accountId;
        $data['full_name'] = $fullName;
        $data['time_last_click'] = $now;
        $data['last_page_url'] = rtrim($_SERVER['REQUEST_URI'], '?');
        $data['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (!empty($result)) {
            $conn->update(TABLE_WHOS_ONLINE, $data, array('session_id' => $sessionId));
        } else {
            $data['ip_address'] = $ipAddress;
            $data['host_address'] = $session->getValue('customers_host_address') ?: '';
            $data['time_entry'] = $now;
            $data['session_id'] = $sessionId;
            $conn->insert(TABLE_WHOS_ONLINE, $data);
        }
    }

    /**
     * Login event handler.
     */
    public function onLoginSuccess($event) {
        $this->updateSessionId($event);
    }

    /**
     * Create account event handler.
     */
    public function onCreateAccount($event) {
        $this->updateSessionId($event);
    }

    /**
     * Logoff event handler.
     *
     * Just delete the db entry since we have no useful session id.
     *
     */
    public function onLogoffSuccess($event) {
        if ($event->has('account') && null != ($account = $event->get('account'))) {
            if (0 < ($accountId == $account->getId())) {
                \ZMRuntime::getDatabase()->delete('whos_online', array('customer_id' => $accountId));
            }
        }
    }

    /**
     * Update the session id if it changed.
     */
    public function updateSessionId($event) {
        $session = $event->get('request')->getSession();
        if (null != ($lastId = $session->getValue('lastSessionId', 'session'))) {
            \ZMRuntime::getDatabase()->update('whos_online',
                array('session_id' => $session->getId(), 'customer_id' => $event->get('account')->getId()),
                array('session_id' => $lastId)
            );
        }
    }
}
