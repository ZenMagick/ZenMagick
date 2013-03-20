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

namespace ZenMagick\ZenCartBundle\Compat;

/**
 * ZenCart compatible breadcrumb class
 */
class Breadcrumb
{
    private $trail;
    private $linkLastElement;

    /**
     * Constructor
     *
     * @param bool $linkLastElement link the last element in the trail
     *
     * @todo add back configuration define like:
     *       DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM == 'true'
     */
    public function __construct($linkLastElement = false)
    {
        $this->reset();
        $this->linkLastElement = $linkLastElement;
    }

    /**
     * Clear the trail element list
     */
    public function reset()
    {
        $this->trail = array();
    }

    /**
     * Add an element to the trail
     *
     * @param string $title
     * @param string $link
     */
    public function add($title, $link = '')
    {
        $element = array();
        $element['title'] = $title;
        if (!empty($link)) {
            $element['link'] = $link;
        }
        $this->trail[] = $element;
    }

    /**
     * Display the trail as a string
     *
     * It has been modified to use <li>
     * when the separator is not the default.
     *
     * @param string $separator
     * @return string
     */
    public function trail($separator = '&nbsp;&nbsp;')
    {
        $trail = $this->trail;
        $last = abs(count($trail) -1);
        if (!$this->linkLastItem && !empty($trail)) {
           unset($trail[$last]['link']);
        }

        $trailElements = array();
        foreach ($trail as $pos => $trailElement) {
            $result = $trailElement['title'];
            if (isset($trailElement['link'])) {
                $result = '<a href="'.$trailElement['link'].'">'.$trailElement['title'].'</a>';
            }
            if ($last != $pos) {
                $result .= $separator;
            }
            if ('&nbsp;&nbsp' != $separator) {
                $result = '<li>'.$result.'</li>';
            }
            $trailElements[] = $result;
        }

        return implode("\n", $trailElements);
    }

    /**
     * Get the title of the last trail element
     *
     * @return string
     */
    public function last()
    {
        $last = abs(count($this->trail) - 1);

        return $this->trail[$last]['title'];
    }
}
