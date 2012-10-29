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
namespace ZenMagick\StoreBundle\Rss;

use Iterator;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Rss\RssItem;

/**
 * Category RSS item iterator.
 *
 * @author DerManoMann
 */
class CatalogCategoryRssItemIterator extends ZMObject implements Iterator {
    private $categoryInfo;
    private $languageId;
    private $index;
    private $fullFeed;

    /**
     * Create new instance.
     *
     * @param array categoryInfo List of category info for categories to iterate.
     * @param int languageId The language id.
     * @param boolean fullFeed Optional flag to enable/disable full feed details; default is <code>true</code>.
     */
    public function __construct(array $categoryInfo, $languageId, $fullFeed=true) {
        parent::__construct();
        $this->categoryInfo = $categoryInfo;
        $this->languageId = $languageId;
        $this->index = 0;
        $this->fullFeed = $fullFeed;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind() {
        $this->index = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function current() {
        return $this->rssItem($this->categoryInfo[$this->index], $this->languageId, $this->fullFeed);
    }

    /**
     * {@inheritDoc}
     */
    public function key() {
        return $this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function next() {
        ++$this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function valid() {
        return isset($this->categoryInfo[$this->index]);
    }

    /**
     * Generate RSS item for the given category info.
     *
     * @param array categoryInfo The category info.
     * @param int languageId The language id.
     * @param boolean fullFeed Optional flag to enable/disable full feed details.
     * @return RssFeed The feed.
     */
    protected function rssItem($categoryInfo, $languageId, $fullFeed) {
        $category = $this->container->get('categoryService')->getCategoryForId($categoryInfo['id'], $languageId);
        $item = new RssItem();
        $item->setTitle($category->getName());
        $item->setLink($categoryInfo['url']);
        $html = $this->container->get('htmlTool');
        $desc = $html->strip($category->getDescription());
        if (!$full) {
            $desc = $html->more($desc, 60);
        }
        $item->setDescription($desc);
        $item->setPubDate($category->getDateAdded());
        $item->addTag('id', $category->getId());
        $item->addTag('path', implode('_', $category->getPath()));
        $item->addTag('children', array('id' => $category->getDecendantIds(false)));

        if ($full) {
            $item->addTag('type', 'category');
        }

        return $item;
    }

}
