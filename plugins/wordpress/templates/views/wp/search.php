<?php if (have_posts()) : ?>

  <h2 style="text-align:left;" class="pagetitle">Search Results</h2>

  <div class="navigation">
    <div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
    <div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
  </div>

  <?php while (have_posts()) : the_post(); ?>

    <div class="post">
      <h3 id="post-<?php the_ID(); ?>"><a style="float:none;" href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
      <small><?php the_time('l, F jS, Y') ?></small>

      <p class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
    </div>

  <?php endwhile; ?>

  <div class="navigation">
    <div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
    <div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
  </div>

<?php else : ?>

  <h2 style="text-align:left;">No posts found. Try a different search?</h2>
  <form method="get" id="searchform" action="/wp">
      <div>
          <input type="text" value="<?php the_search_query(); ?>" name="s" id="s">
          <input type="submit" id="searchsubmit" value="Search">
      </div>
  </form>

<?php endif; ?>
