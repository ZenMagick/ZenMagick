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

use zenmagick\base\Beans;
use zenmagick\http\rss\RssSource;
use zenmagick\http\rss\RssFeedGenerator;

/**
 * Request controller for RSS feeds.
 *
 * <p>Feed content is taken from the first of the configured <code>RssSource</code> instances that returns data.</p>
 *
 * <p>Sources are registered via the container tag '<em>zenmagick.http.rss.source</em>'.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.controller
 */
class ZMRssController extends ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $channel = $request->getParameter('channel');
        $feedArgs = array('key' => $request->getParameter('key', null));

        // find source
        $feed = null;
        foreach ($this->container->findTaggedServiceIds('zenmagick.http.rss.source') as $id => $args) {
            $source = $this->container->get($id);
            if (null != ($feed = $source->getFeed($request, $channel, $feedArgs))) {
                break;
            }
        }

        if (null == $feed) {
            return $this->findView('error');
        }

        // create content
        $this->setContentType('application/xhtml+xml');
        $feedGenerator = new RssFeedGenerator();
        echo $feedGenerator->generate($request, $feed);

        return null;
    }

}
