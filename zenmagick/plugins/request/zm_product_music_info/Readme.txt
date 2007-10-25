This is the ZenMagick plugin implementing the product_music_info product template.

Originally, this code was part of the ZenMagick core, but got extracted into a 
separate plugin as it is only useful for stores selling music online.


Installation
============
* Download (obvious ;)
  Download the latest version from http://www.zenmagick.org

* Extract into the ZenMagick plugins directory

* Install the plugin via the ZenMagick plugins admin page

* Done!


Usage
=====
The code included in this plugin can be used to display product information specific
to music like genre, publisher, etc.

Historically this code is used in a product template named 'product_music_info'. It is part
of the original zen-cart store setup, so it should be already configured in your database.
You can also create a new product type (and template) by loggin in as admin and selecting
Catalog -> Product Types

Once you have determined the template name, it's probably easiest to copy the existing
product template (product_info.php) and use it as basis for your new music template.

The following code can be used to display music related information:

  <?php 
      // set up services
      $zm_music = $this->create("Music");
      $zm_mediaManager = $this->create("MediaManager");
      // get artist information
      $artist = $zm_music->getArtistForProductId($zm_product->getId());
      // get musc collections for this product/artist
      $collections = $zm_mediaManager->getMediaCollectionsForProductId($zm_product->getId());
  ?>
  <fieldset>
      <legend><?php zm_l10n("Additional Music Info") ?></legend>
      <p><?php zm_l10n("Genre:") ?> <?php echo $artist->getGenre() ?></p>
      <?php if ($artist->hasUrl()) { ?>
          <p>
              <?php zm_l10n("Homepage:") ?>
              <a href="<?php zm_redirect_href('url', $artist->getUrl()) ?>"<?php zm_href_target() ?>><?php echo $artist->getName() ?></a>
          </p>
      <?php } ?>
  </fieldset>

  <?php if (0 < count($collections)) { ?>
      <fieldset>
          <legend><?php zm_l10n("Media Collections") ?></legend>
          <?php foreach($collections as $collection) { ?>
              <div class="mcol">
                  <h4><?php echo $collection->getName() ?></h4>
                  <ul>
                      <?php foreach($collection->getItems() as $media) { $type = $media->getType(); ?>
                      <li><a href="<?php zm_media_href($media->getFilename()) ?>"><?php echo $media->getFilename() ?></a> (<?php echo $type->getName() ?>)</li>
                      <?php } ?>
                  </ul>
              </div>
          <?php } ?>
      </fieldset>
  <?php } ?>

