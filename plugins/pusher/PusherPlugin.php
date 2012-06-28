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
namespace zenmagick\plugins\pusher;

use Plugin;
use zenmagick\base\Toolbox;
use zenmagick\http\view\TemplateView;

use Pusher\Pusher;

/**
 * Pusher plugin.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PusherPlugin extends Plugin {
    const EVENT_QUEUE_HISTORY_CACHE_KEY = 'zenmagick.plugins.pusher.EventQueueHistory';
    private $pusher;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Pusher', 'Adds pusher support to the site.', '${plugin.version}');
        $this->setContext('storefront');
        $this->pusher = null;
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('App Id', 'appId', '', 'Your Application Id');
        $this->addConfigValue('App Key', 'appKey', '', 'Your Application Key');
        $this->addConfigValue('App Secret', 'appSecret', '', 'Your Application Secret');
        $this->addConfigValue('Pusher Version', 'pusherVersion', '1.12', 'Pusher API version');
        // should be possible to add blocks of these: all different channel per page
        $this->addConfigValue('Activity Stream', 'activityStream', 'site_activity_stream', 'Container (ul) id for activity stream (leave emtpy do disable)');
        $this->addConfigValue('Channel', 'channel', 'test_channel', 'The channel to subscribe to');
        $this->addConfigValue('Events', 'events', 'my_event', 'The subscribed events (comma separated)');
        $this->addConfigValue('Event Handler', 'eventHandler', 'PusherActivityStreamer.stringActivityHandler', 'JavaScript event handler');
        $this->addConfigValue('Max items', 'maxItems', '10', 'Maximum number of items to display');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        $this->container->get('eventDispatcher')->listen($this);
    }

    /**
     * Inject scripts.
     */
    public function onViewStart($event) {
        $view = $event->get('view');
        if ($view instanceof TemplateView) {
            // got content, so lets see what we need to add
            $resourceManager = $view->getResourceManager();
            if ($this->get('activityStream')) {
                $resourceManager->cssFile('css/activity-streams.css');

                //$resourceManager->jsFile(sprintf('//js.pusher.com/%s/pusher.min.js', $this->get('pusherVersion')), $resourceManager::FOOTER);
                $resourceManager->jsFile('js/pusher.1.11.min.js', $resourceManager::FOOTER);
                $resourceManager->jsFile('js/PusherActivityStreamer.js', $resourceManager::FOOTER);
            }

            // also provide a event queue history to pre-populate
            $cache = $this->container->get('persistentCache');
            if (null === ($eventQueueHistory = $cache->lookup(self::EVENT_QUEUE_HISTORY_CACHE_KEY))) {
                  $eventQueueHistory = array();
            }
            $view->setVariable('eventQueueHistory', (array) $eventQueueHistory);
        }
    }

    /**
     * Inject streamer.
     */
    public function onFinaliseContent($event) {
        $code = null;
        $appKey = $this->get('appKey');
        $activityStream = $this->get('activityStream');
        if (!empty($activityStream)) {
            $channel = trim($this->get('channel'));
            $events = implode("', '", explode(',', trim($this->get('events'))));
            $handler = trim($this->get('eventHandler'));
            $maxItems = (int) $this->get('maxItems');
            $code = <<<EOT
<script type="text/javascript">
  var pusher = new Pusher('$appKey');
  var channel = pusher.subscribe('$channel');
  new PusherActivityStreamer(channel, document.getElementById('$activityStream'), { maxItems: $maxItems, events: ['$events'], handler: '$handler' });
</script>
EOT;
        }

        if ($code) {
            $content = $event->get('content');
            $content = preg_replace('/<\/body>/', $code . '</body>', $content, 1);
            $event->set('content', $content);
        }
    }

    /**
     * Get a pusher instance.
     */
    public function getPusher() {
        if (null == $this->pusher) {
            $this->pusher = new Pusher($this->get('appKey'), $this->get('appSecret'), $this->get('appId'));
        }

        return $this->pusher;
    }

    /**
     * Push an event.
     *
     * @param event The event name.
     * @param string data The event data.
     */
    public function pushEvent($event, $data) {
        $maxItems = $this->get('maxItems');
        $cache = $this->container->get('persistentCache');
        if (null === ($eventQueueHistory = $cache->lookup(self::EVENT_QUEUE_HISTORY_CACHE_KEY))) {
              $eventQueueHistory = array();
        }
        $eventQueueHistory[] = array('type' => $event, 'data' => $data);
        if (count($eventQueueHistory) > $maxItems) {
            array_shift($eventQueueHistory);
        }

        $cache->save($eventQueueHistory, self::EVENT_QUEUE_HISTORY_CACHE_KEY);

        $this->getPusher()->trigger($this->get('channel'), $event, $data);
    }

}
