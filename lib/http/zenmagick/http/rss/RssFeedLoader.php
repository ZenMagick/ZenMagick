<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * Portions Copyright (c)      Vojtech Semecky, webmaster @ webdot . cz
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * A RSS feed loader.
 *
 * <p>ZenMagick wrapper around <code>lastRSS</code>.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.rss
 */
class RssFeedLoader extends ZMObject {
    private $rss_;


    /**
     * Create a new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->rss_ = new lastRSS();
    }


    /**
     * Initialize.
     *
     * @param array config General loader config.
     */
    public function init($config) {
        \ZMFileUtils::mkdir($config['cacheDir']);
        if (!file_exists($config['cacheDir']) || !is_writeable($config['cacheDir'])) {
            Runtime::getLogging()->warn(sprintf('RSS cache dir not usable: %s', $config['cacheDir']));
        }
        $this->rss_->cache_dir = $config['cacheDir'];
        $this->rss_->cache_time = $config['cacheTTL'];
        $this->rss_->CDATA = 'strip';
    }

    /**
     * Get/load the feed.
     *
     * @param string url The feed url.
     * @param string category An optional category; default is <code>null</code>.
     * @param int limit An optional item limit; default is 5; use 0 for all.
     * @return RssFedd A <code>RssFeed</code> instance.
     */
    public function getFeed($url, $category=null, $limit=5) {
        // to filter we need all to start with
        $this->rss_->items_limit = null == $category ? $limit : 0;
        $rs = $this->rss_->Get($url);
        $feed = new RssFeed();
        if (null != $rs) {
            $feed->setChannel(new RssChannel($rs));
            $items = array();
            foreach ($rs['items'] as $item) {
                $item = new RssItem($item);
                if (null == $category || $category == $item->getCategory()) {
                    $items[] = $item;
                }
                if (0 != $limit && $limit <= count($items)) {
                    break;
                }
            }
            $feed->setItems($items);
        }
        return $feed;
    }

}
