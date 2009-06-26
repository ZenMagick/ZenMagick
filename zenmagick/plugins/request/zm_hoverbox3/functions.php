<?php
 
if (!function_exists('zen_hoverbox_IH2_url')) {
 /**
  * Test for the use of Image Handler to correctly format the image URL
  *
  * @package org.zenmagick.plugins.zm_hoverbox3
  */
 function zen_hoverbox_IH2_url($src, $alt = '', $width = '', $height = '', $parameters = '') {
    global $template_dir;

//auto replace with defined missing image
    if ($src == DIR_WS_IMAGES and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
      $src = DIR_WS_IMAGES . PRODUCTS_IMAGE_NO_IMAGE;
    }

if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
      return false;
    }

    // if not in current template switch to template_default
    if (!file_exists($src)) {
      $src = str_replace(DIR_WS_TEMPLATES . $template_dir, DIR_WS_TEMPLATES . 'template_default', $src);
    }

    // hook for handle_image() function such as Image Handler etc
    if (function_exists('handle_image')) {
      $newimg = handle_image($src, $alt, $width, $height, $parameters);
      list($src, $alt, $width, $height, $parameters) = $newimg; 
    }

    $image = zen_output_string($src);

    return $image;
  }
}


 /**
  * product image function
  *
  * @package org.zenmagick.plugins.zm_hoverbox3
  */
  function hover3_product_image_link($product, $imageInfo, $showLargerImage=true) {
      if(HOVERBOX_ENABLED == 'true'){
        if(HOVERBOX_DISPLAY_TITLE == 'true'){
          $title = zen_clean_html($product->getName());
        }else{
          $title='';
        }
        if(HOVERBOX_DISPLAY_PRICE == 'true'){
          $offers = $product->getOffers();
          if($offers->isSpecial()){
            $price = ' - ' . (($offers->isAttributePrice() && 1 == $product->getTypeSetting('starting_at')) ? TEXT_BASE_PRICE : '') . ZMToolbox::instance()->utils->formatMoney($offers->getSpecialPrice(), false, false);
          }else{
            $price = ' - ' . (($offers->isAttributePrice() && 1 == $product->getTypeSetting('starting_at')) ? TEXT_BASE_PRICE : '') . ZMToolbox::instance()->utils->formatMoney($offers->getCalculatedPrice(), false, false);
          }
        }else{
          $price='';
        }
        if (HOVERBOX_PRODUCT_DESC == 'true'){
          $hoverbox_pdesc = '::' . zen_trunc_string(zen_clean_html($product->getDescription()), HOVERBOX_MAX_DESC_LENGTH, true);
        }else{
          $hoverbox_pdesc = '';
        }
          
        if ($showLargerImage) {
            echo zen_image($imageInfo->getMediumImage(), $product->getName(), MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT) . '<div class="lrgarea"><a href="'. zen_hoverbox_IH2_url($imageInfo->getLargeImage(), addslashes($product->getName()), LARGE_IMAGE_WIDTH, LARGE_IMAGE_HEIGHT) .'" class="hoverbox" rel="gallery[group_'.$product->getId().']" title="' . $title . $price . $hoverbox_pdesc . '"><img src="' . Runtime::getTheme()->themeUrl('hover3/images/vlrg-pinfo.jpg', false) . '" alt="View Larger" class="lrglink" /></a></div>';
        } else {
            echo  '<a href="'. zen_hoverbox_IH2_url($imageInfo->getLargeImage(), addslashes($product->getName()), LARGE_IMAGE_WIDTH, LARGE_IMAGE_HEIGHT) .'" class="hoverbox" rel="gallery[group_'.$product->getId().']" title="' . $title . $price . $hoverbox_pdesc . '">'.zen_image($imageInfo->getMediumImage(), $product->getName(), MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT).'</a>';
        }
      }
  }

?>
