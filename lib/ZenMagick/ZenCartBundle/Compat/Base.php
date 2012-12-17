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

namespace ZenMagick\ZenCartBundle\Compat;

use Symfony\Component\EventDispatcher\GenericEvent;

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
abstract class Base
{
    protected static $observers;

    public function getContainer()
    {
        return \ZenMagick\Base\Runtime::getContainer();
    }

    protected function getRequest()
    {
        return $this->getContainer()->get('request');
    }

    /**
     * Get the current "main_page" route
     *
     * @return string
     */
    protected function getMainPage()
    {
        return $this->getRequest()->attributes->get('_route');
    }

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
    public function attach(&$observer, $eventIds)
    {
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
    public function detach($observer, $eventIds)
    {
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
    public function notify($eventId, $params = array())
    {
        $observers = (array) self::getStaticObserver();
        foreach ($observers as $hash => $observer) {
            if ($observer['eventId'] == $eventId) {
                $observer['obs']->update($this, $eventId, $params);
                $dispatcher = $this->getContainer()->get('event_dispatcher');
                if (!is_array($params)) {
                    $params = array($params);
                }
                $dispatcher->dispatch($eventId, new GenericEvent($this, $params));
            }
        }
    }

    protected static function getStaticObserver()
    {
        return self::$observers;
    }

    protected static function setStaticObserver($element, $value)
    {
        self::$observers[$element] = $value;
    }

    protected static function unsetStaticObserver($element)
    {
        unset(self::$observers[$element]);
    }
}
