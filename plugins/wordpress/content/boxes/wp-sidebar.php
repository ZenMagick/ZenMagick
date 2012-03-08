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
?>
<h3><?php _vzm("WP-Sidebar") ?></h3>
<div id="sb_wp_sidebar" class="box">
    <form method="get" id="searchform" action="">
        <div>
          <input type="hidden" value="<?php echo '' ?>" name="main_page">
            <input type="text" value="<?php the_search_query(); ?>" name="s" id="s">
            <input type="submit" id="searchsubmit" value="Search">
        </div>
    </form>
    <ul>
      <?php wp_list_pages('title_li=<strong>Pages</strong>'); ?>

      <li><strong>Archives</strong>
        <ul>
          <?php wp_get_archives('type=monthly'); ?>
        </ul>
      </li>

      <?php wp_list_categories('show_count=1&title_li=<strong>Categories</strong>'); ?>

      <?php wp_list_bookmarks('title_li=Blogroll&title_before=<strong>&title_after=</strong>&categorize=0'); ?>
    </ul>
</div>
