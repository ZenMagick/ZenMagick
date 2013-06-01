This is a ZenMagick plugin that allows to add a single product multiple
times for different values of a single attribute.


Installation
============
1) Unzip this plugin into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Enjoy


Usage
=====
In order to use this plugin you will have to modify your product_info view.
Please note that some of the changes can be avoided (hiding the regular qty field) if using a custom
product type for all products that should be treated this way.

NOTE: The attribute used can not be of type TEXT or UPLOAD!


Template changes
================

1) Show multiple quantity fields for all available values of a single attribute
-------------------------------------------------------------------------------
  <?php
      // Name of the attribute to allow multi qty on the product page
      define('MULTI_QUANTITY_NAME', 'Memory');

      $isMultiQty = false;
      $attributes = $currentProduct->getAttributes();
      foreach ($attributes as $attribute) {
          if (MULTI_QUANTITY_NAME == $attribute->getName()) {
              $isMultiQty = true;
              // this is required for the server code to know which attribute to use
              echo '<input type="hidden" name="'.MULTI_QUANTITY_ID.'" value="'.$attribute->getId().'">';

              // qty input fields for each attribute value
              foreach ($attribute->getValues() as $value) {
                  echo $value->getName() . ': ';
                  echo '<input type="text" name="id['.$attribute->getId().']['.$value->getId().']">';
              }
          }
      }

  ?>



2) Exclude the multi quantity attribute from being shown as *regular* attribute
-------------------------------------------------------------------------------
In the default theme the code to display attributes starts with the following lines:

  <?php $attributes = $macro->productAttributes($currentProduct); ?>
  <?php foreach ($attributes as $attribute) { ?>

The second line (foreach) needs to be modified to ignore the multi qty attribute:

  <?php $attributes = $macro->productAttributes($currentProduct); ?>
  <?php foreach ($attributes as $attribute) { /* ADDED: */ if (MULTI_QUANTITY_NAME == $attribute['name']) { continue; } ?>



3) Do not show the regular qty input field
------------------------------------------
Make showing the regular qty input field conditional depending on whether this is a multi qty product or not:

      <?php if (!$isMultiQty) { ?>
          <label for="cart_quantity">{{ 'Quantity'|trans }}<?php echo $minMsg; ?></label>
          <input type="text" id="cart_quantity" name="cart_quantity" value="1" maxlength="6" size="4" />
      <?php } ?>

