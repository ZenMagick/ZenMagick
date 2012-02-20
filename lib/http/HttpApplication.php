<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\http;

use Exception;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\Application;

/**
 * HTTP application
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class HttpApplication extends Application {

    /**
     * Create new application
     *
     * @param array config Optional config settings.
     */
    public function __construct(array $config=array()) {
        parent::__construct($config);
        $this->config['packages'] = array_merge($this->config['packages'], array('lib/http', 'lib/mvc'));
        $this->config['eventListener'][] = 'zenmagick\http\EventListener';
    }


    /**
     * Init the bootstrap config.
     */
    protected function initBootstrap() {
        parent::initBootstrap();
        $this->bootstrap['b33'] = array('type' => 'method', 'list' => array('initRequest'));
        $this->bootstrap['b34'] = array('type' => 'event', 'list' => array('request_ready'));
    }

    /**
     * {@inheritDoc}
     */
    protected function fireEvent($eventName, array $parameter=array()) {
        if (in_array($eventName, array('request_ready', 'container_ready'))) {
            $parameter['request'] = Runtime::getContainer()->get('request');
        }
        parent::fireEvent($eventName, $parameter);
    }

    /**
     * Init request.
     */
    protected function initRequest() {
        Runtime::getContainer()->get('request');
    }

    /**
     * Handle web request.
     */
    public function serve() {
        try {
            $container = Runtime::getContainer();
            $settingsService = $container->get('settingsService');
            $request = $container->get('request');

            // allow seo rewriters to fiddle with the request
            $request->urlDecode();
            $this->profile('exit: urlDecode');

            // make sure we use the appropriate protocol (HTTPS, for example) if required
            $container->get('sacsManager')->ensureAccessMethod($request);

            // form validation
            $applicationPath = $this->config['applicationPath'];
            $validationConfig = $applicationPath.'/config/validation.yaml';
            if ($container->has('validator') && file_exists($validationConfig)) {
                $container->get('validator')->load(file_get_contents(Toolbox::resolveWithEnv($validationConfig)));
            }

            // reset as other global code migth fiddle with it...
            $this->fireEvent('init_done', array('request' => $request));
            $this->profile(sprintf('finished event: %s', 'init_done'));

            $container->get('dispatcher')->dispatch($request);
        } catch (Exception $e) {
            die(sprintf('serve failed: %s', $e->getMessage()));
        }
    }

}
