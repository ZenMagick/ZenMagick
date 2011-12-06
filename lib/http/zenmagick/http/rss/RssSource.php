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
namespace zenmagick\http\rss;


/**
 * Source of RSS feed(s).
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.rss
 */
interface RssSource {

    /**
     * Get the feed.
     *
     * @param ZMRequest request The current request.
     * @param string channel The feed name.
     * @param array args Optional parameter; default is an empty array.
     * @return ZMRssFeed The feed or <code>null</code>.
     */
    public function getFeed($request, $channel, $args=array());

}
