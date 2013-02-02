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

use ZenMagick\Base\Runtime;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * Redirect to a url (safely)
 *
 * This function safely shuts down Symfony by making sure the
 * response and terminate kernel events are called.
 *
 * @param string $url The url to redirect to
 * @param int $response_code http response code (defaults to 302)
 */
function zen_redirect($url, $responseCode = 302)
{
    // fix accidental ampersands
    $url = str_replace(array('&amp;', '&&'), '&', $url);

    $container = Runtime::getContainer();
    $request = $container->get('request');

    if (ENABLE_SSL == 'true' && $request->isSecure()) {
        $url = str_replace('http://', 'https://', $url);
    }

    $response = new RedirectResponse($url, $responseCode);

    $httpKernel = $container->get('http_kernel');
    $event = new FilterResponseEvent(
        $httpKernel,
        $request,
        HttpKernelInterface::MASTER_REQUEST,
        $response
    );

    $dispatcher = $container->get('event_dispatcher');
    $dispatcher->dispatch(KernelEvents::RESPONSE, $event);
    $response->send();
    $httpKernel->terminate($request, $response);
    exit();
}

