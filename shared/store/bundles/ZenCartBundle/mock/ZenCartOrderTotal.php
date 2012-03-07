<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\bundles\ZenCartBundle\Mock;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * zencart order totals.
 *
 * @author DerManoMann
 */
class ZenCartOrderTotal extends ZMObject {

    /**
     * New instance.
     */
    public function __construct() {

    }

    /**
     * Clear posts
     */
    function clear_posts() {
        //TODO?
    }

    /**
     * Pre confirmation check.
     */
    function pre_confirmation_check($returnOrderTotalOnly=false) {
        // TODO
        return 0.00;
    }

    /**
     * Process.
     */
    function process() {
    global $order;
    $order_total_array = array();
    if (is_array($this->modules)) {
      reset($this->modules);
      while (list(, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (!isset($GLOBALS[$class])) continue;
        $GLOBALS[$class]->process();
        for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
          $title = trim($GLOBALS[$class]->output[$i]['title']);
          $text = trim($GLOBALS[$class]->output[$i]['text']);
          if (!empty($title) && !empty($text)) {
            $order_total_array[] = array('code' => $GLOBALS[$class]->code,
                                         'title' => $title,
                                         'text' => $text,
                                         'value' => $GLOBALS[$class]->output[$i]['value'],
                                         'sort_order' => $GLOBALS[$class]->sort_order);
          }
        }
      }
    }

    return $order_total_array;
    }

}
