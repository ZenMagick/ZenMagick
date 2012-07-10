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
namespace zenmagick\http;

use Exception;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * HTTP application
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class HttpApplication extends Application implements HttpKernelInterface {
    /**
     * Handle web request.
     */
    public function handle(\Symfony\Component\HttpFoundation\Request $request, $type = self::MASTER_REQUEST, $catch = true) {
        try {
            if (false === $this->booted) $this->boot();
            $container = Runtime::getContainer();
            $settingsService = $container->get('settingsService');
            $request = $container->get('request'); // @todo use it from the argument :)
            // allow seo rewriters to fiddle with the request
            $this->profile('enter urlDecode');
            foreach ($request->getUrlRewriter() as $rewriter) {
                if ($rewriter->decode($request)) break; // traditional ZenMagick routing
            }
            $this->profile('exit: urlDecode');

            // make sure we use the appropriate protocol (HTTPS, for example) if required
            $container->get('sacsManager')->ensureAccessMethod($request);

            // form validation
            $this->profile('enter initValidator');
            $applicationPath = $this->config['applicationPath'];
            $validationConfig = $applicationPath.'/config/validation.yaml';
            if ($container->has('validator') && file_exists($validationConfig)) {
                $container->get('validator')->load(file_get_contents(Toolbox::resolveWithEnv($validationConfig)));
            }
            $this->profile('exit initValidator');

            // reset as other global code migth fiddle with it...
            $this->profile(sprintf('fire event: %s', 'init_done'));
            $this->fireEvent('init_done', array('request' => $request));
            $this->profile(sprintf('finished event: %s', 'init_done'));

            $this->profile('enter dispatcher');
            return $container->get('dispatcher')->dispatch($request);
        } catch (Exception $e) {
            if (false === $catch) {
                throw $e;
            }
            return new Response(sprintf('serve failed: %s', $e->getMessage()), 500);
        }
    }
}
