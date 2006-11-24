<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 *
 * $Id$
 */
?>
<?php  
require_once('includes/application_top.php');
require_once('includes/zmCatalogDtree.php');
require_once('../zenmagick/init.php');
require_once('../zenmagick/admin_init.php');

    // set up some stuff
    $featureTypes = $zm_features->getFeatureTypes();
    $features = $zm_features->getFeatureList();
    $productId = $zm_request->getRequestParameter('productId');

    $edit_feature = false;
    $feature_name = '';
    $feature_description = '';
    $feature_hidden = false;

    $edit_product_feature = false;
    $value_index = 1;
    $value_text = '';

    switch ($zm_request->getRequestParameter('action')) {
      case 'remove_feature':
        $id = (int)$zm_request->getRequestParameter('feature_id');
        $zm_features->removeFeatureForId($id);
        // reload
        zen_redirect(zen_href_link(ZM_ADMINFN_FEATURES . "?cPath=" . $zm_request->getCategoryPath()));
        break;

      case 'update_feature':
        $id = (int)$zm_request->getRequestParameter('feature_id');
        $name = $zm_request->getRequestParameter('name');
        $description = $zm_request->getRequestParameter('description');
        $hidden = $zm_request->getRequestParameter('hidden');
        $zm_features->updateFeature($id, $zm_request->getLanguageId(), $name, $description, $hidden);
        // reload
        zen_redirect(zen_href_link(ZM_ADMINFN_FEATURES . "?cPath=" . $zm_request->getCategoryPath()));
        break;

      case 'edit_feature':
        $edit_feature = true;
        $update_id = (int)$zm_request->getRequestParameter('feature_id');
        $feature = $zm_features->getFeatureForId($update_id);
        $type = $feature->getType();
        $editType = $type->getName();
        $feature_name = $feature->getName();
        $feature_description = $feature->getDescription();
        $feature_hidden = $feature->isHidden();
        break;

      case 'add_feature':
        $type = $zm_request->getRequestParameter('type');
        $name = $zm_request->getRequestParameter('name');
        $description = $zm_request->getRequestParameter('description');
        $hidden = null != $zm_request->getRequestParameter('hidden') ? '1' : '0';
        $zm_features->addFeature($type, $zm_request->getLanguageId(), $name, $description, $hidden);
        // reload
        zen_redirect(zen_href_link(ZM_ADMINFN_FEATURES . "?cPath=" . $zm_request->getCategoryPath()));
        break;

      case 'update_feature_value':
        $featureId = (int)$zm_request->getRequestParameter('featureId');
        $value = $zm_request->getRequestParameter('value');
        $oldIndex = $zm_request->getRequestParameter('oldIndex');
        $index = $zm_request->getRequestParameter('index');
        $zm_features->updateFeatureForProduct($productId, $featureId, $oldIndex, $value, $index);
        // reload
        zen_redirect(zen_href_link(ZM_ADMINFN_FEATURES."?productId=" . $productId . "&cPath=" . $zm_request->getCategoryPath()));
        break;

      case 'edit_feature_value':
        $edit_product_feature = true;
        $update_id = (int)$zm_request->getRequestParameter('featureId');
        $value_index = (int)$zm_request->getRequestParameter('index');
        $pFeatures = $zm_features->getFeaturesForProductId($productId);
        foreach ($pFeatures as $feature) {
          if ($feature->getId() == $update_id) {
            break;
          }
        }
        $editType = $feature->getName();
        $values = $feature->getValues();
        $value_text = $values[$value_index];
        break;

      case 'add_feature_value':
        $featureId = $zm_request->getRequestParameter('featureId');
        $value = $zm_request->getRequestParameter('value');
        $index = $zm_request->getRequestParameter('index');

        // stop duplicate index
        $invalid = false;
        $pFeatures = $zm_features->getFeaturesForProductId($productId);
        foreach ($pFeatures as $feature) {
          $values = $feature->getValues();
          if (array_key_exists($index, $values) && $featureId == $feature->getId()) {
            $invalid = true;
            $messageStack->add('Duplicate index '.$index, 'error');
            // preset
            $value_index = $index;
            $value_text = $value;
            break;
          }
        }
        if (!$invalid) {
          $zm_features->addFeatureForProduct($productId, $featureId, $value, $index);
          // reload
          zen_redirect(zen_href_link(ZM_ADMINFN_FEATURES."?productId=" . $productId . "&cPath=" . $zm_request->getCategoryPath()));
        }
        break;

      case 'remove_feature_value':
        $featureId = $zm_request->getRequestParameter('featureId');
        $index = $zm_request->getRequestParameter('index');
        $zm_features->removeFeatureForProduct($productId, $featureId, $index);
        // reload
        zen_redirect(zen_href_link(ZM_ADMINFN_FEATURES."?productId=" . $productId . "&cPath=" . $zm_request->getCategoryPath()));
        break;

    }

    $product = null;
    if (0 != $productId) {
      $product = $zm_products->getProductForId($productId);
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>ZenMagick Product Features</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript">
      function init() {
        cssjsmenu('navbar');
        if (document.getElementById) {
          var kill = document.getElementById('hoverJS');
          kill.disabled = true;
        }
      }
    </script>
  </head>
  <body id="b_features" onload="init()">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <div id="main">
      <div id="cnav" class="dtree">
        <?php zm_catalog_dtree(ZM_ADMINFN_FEATURES, ZM_ADMINFN_FEATURES) ?>
      </div>

      <div id="catalogContent">
        <fieldset>
          <legend>Feature Maintenance</legend>
          <h3>Available features</h3>
          <table cellspacing="0" cellpadding="2">
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Description</th>
                <th>Hidden</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($features as $feature) { $type = $feature->getType(); ?>
                <tr>
                  <td><?php echo $feature->getName() ?></td>
                  <td><?php echo "[".$type->getName() . "]" ?></td>
                  <td><?php echo $feature->getDescription() ?></td>
                  <td><?php echo ($feature->isHidden()?'x':'') ?></td>
                  <td>
                    <a href="?action=edit_feature&amp;feature_id=<?php echo $feature->getId() ?>&amp;cPath=<?php echo $zm_request->getCategoryPath() ?>">Edit</a>
                    &nbsp;|&nbsp;
                    <a href="?action=remove_feature&amp;feature_id=<?php echo $feature->getId() ?>&amp;cPath=<?php echo $zm_request->getCategoryPath() ?>">Delete</a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          <h3><?php echo ($edit_feature ? "Edit" : "Add") ?> feature</h3>
          <form action="<?php echo ZM_ADMINFN_FEATURES ?>" method="post">
            <input type="hidden" name="cPath" value="<?php echo $zm_request->getCategoryPath() ?>">
            <input type="hidden" name="action" value="<?php echo ($edit_feature ? "update_feature" : "add_feature") ?>">
            <table cellspacing="0" cellpadding="2">
              <thead>
                <tr>
                  <th>Feature Type</th>
                  <th>Name</th>
                  <th>Description</th>
                  <th>Hidden</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <?php if ($edit_feature) { ?>
                      <input type="hidden" name="feature_id" value="<?php echo $update_id ?>">
                      <input type="hidden" name="type" value="<?php echo $editType ?>">
                      <?php echo $editType ?>
                    <?php } else { ?>
                      <select name="type">
                      <?php foreach ($featureTypes as $type) { ?>
                        <option value="<?php echo $type->getId() ?>"><?php echo $type->getName() ?></option>
                      <?php } ?>
                      </select>
                    <?php } ?>
                  </td>
                  <td>
                    <input type="text" size="30" name="name" value="<?php echo $feature_name ?>">
                  </td>
                  <td>
                    <input type="text" size="40" name="description" value="<?php echo $feature_description ?>">
                  </td>
                  <td>
                    <input type="checkbox" name="hidden" value="1" <?php echo ($feature_hidden?'checked="checked"':'') ?>>
                  </td>
                  <td>
                    <input type="submit" value="<?php echo ($edit_feature ? "Update" : "Add") ?>">
                  </td>
                </tr>
              </tbody>
            </table>
          </form>
        </fieldset>

        <?php if (null != $product) { ?>
          <fieldset>
              <legend><?php zm_l10n("Features for <span>%s</span>", $product->getName()) ?></legend>
              <?php $productFeatures = $product->getFeatures(true); ?>
              <?php if (0 < count($productFeatures)) { ?>
                <table cellspacing="0" cellpadding="2">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Value</th>
                      <th>Index</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($productFeatures as $feature) { ?>
                    <?php foreach ($feature->getValues() as $index => $value) { ?>
                      <tr>
                        <td><?php echo $feature->getName() ?></td>
                        <td><?php echo $value ?></td>
                        <td><?php echo $index ?></td>
                        <td>
                          <a href="?action=edit_feature_value&amp;productId=<?php echo $product->getId() ?>&amp;featureId=<?php echo $feature->getId() ?>&amp;index=<?php echo $index ?>&amp;cPath=<?php echo $zm_request->getCategoryPath() ?>">Edit</a>
                          &nbsp;|&nbsp;
                          <a href="?action=remove_feature_value&amp;productId=<?php echo $product->getId() ?>&amp;featureId=<?php echo $feature->getId() ?>&amp;index=<?php echo $index ?>&amp;cPath=<?php echo $zm_request->getCategoryPath() ?>">Delete</a>
                        </td>
                      </tr>
                    <?php } ?>
                  <?php } ?>
                  </tbody>
                </table>
              <?php } ?>
                <h3><?php echo ($edit_product_feature ? "Update" : "Add") ?> Product Feature</h3>
                <form action="<?php echo ZM_ADMINFN_FEATURES ?>" method="post">
                  <input type="hidden" name="action" value="<?php echo ($edit_product_feature ? "update_feature_value" : "add_feature_value") ?>">
                  <input type="hidden" name="cPath" value="<?php echo $zm_request->getCategoryPath() ?>">
                  <input type="hidden" name="productId" value="<?php echo $product->getId() ?>">
                  <table cellspacing="0" cellpadding="2">
                    <thead>
                      <tr>
                        <th>Feature</th>
                        <th>Value</th>
                        <th>Index</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>
                          <?php if ($edit_product_feature) { ?>
                            <input type="hidden" name="featureId" value="<?php echo $update_id ?>">
                            <?php echo $editType ?>
                          <?php } else { ?>
                            <select name="featureId">
                              <?php foreach ($features as $feature) {
                                $type = $feature->getType();
                                $selected = $featureId == $feature->getId() ? " selected=\"selected\"" : "";?>
                                <option value="<?php echo $feature->getId() ?>"<?php echo $selected?>><?php echo $feature->getName() ?></option>
                              <?php } ?>
                            </select>
                          <?php } ?>
                        </td>
                        <td>
                          <textarea type="text" cols="60" rows="5" name="value"><?php echo $value_text ?></textarea>
                        </td>
                        <td>
                          <input type="hidden" size="5" name="oldIndex" value="<?php echo $value_index ?>">
                          <input type="text" size="5" name="index" value="<?php echo $value_index ?>">
                        </td>
                        <td>
                          <input type="submit" value="<?php echo ($edit_product_feature ? "Update" : "Add") ?>">
                        </td>
                      </tr>
                    </body>
                  </table>
                </form>
            </fieldset>
          <?php } ?>
        </div>
    </div>

  </body>
</html>
