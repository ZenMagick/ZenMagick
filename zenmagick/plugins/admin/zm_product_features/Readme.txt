This plugin allows to attach features to products.

A feature is a typed bit of information and products can be compared on the basis
of the shared features.

An example of how features could be displayed per product would be:
(with $zm_product being the product)

  <?php $features = ZMFeatures::instance()->getFeaturesforProductIdAndStatus($zm_product->getId()); ?>
  <?php if (0 < count($features)) { ?>
      <fieldset>
          <legend><?php zm_l10n("Features") ?></legend>
          <?php foreach ($features as $feature) { ?>
              <?php echo $feature->getName() ?>: <?php zm_list_values($feature->getValues()) ?> <?php zm_htmlencode($feature->getDescription()) ?><br>
          <?php } ?>
      </fieldset>
  <?php } ?>


Sample code on how to display products next to each other can be found in the etc folder of this plugin.
