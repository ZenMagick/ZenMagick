<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Interface for for block content.
 *
 * @package org.zenmagick.plugins.blockHandler
 * @author DerManoMann
 */
interface ZMBlockContents {

    /**
     * Get the sort order for this block.
     *
     * @return int The sort order relative to the other blocks.
     */
    public function getSortOrder();
      

    /**
     * Get the name (for UI).
     *
     * @return string The name.
     */
    public function getName();

    /**
     * Get options map.
     *
     * <p>Query supported options.</p>
     *
     * <p>Values for custom options (or arguments) may be passed into the <code>getBlockContents()</code> method. Optionally, a UI
     * may exist to manage those and store them somewhere.</p>
     *
     * @return array A map of optionKey =&gt; Description of all supported options.
     */
    public function getOptionsMap();

    /**
     * Get the contents for this block.
     *
     * @param array args Optional arguments.
     * @return string The content.
     */
    public function getBlockContents($args);

}
