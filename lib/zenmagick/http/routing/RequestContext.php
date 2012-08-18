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
namespace zenmagick\http\routing;

use Symfony\Component\Routing\RequestContext as SymfonyRequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ZenMagick request context, populated from <code>zenmagick\http\Request</code>.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RequestContext extends SymfonyRequestContext {

    /**
     * Create new instance.
     */
    public function __construct(ContainerInterface $container) {
        $frontendController = '';
        $request = $container->get('request');
        $settingsService = $container->get('settingsService');
        if ('path' == $settingsService->get('zenmagick.http.request.urlType', 'default')) {
            $frontendController = '/'.basename($request->getScriptName());
        }
        // todo: should ports be configurable?
        // todo: have a separate https hostname
        parent::__construct($request->getContext().$frontendController, $request->getMethod(), $request->getHost(), $request->isSecure() ? 'https' : 'http', 80, 443);
    }

}
