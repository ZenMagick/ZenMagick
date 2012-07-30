<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id$
//      based on site_map.php v1.0.1 by networkdad 2004-06-04
// Fix for line 48 provided by Paulm, uploaded by Kelvyn
// Changes for click-show-hide menu by Cameron, 2008-01-10

// Showing category counts will use default Zen function, which generates massive
// recusive database queries. Could be improved by instead retrieving in a single
// query all products to categories and then using recursive PHP to fetch counts.
namespace zenmagick\plugins\cssCategories;

use zenmagick\base\Runtime;


class CategoriesUlGenerator {
  var $root_category_id = 0,
      $max_level = 6,
      $data = array(),
      $root_start_string = '',
      $root_end_string = '',
      $parent_start_string = '',
      $parent_end_string = '',
      $parent_group_start_string = '%s<ul>',
      $parent_group_end_string = '%s</ul>',
      $child_start_string = '%s<li>',
      $child_end_string = '%s</li>',
      $spacer_string = '',
      $spacer_multiplier = 1;
  var $document_types_list = ' (3) ';  // acceptable format example: ' (3, 4, 9, 22, 18) '
  protected $netToolbox;

  function __construct($request) {
    $this->data = array();

    $this->netToolbox = $request->getToolbox()->net;
    foreach (Runtime::getContainer()->get('categoryService')->getCategories($request->getSession()->getLanguageId()) as $category) {
      $products_in_category = SHOW_COUNTS == 'true' ? count(Runtime::getContainer()->get('productService')->getProductIdsForCategoryId($category->getId(), $request->getSession()->getLanguageId())) : 0;
      $this->data[$category->getParentId()][$category->getId()] = array('name' => $category->getName(), 'count' => $products_in_category);
    }
  }

  function buildBranch($parent_id, $level = 0, $cpath = '') {
    global $cPath;
    $result = "\n".sprintf($this->parent_group_start_string, str_repeat(' ', $level*4))."\n";
    if (isset($this->data[$parent_id])) {
      foreach ($this->data[$parent_id] as $category_id => $category) {
        $result .= sprintf($this->child_start_string, str_repeat(' ', $level*4+2));
        if (isset($this->data[$category_id])) {
          $result .= $this->parent_start_string;
        }
        if ($level == 0) {
          $result .= $this->root_start_string;
          $new_cpath  = $category_id;
        } else {
          $new_cpath = $cpath."_".$category_id;
        }
        if ($cPath == $new_cpath) {
          $result .= '<a href="javascript:void(0)" class="on">'; // highlight current category & disable link
        } else {
          $result .= '<a href="' . $this->netToolbox->url('category', 'cPath=' . $new_cpath) . '">';
        }
        $result .= $category['name'];
        if (SHOW_COUNTS == 'true' && ((CATEGORIES_COUNT_ZERO == '1' && $category['count'] == 0) || $category['count'] >= 1)) {
          $result .= CATEGORIES_COUNT_PREFIX . $category['count'] . CATEGORIES_COUNT_SUFFIX;
        }
        $result .= '</a>';
        if ($level == 0) {
          $result .= $this->root_end_string;
        }
        if (isset($this->data[$category_id])) {
          $result .= $this->parent_end_string;
        }
        if (isset($this->data[$category_id]) && (($this->max_level == '0') || ($this->max_level > $level+1))) {
          $result .= $this->buildBranch($category_id, $level+1, $new_cpath);
          $result .= sprintf($this->child_end_string, str_repeat(' ', $level*4+2))."\n";
        } else {
          $result .= sprintf($this->child_end_string, '')."\n";
        }
      }
    }
    $result .= sprintf($this->parent_group_end_string, str_repeat(' ', $level*4))."\n";
    return $result;
  }

  function buildTree() {
    return $this->buildBranch($this->root_category_id, 0);
  }
}
?>
