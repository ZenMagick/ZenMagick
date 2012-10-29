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

use DateTime;
use ZenMagick\Base\ZMObject;

/**
 * A RSS feed channel.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RssChannel extends ZMObject {

    /**
     * Create new RSS channel.
     *
     * @param array Channel data; default is an empty array.
     */
    public function __construct(array $rs=array()) {
        parent::__construct();
        if (is_array($rs)) {
            foreach ($rs as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Get the channel title.
     *
     * @return string The channel title.
     */
    public function getTitle() { return $this->get('title'); }

    /**
     * Get the channel link.
     *
     * @return string The channel link.
     */
    public function getLink() { return $this->get('link'); }

    /**
     * Get the channel encoding.
     *
     * @return string The channel encoding.
     */
    public function getEncoding() { return $this->get('encoding'); }

    /**
     * Get the channel description.
     *
     * @return string The channel description.
     */
    public function getDescription() { return $this->get('description'); }

    /**
     * Get the channels last build date.
     *
     * @return DateTime The channels last build date.
     */
    public function getLastBuildDate() {
        $lastBuildDate = $this->get('lastBuildDate');
        if ($lastBuildDate instanceof DateTime) {
          return $lastBuildDate;
        }

        return new DateTime($lastBuildDate);
    }

    /**
     * Get the channels image title.
     *
     * @return string The channels image title.
     */
    public function getImageTitle() { return $this->get('image_title'); }

    /**
     * Get the channels image link.
     *
     * @return string The channels image link.
     */
    public function getImageLink() { return $this->get('image_link'); }

    /**
     * Get the channels image width.
     *
     * @return string The channels image width.
     */
    public function getImageWidth() { return $this->get('image_width'); }

    /**
     * Get the channels image height.
     *
     * @return string The channels image height.
     */
    public function getImageHeight() { return $this->get('image_height'); }

    /**
     * Get a list of custom tags to be handled.
     *
     * @return array List of custom tags.
     */
    public function getTags() { return $this->get('tags', array()); }

    /**
     * Checks if the channel has an image.
     *
     * @return boolean <code>true</code> if a channel image is available, <code>false</code> if not.
     */
    public function hasImage() { return array_key_exists('image_url', $this->getPropertyNames()); }

    /**
     * Set the channel title.
     *
     * @param string title The channel title.
     */
    public function setTitle($title) { $this->set('title', $title); }

    /**
     * Set the channel link.
     *
     * @param string link The channel link.
     */
    public function setLink($link) { $this->set('link', $link); }

    /**
     * Set the channel encoding.
     *
     * @param string encoding The channel encoding.
     */
    public function setEncoding($encoding) { $this->set('encoding', $encoding); }

    /**
     * Set the channel description.
     *
     * @param string description The channel description.
     */
    public function setDescription($description) { $this->set('description', $description); }

    /**
     * Set the channels last build date.
     *
     * @param string date The channels last build date.
     */
    public function setLastBuildDate($date) { $this->set('lastBuildDate', $date); }

    /**
     * Set the channels image title.
     *
     * @param string title The channels image title.
     */
    public function setImageTitle($title) { $this->set('image_title', $title); }

    /**
     * set the channels image link.
     *
     * @param string link The channels image link.
     */
    public function setImageLink($lin) { $this->set('image_link', $link); }

    /**
     * set the channels image width.
     *
     * @param int width The channels image width.
     */
    public function setImageWidth($width) { $this->set('image_width', $width); }

    /**
     * Set the channels image height.
     *
     * @param int height The channels image height.
     */
    public function setImageHeight($height) { $this->set('image_height', $height); }

    /**
     * Set a list of custom tags to be handled.
     *
     * @param array tags List of custom tags.
     */
    public function setTags($tags) { $this->set('tags', $tags); }

    /**
     * Add tag.
     *
     * <p>Shortcut for adding the tag name and setting the value in one go.</p>
     *
     * @param string name The tag name.
     * @param mixed value The tag value.
     */
    public function addTag($name, $value) {
        $tags = $this->getTags();
        $tags[] = $name;
        $this->setTags($tags);
        $this->set($name, $value);
    }

}
