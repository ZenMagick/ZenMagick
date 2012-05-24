<?php
/**
 * File contains just the base class
 *
 * @package classes
 * @copyright Copyright 2003-2009 Zen Cart Development Team
 * @copyright Johnny Robeson <johnny@localmomentum.net>
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: class.base.php 14535 2009-10-07 22:16:19Z wilt $
 */
/**
 * Base class for ZenCart
 *
 * This class has been modified to use static properites to look nicer and
 * fix an E_STRICT error in regards to passing variables by reference.
 * while still being compatible with the original.
 *
 * It has been modified to pass certain events to ZenMagick.
 * See notifyZenMagick() for details.
 *
 *
 */
class base {

    protected static $observers;

    /**
     * Attach an observer to the notifier object
     *
     * NB. We have to get a little sneaky here to stop session based classes adding events ad infinitum
     * To do this we first concatenate the class name with the event id, as a class is only ever going to attach to an
     * event id once, this provides a unigue key. To ensure there are no naming problems with the array key, we md5 the unique
     * name to provide a unique hashed key.
     *
     * @param object objserver Reference to the observer class
     * @param array eventIds An array of event ids to observe
     */
    public function attach(&$observer, $eventIds) {
        foreach ($eventIds as $eventId) {
            $nameHash = md5(get_class($observer).$eventId);
            self::setStaticObserver($nameHash, array('obs' => &$observer, 'eventId' => $eventId));
        }
    }

    /**
     * Detach an observer from the notifier object
     *
     * @param object observer
     * @param array eventIds
     */
    function detach($observer, $eventIds) {
        foreach ($eventIds as $eventId) {
            $nameHash = md5(get_class($observer).$eventId);
            self::unsetStaticObserver($nameHash);
        }
    }

    /**
     * Notify observers that an event as occurred in the notifier object
     *
     * @param string eventId The event ID to notify for
     * @param array params paramters to pass to the observer, useful for passing stuff which is outside of the 'scope' of the observed class.
     */
    public function notify($eventId, $params = array()) {
        // Tell ZenMagick about the event
        $this->notifyZenMagick($eventId, $params);
        $observers = (array)self::getStaticObserver();
        foreach ($observers as $hash => $observer) {
            if ($observer['eventId'] == $eventId) {
                $observer['obs']->update($this, $eventId, $params);
            }
        }
    }

    /**
     * Tell ZenMagick too
     */
    public function notifyZenmagick($eventId, $params = array()) {
        $container = zenmagick\base\Runtime::getContainer();
        if (!$container->has('themeService')) return;
        $themeMeta = $container->get('themeService')->getActiveTheme()->getConfig('meta');
        if (isset($themeMeta['zencart'])) {
            if (0 === strpos($eventId, 'NOTIFY_HEADER_START_')) {
                $controllerId = str_replace('NOTIFY_HEADER_START_', '', $eventId);
                $params = array_merge($params, array('controllerId' => $controllerId, 'request' => $container->get('request')));
                zenmagick\base\Runtime::getEventDispatcher()->dispatch('controller_process_start', new zenmagick\base\events\Event($this, $params));
            } else if (0 === strpos($eventId, 'NOTIFY_HEADER_END_')) {
                $controllerId = str_replace('NOTIFY_HEADER_END_', '', $eventId);
                $params = array_merge($params, array('controllerId' => $controllerId, 'request' => $container->get('request')));
                zenmagick\base\Runtime::getEventDispatcher()->dispatch('controller_process_end', new zenmagick\base\events\Event($this, $params));
            }
        }
    }

    protected static function getStaticObserver() {
        return self::$observers;
    }

    protected static function setStaticObserver($element, $value) {
        self::$observers[$element] = $value;
    }

    protected static function unsetStaticObserver($element) {
        unset(self::$observers[$element]);
    }
}
