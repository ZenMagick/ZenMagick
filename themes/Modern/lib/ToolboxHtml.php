<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
namespace zenmagick\apps\store\themes\modern;


/**
 * HTML utilities.
 *
 * @author DerManoMann
 */
class ToolboxHtml extends \zenmagick\apps\store\toolbox\ToolboxHtml {

	/**
     * Create a full HTML &lt;a&gt; tag pointig to an ezpage.
     *
     * <p>Since the link text may be HTML, no HTML escaping is done on the <code>$text</code> parameter.</p>
     *
     * @param integer id The EZ page id.
     * @param string text Optional link text; default is <code>null</code> to use the ezpage title as link text.
     * @param array attr Optional HTML attribute map; default is an empty array().
     * @return string A full HTML link.
     */
    public function ezpageLink($id, $text=null, $attr=array()) {
        $toolbox = $this->getToolbox();
        $page = $this->container->get('ezPageService')->getPageForId($id, $this->getRequest()->getSession()->getLanguageId());
        $link = '<a href="' . $toolbox->net->ezPage($page) . '"' . $this->hrefTarget($page->isNewWin());
        foreach ($attr as $name => $value) {
            if (null !== $value) {
                $link .= ' '.$name.'="'.$value.'"';
            }
        }
        $link .=  '><span class="navEZCol">' . (null == $text ? $this->encode($page->getTitle()) : $text) . '</span></a>';

        return $link;
    }


}
