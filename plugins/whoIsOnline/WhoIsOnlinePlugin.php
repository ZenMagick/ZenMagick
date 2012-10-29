<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\plugins\whoIsOnline;

use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Base\Runtime;

/**
 * Provide information about current site users.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo create an admin interface and widget.
 */
class WhoIsOnlinePlugin extends Plugin {

    /**
     * Get Table Mapper mappings
     *
     */
    public function setTableMappings() {
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
        $sql = "SELECT customer_id FROM %table.whos_online%";
        $results = \ZMRuntime::getDatabase()->fetchAll($sql, array(), 'whos_online', \ZenMagick\Base\Database\Connection::MODEL_RAW);
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
            $sql = "DELETE FROM %table.whos_online%
                    WHERE time_last_click < :lastRequestTime";
            \ZMRuntime::getDatabase()->updateObj($sql, array('lastRequestTime' => $timeAgo), 'whos_online');
    }

    /**
     * {@inheritDoc}
     *
     * @todo Should spiders be detected and marked here or output?
     *       Probably marked on output, but that's not how the current admin works
     * @todo Can we differentiate between spiders and regular guest users without using HTTP_USER_AGENT?
     */
    public function onContainerReady($event) {
        $this->setTableMappings();
        $this->removeExpired(); // @todo cron!

        if (Runtime::isContextMatch('admin')) return;

        $settingsService = $this->container->get('settingsService');
        $request = $event->getArgument('request');
        $session = $request->getSession();
        $accountId = $session->getAccountId();
        $sessionId = $session->getId();

        $ipAddress = $request->getClientIp();

        $fullName = 'Guest'; // used for zc admin page
        if (!empty($accountId) && !empty($sessionId)) {
            $account = $request->getAccount();
            $fullName = $account->getLastName().', '.$account->getFirstName();
        }

        if (empty($sessionId)) {
            // create hash
            $token = array($ipAddress, $this->server->get('HTTP_USER_AGENT'));
            $sessionId = md5(implode(':', $token));
        }

        $conn = \ZMRuntime::getDatabase();
        $sql = "SELECT customer_id FROM %table.whos_online%
                WHERE session_id = :sessionId AND ip_address = :ipAddress";
        $result = $conn->querySingle($sql, array('sessionId' => $sessionId, 'ipAddress' => $ipAddress), 'whos_online');

        $now = time();
        $data = array();
        $data['customer_id'] = (int) $accountId;
        $data['full_name'] = $fullName;
        $data['time_last_click'] = $now;
        $data['last_page_url'] = rtrim($request->getRequestUri(), '?');
        $data['user_agent'] = (string) $request->server->get('HTTP_USER_AGENT');
        if (!empty($result)) {
            $conn->update('whos_online', $data, array('session_id' => $sessionId));
        } else {
            $hostAddress = '';
            if ($settingsService->get('isResolveClientIP')) {
                // @todo we should probably only do this in the admin interface (when one exists)
                $hostAddress = @gethostbyaddr($ipAddress);
            }
            $data['ip_address'] = $ipAddress;
            $data['host_address'] = $hostAddress;
            $data['time_entry'] = $now;
            $data['session_id'] = $sessionId;
            $conn->insert('whos_online', $data);
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
        if ($event->hasArgument('account') && null != ($account = $event->getArgument('account'))) {
            if (0 < ($accountId = $account->getId())) {
                \ZMRuntime::getDatabase()->delete('whos_online', array('customer_id' => $accountId));
            }
        }
    }

    /**
     * Update the session id if it changed.
     */
    public function updateSessionId($event) {
        $session = $event->getArgument('request')->getSession();
        if (null != ($lastId = $session->get('lastSessionId'))) {
            \ZMRuntime::getDatabase()->update('whos_online',
                array('session_id' => $session->getId(), 'customer_id' => $event->getArgument('account')->getId()),
                array('session_id' => $lastId)
            );
        }
    }
}
