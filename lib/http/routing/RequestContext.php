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

use zenmagick\base\Runtime;

/**
 * ZenMagick request context, populated from <code>ZMRequest</code>.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RequestContext extends SymfonyRequestContext {

    /**
     * Create new instance.
     */
    public function __construct($request) {
        $frontendController = '';
        $settingsService = Runtime::getSettings();
        if ('path' == $settingsService->get('zenmagick.http.request.urlType', 'default')) {
            $frontendController = '/'.$settingsService->get('zenmagick.http.request.handler', 'index.php');
        }
        // todo: should ports be configurable?
        // todo: have a separate https hostname
        parent::__construct($request->getContext().$frontendController, $request->getMethod(), $request->getHostname(), $request->isSecure() ? 'https' : 'http', 80, 443);
    }

}
