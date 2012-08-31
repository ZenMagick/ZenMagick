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
namespace ZenMagick\http\controller;

use Symfony\Component\HttpFoundation\Response;
use ZenMagick\base\ZMObject;
use ZenMagick\http\Request;
use ZenMagick\http\rss\RssSource;
use ZenMagick\http\rss\RssFeedGenerator;

/**
 * Request controller for RSS feeds.
 *
 * <p>Feed content is taken from the first of the configured <code>RssSource</code> instances that returns data.</p>
 *
 * <p>Sources are registered via the container tag '<em>zenmagick.http.rss.source</em>'.</p>
 *
 * <p>Routing for this controller should look something like this:</p>
 * <pre>
 *   &lt;route id="rss" pattern="/rss/{channel}/{key}">
 *      &lt;default key="_controller">ZenMagick\http\controller\RssController:generate&lt;default>
 *      &lt;default key="key">&lt;default>
 *   &lt;route>
 * </pre>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo caching
 */
class RssController extends ZMObject {

    /**
     * {@inheritDoc}
     */
    public function generate(Request $request, $channel, $key) {
        // find source
        $feed = null;
        $key = empty($key) ? null : $key;
        foreach ($this->container->get('containerTagService')->findTaggedServiceIds('zenmagick.http.rss.source') as $id => $args) {
            $source = $this->container->get($id);
            if (null != ($feed = $source->getFeed($request, $channel, array('key' => $key)))) {
                break;
            }
        }

        if (null == $feed) {
            return 'error';
        }

        // create content
        $feedGenerator = new RssFeedGenerator();
        return new Response($feedGenerator->generate($request, $feed), 200, array('Content-Type' => 'application/rss+xml'));
    }

}
