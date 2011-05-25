<?php if (function_exists('the_search_query')) { ?>
<h3><?php _vzm("Sidebar") ?></h3>
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
    <?php SidebarEventsCalendar(); ?>
</div>
<?php } ?>
