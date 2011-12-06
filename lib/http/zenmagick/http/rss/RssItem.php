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

use DateTime;
use zenmagick\base\ZMObject;

/**
 * A RSS feed item.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.rss
 */
class RssItem extends ZMObject {

    /**
     * Create new RSS item.
     *
     * @param array Array of item data.
     */
    public function __construct($item) {
        parent::__construct();
        if (is_array($item)) {
            foreach ($item as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the item title.
     *
     * @return string The item title.
     */
    public function getTitle() { return $this->get('title'); }

    /**
     * Get the item link.
     *
     * @return string The item link.
     */
    public function getLink() { return $this->get('link'); }

    /**
     * Get the item description.
     *
     * @return string The item description.
     */
    public function getDescription() { return $this->get('description'); }

    /**
     * Get the item category.
     *
     * @return string The item category.
     */
    public function getCategory() { return $this->get('category'); }

    /**
     * Get the item publish date.
     *
     * @return DateTime The item publish date.
     */
    public function getPubDate() {
        $pubDate = $this->get('pubDate');
        if ($pubDate instanceof DateTime) {
          return $pubDate;
        }

        return new DateTime($pubDate);
    }


    /**
     * Get a list of custom tags to be handled.
     *
     * @return array List of custom tags.
     */
    public function getTags() { return $this->get('tags', array()); }

    /**
     * Set the item title.
     *
     * @param string title The item title.
     */
    public function setTitle($title) { $this->set('title', $title); }

    /**
     * Set the item link.
     *
     * @param string link The item link.
     */
    public function setLink($link) { $this->set('link', $link); }

    /**
     * Set the item description.
     *
     * @param string description The item description.
     */
    public function setDescription($description) { $this->set('description', $description); }

    /**
     * set the item category.
     *
     * @param string category The item category.
     */
    public function setCategory($category) { $this->set('category', $category); }

    /**
     * Set the item publish date.
     *
     * @param string date The item publish date.
     */
    public function setPubDate($date) { $this->set('pubDate', $date); }

    /**
     * Set a list of custom tags to be handled.
     *
     * @param array tags List of custom tags.
     */
    public function setTags($tags) { $this->set('tags', $tags); }

}
