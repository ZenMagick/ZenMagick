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
namespace ZenMagick\Http\Rss;

use ZenMagick\Base\ZMObject;

/**
 * A RSS feed.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RssFeed extends ZMObject
{
    private $channel;
    private $items;

    /**
     * Create new RSS feed.
     */
    public function __construct()
    {
        parent::__construct();
        $this->channel = null;
        $this->items = array();
    }

    /**
     * Returns <code>true</code> if contents is available.
     *
     * @return boolean <code>true</code> if feed items are available, <code>false</code>, if not.
     */
    public function hasContents()
    {
        return 0 != count($this->items);
    }
    /**
     * Get the channel.
     *
     * @return RssChannel The channel.
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Get the feed items.
     *
     * @return Iterator An iterator over <code>RssItem</code> instances.
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set the channel.
     *
     * @param RssChannel channel The channel.
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Set the feed items.
     *
     * @param Iterator items An iterator over <code>RssItem</code> instances.
     */
    public function setItems(\Iterator $items)
    {
        $this->items = $items;
    }

}
