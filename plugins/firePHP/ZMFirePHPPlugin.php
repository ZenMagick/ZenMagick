<?php
/*
 * ZenMagick - Smart e-commerce
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

use zenmagick\base\Runtime;

/**
 * FirePHP support for ZenMagick.
 *
 * @package org.zenmagick.plugins.firePHP
 * @author DerManoMann
 */
class ZMFirePHPPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('FirePHP', 'Adds FirePHP support to ZenMagick', '${plugin.version}');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Enable on demand only', 'isOnDemand', 'false', 'If set, the plugin will be inactive unless the configured query parameter is set',
            'widget@ZMBooleanFormWidget#name=isOnDemand&default=false&label=Enable on demand only&style=radio');
        $this->addConfigValue('On demand query parameter name', 'onDemandName', 'firephp', 'The name of the query parameter to enable FirePHP.');
        $this->addConfigValue('On demand log level', 'onDemandLogLevel', ZMLogging::TRACE, 'The log level to be used for on deman logging.',
            'widget@ZMSelectFormWidget#name=onDemandLogLevel&default='.ZMLogging::TRACE.'false&options='.urlencode(
                ZMLOGGING::ERROR.'=Error&'.
                ZMLOGGING::WARN.'=Warn&'.
                ZMLOGGING::INFO.'=Info&'.
                ZMLOGGING::DEBUG.'=Debug&'.
                ZMLOGGING::TRACE.'=Trace'
            ));
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        zenmagick\base\Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Handle init request.
     */
    public function onInitRequest($event) {
        $request = $event->get('request');
        $settings = Runtime::getSettings();
        if (ZMLangUtils::asBoolean($this->get('isOnDemand'))) {
            if (null != $request->getParameter($this->get('onDemandName'))) {
                // enable logging
                $settings->set('zenmagick.base.logging.enabled', true);
                $settings->set('zenmagick.base.logging.level', (int)$this->get('onDemandLogLevel'));
            }
        }
    }

}
