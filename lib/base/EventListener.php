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
namespace zenmagick\base;

use Swift_Transport_AbstractSmtpTransport;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * BASE event listener.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EventListener extends ZMObject {

    /**
     * Additional config loading.
     */
    public function onBootstrapDone($event) {
        $key = 'zenmagick.base.email.host';
        // enable encryption for gmail smtp
        if ($this->container->getParameterBag()->has($key)) {
            if ('smtp.gmail.com' == $this->container->getParameterBag()->get($key)) {
                $this->container->getParameterBag()->set('zenmagick.base.email.encryption', 'tls');
            }
        }

        // load email container config unless we do have already some swiftmailer config
        $bundles = array_keys($this->container->get('settingsService')->get('zenmagick.bundles', array()));
        if (0 == count($this->container->getExtensionConfig('swiftmailer')) && in_array('SwiftmailerBundle', $bundles)) {
            $emailConfig = __DIR__.'/email.xml';
            if (file_exists($emailConfig)) {
                $containerLoader = new XmlFileLoader($this->container, new FileLocator(dirname($emailConfig)));
                $containerLoader->load($emailConfig);
            }
        }
    }

    /**
     * Cannot have a default for encryption, so need to set that explicitely.
     */
    public function onContainerReady($event) {
        if ($this->container->has('swiftmailer.transport')) {
            if (null != ($transport = $this->container->get('swiftmailer.transport')) && $transport instanceof Swift_Transport_AbstractSmtpTransport) {
                $transport->setEncryption($this->container->getParameterBag()->get('zenmagick.base.email.encryption'));
            }
        }
    }

}
