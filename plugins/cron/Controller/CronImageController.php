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
namespace ZenMagick\plugins\cron\Controller;

use ZMController;

use Symfony\Component\HttpFoundation\Response;

/**
 * Cron image controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CronImageController extends ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $plugin = $this->container->get('pluginService')->getPluginForId('cron');
        $response = new Response();
        $response->headers->set('Content-Type', 'image/gif');

        if (null != $plugin) {
            // execute configured jobs
            $plugin->runCron();
        }

        // create 1x1 image
        $response->setContent(base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='));
        return $response;
    }

}
