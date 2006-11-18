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

    // navigation block
    function rnblk($resultList, $sort) {
    global $zm_request;
        $html = '<div class="rnblk">';
        if (1 < $resultList->getNumberOfPages()) {
            $html .= '<div class="rnav">';
            $html .= '<span class="pno">';
            $html .= zm_l10n_get("Page %s/%s", $resultList->getCurrentPageNumber(), $resultList->getNumberOfPages());
            $html .= '</span>';
            if ($resultList->hasPreviousPage()) {
                $html .= '<a href="' . $resultList->getPreviousURL(false) . '">' . zm_l10n_get("Previous") . '</a>';
            } else {
                $html .= '<span class="nin">' . zm_l10n_get("Previous") . '</span>';
            }
            $html .= '&nbsp;';
            if ($resultList->hasNextPage()) {
                $html .= '<a href="' . $resultList->getNextURL(false) . '">' . zm_l10n_get("Next") . '</a>';
            } else {
                $html .= '<span class="nin">' . zm_l10n_get("Next") . '</span>';
            }
            $html .= '</div>';
        }

        $options = $resultList->getSortOptions();
        if ($sort && $options->hasOptions()) {
            $html .= zm_form(null, '', null, "get", null, false);
            $html .= '<div>';
            $html .= '<input type="hidden" name="page" value="' . $resultList->getCurrentPageNumber() . '" />';
            if ($zm_request->getCategoryPath()) {
                $html .= '<input type="hidden" name="cPath" value="' . $zm_request->getCategoryPath() . '" />';
            } else if ($zm_request->getManufacturerId()) {
                $html .= '<input type="hidden" name="manufacturers_id" value="' . $zm_request->getManufacturerId() . '" />';
            } else if (null != $zm_request->getRequestParameter("compareId")) {
                $html .= zm_hidden_list('compareId[]', $zm_request->getRequestParameter("compareId"), false);
            }
            $html .= '<select name="sort" onchange="this.form.submit()">';
            if (!$options->hasActiveOption()) {
                $html .= '<option value="">' . zm_l10n_get("Sort by...") . '</option>';
            }
            foreach ($options->getOptions() as $option) {
                $selected = ($option->isActive() ? ' selected="selected"' : '');
                $html .= '<option value="' .$option->getSort() . '"' . $selected . '>' . $option->getName() . ($option->isActive() ? ($options->isDecending() ? '&nbsp;-&nbsp;&nbsp;&nbsp;' : '&nbsp;+&nbsp;&nbsp;&nbsp;') : '') . ' </option>';
              }
            $html .= '</select>';
            $html .= '<input type="submit" class="btn" value="' . zm_l10n_get("Sort / Reverse") . '" />';
            $html .= '</div>';
            $html .= '</form>';
        }

        $html .= '</div>';
        return $html;
    }

    
    // generic result list handling using simple callbacks
    function processResultList($resultList, $resultName, $resultCallback, $tableDefCallback=null, $sort=true, $echo=true) {
    global $zm_request;
        $html = '';
        $html .= '<div class="rlist">';

        $html .= rnblk($resultList, $sort);

        $html .= zm_form(ZM_FILENAME_COMPARE_PRODUCTS, '', null, 'get', null, false);
        $html .= '<table cellspacing="0" cellpadding="0">';
        if (null != $tableDefCallback) {
            $html .= call_user_func($tableDefCallback);
        }
        $html .= "<tbody>";
        $first = true;
        $odd = true;
        foreach ($resultList->getResults() as $result) {
            $html .= call_user_func($resultCallback, $result, $first, $odd);
            $first = false;
            $odd = !$odd;
        }

        $html .= "</tbody>";
        $html .= '</table>';
        $html .= '<div class="btn"><input type="submit" class="btn" value="'.zm_l10n_get("Compare Selected").'" /></div>';
        $html .= '</form>';
        $html .= rnblk($resultList, $sort);
        $html .= '</div>';

        if ($echo) echo $html;
        return $html;
    }


    function product_table_def() { return ''; }
    function order_table_def() { return ''; }

    function handle_product_result($product, $first, $odd) {
        $html = '<tr class="'.($odd?"odd":"even").($first?" first":" other").'"><td class="cpt">';
        $html .= '<input type="checkbox" name="compareId[]" value="'. $product->getId().'" />'; 
        $html .= '</td><td>';
        $html .= '<a href="' . zm_product_href($product->getId(), false) . '">' . 
            zm_product_image($product, false) . '</a>';
        $html .= '</td><td class="pinfo">';
        $html .= '<a href="' . zm_product_href($product->getId(), false) . '">' . 
            $product->getName() . '</a><br/>';
        $html .= zm_more(zm_strip_html($product->getDescription(), false), 120, false);
        $html .= '</td><td class="pprice">';
        $html .= zm_format_currency($product->getPrice(), false);
        $html .= '</td></tr>';
        return $html;
    }

    function handle_order_result($order, $first, $odd) {
        $html = '<tr class="'.($odd?"odd":"even").($first?" first":" other").'"><td>';
        $html .= '<a href="' . zm_href(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$order->getId(), false) . '">' . 
            $order->getId() . '</a>';
        $html .= '</td><td>';
        $html .= zm_date_short($order->getOrderDate(), false);
        $html .= '</td><td>';
        $address = $order->getBillingAddress();
        $html .= $address->getFirstName() . ' ' . $address->getLastName();
        $html .= '</td><td>';
        $html .= zm_l10n($order->getStatus());
        $html .= '</td><td class="pprice">';
        $html .= zm_format_currency($order->getTotal(), false);
        $html .= '</td></tr>';
        return $html;
    }

?>
