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

use zenmagick\base\Runtime;

/**
 * Example plugin to illustrate a few key points of the ZenMagick plugin architecture.
 *
 * @package org.zenmagick.plugins.examplePlugin
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMExamplePluginPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('ZenMagick Example Plugin', 'This is the ZenMagick Example Plugin');
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Plugin true/false', 'rq1key1', 'true', 'Select true or false',
            'widget@booleanFormWidget#name=rq1key1&default=false&label=Select true or false&style=radio');
        $this->addConfigValue('Plugin text config', 'rq1key2', 'doh', 'Some text');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        /*
         * this is the place to do init stuff other than just setting up the infrastructure
         */

        // set up as event subscriber
        Runtime::getEventDispatcher()->listen($this);

        // add admin page
        $this->addMenuItem2(_zm('Example Plugin Admin Page'), 'examplePluginAdmin');
    }

    /**
     * Handle container ready event.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
    }

    /**
     * As event listener a class is also automatically registered as listener for zen-cart zco events.
     */
    public function onNotifyHeaderStartIndex($event) {
        echo "Start of Zen Cart's index page event callback in " . $this->getName() . " ...<br>";
    }

    /**
     * Handle final content.
     */
    public function onFinaliseContent($event) {
        $content = $event->get('content');
        $request = $event->get('request');

        if ('login' == $request->getRequestId()) {
            $content = preg_replace('/<\/h1>/', ' (modified by ' . $this->getName() . ')</h1>', $content, 1);
            $event->set('content', $content);
        }
    }

}
