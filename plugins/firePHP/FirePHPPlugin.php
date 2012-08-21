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
namespace zenmagick\plugins\firePHP;

use zenmagick\apps\store\plugins\Plugin;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\logging\Logging;

/**
 * FirePHP support for ZenMagick.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class FirePHPPlugin extends Plugin {

    /**
     * Set log level.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        if (Toolbox::asBoolean($this->get('isOnDemand'))) {
            if (null != $request->query->get($this->get('onDemandName'))) {
                // make sure logging is enabled
                Runtime::getSettings()->set('zenmagick.base.logging.enabled', true);
                $this->container->get('firePHPLoggingHandler')->setLogLevel($this->get('logLevel'));
            }
        } else {
            $this->container->get('firePHPLoggingHandler')->setLogLevel($this->get('logLevel'));
        }
    }

}
