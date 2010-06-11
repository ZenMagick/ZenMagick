<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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


/**
 * Source of RSS feed(s).
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.rss
 */
interface ZMRssSource {

    /**
     * Get the feed.
     *
     * @param ZMRequest request The current request.
     * @param string channel The feed name.
     * @param string key Optional key in case a source supports more than one feed; default is <code>null</code>.
     * @return ZMRssFeed The feed or <code>null</code>.
     */
    public function getFeed($request, $channel, $key=null);

}
