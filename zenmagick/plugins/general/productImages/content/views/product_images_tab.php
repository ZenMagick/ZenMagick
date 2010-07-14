<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

  function find_additional_images(&$array, $directory, $extension, $base ) {
  
    $image = $base . $extension;
  
    // Check for additional matching images
    if ($dir = @dir($directory)) {
      while ($file = $dir->read()) {
        if (!is_dir($directory . $file)) {
          if(preg_match("/^" . $base . "/i", $file) == '1') {
            // echo "BASE: ".$base.' FILE: '.$file.'<br />';
            if (substr($file, 0, strrpos($file, '.')) != substr($image, 0, strrpos($image, '.'))) {
              if ($base . preg_replace("/^$base/", '', $file) == $file) {
                $array[] = $file;
                // echo 'I AM A MATCH ' . $products_image_directory . '/'.$file . $products_image_extension .'<br />';
              } else {
                // echo 'I AM NOT A MATCH ' . $file . '<br />';
              } 
            }
          }
        }
      }
      
      if (sizeof($array) > 1) {
        sort($array);
      }
      
      $dir->close();
      
      return 1;
    }
    
    return 0;
  }

  echo $product->getName();

    $products_image = $product->getImage();
    $products_image_match_array = array();

    // get file extension and base
    $products_image_extension = substr($products_image, strrpos($products_image, '.'));
    $products_image_base = preg_replace("/".$products_image_extension."$/", '', $products_image);
        
    // if in a subdirectory
    if (strrpos($products_image_base, '/')) {
      $products_image_base = substr($products_image_base, strrpos($products_image_base, '/')+1);
    }
    
    
    // sort out directory
    $products_image_directory =  substr($products_image, 0, strrpos($products_image, '/'));
      // add slash to base dir
      if (($products_image_directory != '') && (!preg_match("|\/$|", $products_image_directory))) {
        $products_image_directory .= '/'; 
      }
    $products_image_directory_full = DIR_FS_CATALOG . DIR_WS_IMAGES . $products_image_directory;
    
    // Check that the image exists! (out of date Database)
    if (file_exists( $products_image_directory_full . $products_image_base . $products_image_extension )) {

      // Add base image to array
      $products_image_match_array[] = $products_image_base . $products_image_extension;
      // $products_image_base .= "_";
      
      // Check for additional matching images
      find_additional_images($products_image_match_array, $products_image_directory_full, $products_image_extension, $products_image_base );
    }
    
          if (preg_match("/^([^\/]+)\//", $products_image, $matches)) {
            echo '<br>basedir' .': ';
            echo $matches[1];
          }

    var_dump($products_image_match_array);

    $addImgList = $product->getAdditionalImages();
    foreach ($addImgList as $addImg) {
        echo $addImg."<BR>";
    }

    // use ih2 class to pull details for each image...
    // NOTE: ImageInfo does not resolve path correct as it makes image relative to admin app context
