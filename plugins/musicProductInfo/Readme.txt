This is the ZenMagick plugin implementing the product_music_info product template.

Originally, this code was part of ZenMagick core, but got extracted into a 
separate plugin as it is only useful for stores selling music online.


Installation
============
1) Download (obvious ;)
   Download the latest version from http://www.zenmagick.org/
2) Extract into the ZenMagick plugins directory
3) Install the plugin via the ZenMagick plugins admin page
4) Done!


Usage
=====
The code included in this plugin can be used to display product information specific
to music like genre, publisher, etc.

Historically this code is used in a product template named 'product_music_info'. It is part
of the original zen-cart store setup, so it should be already configured in your database.
You can also create a new product type (and template) by logging in as admin and selecting
Catalog -> Product Types

Once you have determined the template name, it's probably easiest to copy the existing
product template (product_info.php) to music_product_info.php and use it as basis for your new music template.

For music products, or products configured with the music_product_info view template, the following additional 
view vars will be made available:
* musicManager - The manager service (usually not really needed)
* artis - Artist information
* collections - The available collections

The following code can be used to display music related information:

  <?php if ($artist) { ?>
    <fieldset>
        <legend><?php _vzm("Additional Music Info") ?></legend>
        <p><?php _vzm("Genre:") ?> <?php echo $artist->getGenre() ?></p>
        <?php if ($artist->hasUrl()) { ?>
            <p>
                <?php _vzm("Homepage:") ?>
                <a href="<?php echo $net->trackLink('url', $artist->getUrl()) ?>" class="new-win"><?php echo $artist->getName() ?></a>
            </p>
        <?php } ?>
    </fieldset>
  <?php } ?>

  <?php if (0 < count($collections)) { ?>
      <fieldset>
          <legend><?php _vzm("Media Collections") ?></legend>
          <?php foreach($collections as $collection) { ?>
              <div class="mcol">
                  <h4><?php echo $collection->getName() ?></h4>
                  <ul>
                      <?php foreach($collection->getItems() as $mediaItem) { ?>
                      <li><a href="<?php echo $net->absoluteUrl($musicProductInfo->mediaUrl($mediaItem->getFilename())) ?>"><?php echo $mediaItem->getFilename() ?></a> 
                          (<?php echo $mediaItem->getType()->getName() ?>)</li>
                      <?php } ?>
                  </ul>
              </div>
          <?php } ?>
      </fieldset>
  <?php } ?>

