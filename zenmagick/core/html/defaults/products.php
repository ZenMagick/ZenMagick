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

    // build HTML elements for product attributes;
    // This will return an array where each element is an array with:
    //   ['name'] = the attribute name
    //   ['type'] = the HTML element type
    //   ['html'] = An array of HTML elements (may be only one, depending on the type)

    /**
     * Helper to generate HTML for product attributes.
     *
     * <p>Usage sample:</p>
     *
     * <code><pre>
     *  &lt;?php $attributes = zm_buildAttributeElements($zm_product); ?&gt;
     *  &lt;?php foreach ($attributes as $attribute) { ?&gt;
     *  &nbsp;&nbsp;  &lt;?php foreach ($attribute['html'] as $option) { ?&gt;
     *  &nbsp;&nbsp;&nbsp;&nbsp;    &lt;p&gt;&lt;?php echo $option ?&gt;&lt;/p&gt;
     *  &nbsp;&nbsp;  &lt;?php } ?&gt;
     *  &lt;?php } ?&gt;
     * </pre></code>
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param ZMProduct product A <code>ZMProduct</code> instance.
     * @return array An array containing HTML formatted attributes.
     */
    function zm_build_attribute_elements($product) {
        $elements = array();
        // not sure how this could happen!
        $attributes = $product->getAttributes();
        $attributes = $attributes->getAttributes();
        foreach ($attributes as $attribute) {
            switch ($attribute->getType()) {
                case PRODUCTS_OPTIONS_TYPE_RADIO:
                    array_push($elements, _zm_buildRadioElement($product, $attribute));
                    break;
                case PRODUCTS_OPTIONS_TYPE_CHECKBOX:
                    array_push($elements, _zm_buildCheckboxElement($product, $attribute));
                    break;
                case PRODUCTS_OPTIONS_TYPE_READONLY:
                    array_push($elements, _zm_buildFeatureElement($attribute));
                    break;
                case PRODUCTS_OPTIONS_TYPE_TEXT:
                    array_push($elements, _zm_buildTextElement($product, $attribute));
                    break;
                case PRODUCTS_OPTIONS_TYPE_FILE:
                    die('Unsupported attribute type: file');
                    break;
                case PRODUCTS_OPTIONS_TYPE_SELECT:
                    array_push($elements, _zm_buildSelectElement($product, $attribute));
                    break;
                default:
                    die('unsupported $attribute type: '.$attribute->getType());
            }
        }
        return $elements;
    }


    function _zm_buildRadioElement($product, $attribute) {
        $element = array();
        $element['name'] = $attribute->getName();
        $element['type'] = 'radio';
        $elements = array();
        $index = 1;
        foreach ($attribute->getValues() as $value) {
            $id = 'id_'.$attribute->getId().'_'.$index++;
            $name = 'id['.$attribute->getId().']';
            $checked = $value->isDefault() ? ' checked="checked"' : '';
            $radio = '<input type="radio" id="'.$id.'" name="'.$name.'" value="'.$value->getId().'"'.$checked.'/>';
            $radio .= '<label for="'.$id.'">'._zm_buildAttributeValueLabel($product, $value).'</label>';
            array_push($elements, $radio);
        }
        $element['html'] = $elements;
        return $element;
    }


    function _zm_buildCheckboxElement($product, $attribute) {
        $element = array();
        $element['name'] = $attribute->getName();
        $element['type'] = 'checkbox';
        $elements = array();
        $index = 1;
        foreach ($attribute->getValues() as $value) {
            $id = 'id_'.$attribute->getId().'_'.$index++;
            $name = 'id['.$attribute->getId().']['.$value->getId().']';
            $checked = $value->isDefault() ? ' checked="checked"' : '';
            $checkbox = '<input type="checkbox" id="'.$id.'" name="'.$name.'" value="'.$value->getId().'"'.$checked.'/>';
            $checkbox .= '<label for="'.$id.'">'._zm_buildAttributeValueLabel($product, $value).'</label>';
            array_push($elements, $checkbox);
        }
        $element['html'] = $elements;
        return $element;
    }


    function _zm_buildTextElement($product, $attribute) {
        $element = array();
        $element['name'] = $attribute->getName();
        $element['type'] = 'text';
        $elements = array();
        $index = 1;
        foreach ($attribute->getValues() as $value) {
            $id = 'id_'.$attribute->getId().'_'.$index++;
            $name = 'id['.$attribute->getId().']['.$value->getId().']';
            $text = '<label for="'.$id.'">'._zm_buildAttributeValueLabel($product, $value).'</label>';
            $text .= '<input type="text" id="'.$id.'" name="'.$name.'" value=""/>';
            array_push($elements, $text);
        }
        $element['html'] = $elements;
        return $element;
    }


    function _zm_buildFeatureElement($attribute) {
        $element = array();
        $element['name'] = $attribute->getName();
        $element['type'] = 'feature';
        $elements = array();
        foreach ($attribute->getValues() as $value) {
            array_push($elements, $value->getName());
        }
        $element['html'] = $elements;
        return $element;
    }


    function _zm_buildSelectElement($product, $attribute) {
        $element = array();
        $element['name'] = $attribute->getName();
        $element['type'] = 'select';
        $elements = array();
        $html = '<select name="id['.$attribute->getId().']">';
        foreach ($attribute->getValues() as $value) {
            $selected = $value->isDefault() ? ' selected="selected"' : '';
            $html .= '<option value="'.$value->getId().'"'.$selected.'>'._zm_buildAttributeValueLabel($product, $value).'</option>';
        }
        $html .= '</select>';
        array_push($elements, $html);
        $element['html'] = $elements;
        return $element;
    }


    function _zm_buildAttributeValueLabel($product, $value) {
        $label = zm_l10n_get($value->getName());

        if ($value->isFree() && $product->isFree()) {
            $label .= zm_l10n_get(' [FREE! (was: %s%s)]', $value->getPricePrefix(), zm_format_currency($value->getPrice(), false));
        } else if (0 != $value->getPrice()) {
            $label .= zm_l10n_get(' (%s%s)', $value->getPricePrefix(), zm_format_currency($value->getPrice(), false));
        }
        //TODO: onetime and weight

        return $label;
    }


    // format offer price
    function zm_fmt_price($product, $echo=true) {
      $offers = $product->getOffers();

      $html = '<span class="price">';
      if ($offers->isAttributePrice()) {
          $html .= zm_l10n_get("Starting at: ");
      }
      if ($offers->isSpecial() || $offers->isSale()) {
          $html .= '<strike class="base">' . zm_format_currency($offers->getBasePrice(), false) . '</strike> ';
          if ($offers->isSpecial())  {
              if ($offers->isSale()) {
                 $html .= '<strike class="special">' . zm_format_currency($offers->getSpecialPrice(), false) . '</strike>';
              } else {
                 $html .= zm_format_currency($offers->getSpecialPrice(), false);
              }
          }
          if ($offers->isSale()) {
             $html .= zm_format_currency($offers->getSalePrice(), false);
          }
      } else {
          $html .= zm_format_currency($offers->getCalculatedPrice(), false);
      }
      $html .= '</span>';

      if ($echo) echo $html;
      return $html;
    }

?>
