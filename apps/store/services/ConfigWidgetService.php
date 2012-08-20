<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
namespace zenmagick\apps\store\services;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\http\widgets\Widget;
use zenmagick\apps\store\model\ConfigValue;

/**
 * Config service.
 *
 * @author DerManoMann
 */
class ConfigWidgetService extends ConfigService {

    /**
     * Build a collection of Widget and/or ConfigValue objects
     *
     * @param array array of config values
     * @return array A list of <code>ConfigValue</code>s.
     */
    protected function buildObjects($configValues) {
        $values = array();
        foreach ($configValues as $value) {
            if (0 === strpos($value['setFunction'], 'widget@')) {
                $widgetDefinition = $value['setFunction'].'&'.$value['useFunction'];
                // build definition from both function values (just in case)
                $definition = str_replace('widget@', '', $widgetDefinition);

                // handle old definition ids in the db
                if (0 === strpos($definition, 'ZM')) {
                    $definition = lcfirst(substr($definition, 2));
                }

                $widget = Beans::getBean($definition, $this->container);
                if (null !== $widget) {
                    $widget->setTitle($value['name']);
                    $widget->setDescription($value['description']);
                    $widget->setValue($value['value']);
                    // needed for generic plugin config support
                    $widget->set('configurationKey', $value['key']);
                    $values[] = $widget;
                } else {
                    $this->container->get('loggingService')->warn('failed to create widget: '.$widgetDefinition);
                }
            } else {
                // try to convert into widget...
                $widget = null;
                $setFunction = $value['setFunction'];
                if (null != $setFunction) {
                    $tmp = explode('(', $setFunction);
                    $setFunction = trim($tmp[0]);
                }
                switch ($setFunction) {
                case null:
                    $widget = $this->container->get('textFormWidget');
                    $size = strlen($value['value'])+3;
                    $size = 64 < $size ? 64 : $size;
                    $widget->set('size', $size);
                    break;
                case 'zen_cfg_textarea':
                    $widget = $this->container->get('textAreaFormWidget');
                    $widget->setRows(5);
                    $widget->setCols(60);
                    break;
                case 'zen_cfg_textarea_small':
                    $widget = $this->container->get('textAreaFormWidget');
                    $widget->setRows(1);
                    $widget->setCols(35);
                    break;
                case 'zen_cfg_select_option':
                    // XXX: perhaps make radio group
                    $widget = $this->container->get('selectFormWidget');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    if (3 < count($widget->getOptions(null))) {
                        $widget->setStyle('select');
                    } else {
                        $widget->setStyle('radio');
                    }
                    break;
                case 'zen_cfg_select_drop_down':
                    $widget = $this->container->get('selectFormWidget');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    break;
                case 'zen_cfg_pull_down_order_statuses':
                    $widget = $this->container->get('orderStatusSelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list':
                    $widget = $this->container->get('countrySelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list_none':
                    $widget = $this->container->get('countrySelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;
                case 'zen_cfg_pull_down_htmleditors':
                    $widget = $this->container->get('textFormWidget');
                    $widget->set('readonly', true);
                    //$widget = $this->container->get('EditorSelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_zone_list';
                    $widget = $this->container->get('zoneSelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;
                case 'zen_cfg_select_coupon_id';
                    $widget = $this->container->get('couponSelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;

                default:
                    $widget = Beans::map2obj('zenmagick\apps\store\model\ConfigValue', $value);
                    break;
                }
                if ($widget instanceof Widget) {
                    // common stuff
                    $widget->setName($value['key']);
                    $widget->setTitle($value['name']);
                    $widget->setDescription(htmlentities($value['description']));
                    $widget->setValue(htmlentities($value['value']));
                    $widget->set('id', $value['key']);
                    // needed for generic plugin config support
                    $widget->set('configurationKey', $value['key']);
                }

                $values[] = $widget;
            }
        }
        return $values;
    }

    /**
     * Split options into map.
     *
     * @param string value The set function value.
     * @return array An options map.
     */
    protected function splitOptions($value) {
        // some initial stripping
        $value = preg_replace('/.*\(array\((.*)\).*/', '\1', $value);

        $idText = false;
        if (false !== strpos($value, 'id') && false !== strpos($value, 'text') && false !== strpos($value, '=>')) {
            // we do have an id/text mapping (nested arrays)
            $idText = true;
        }

        $options = array();
        if ($idText) {
            foreach (explode(', array(', $value) as $option) {
                $tmp = explode(',', $option);
                $value = str_replace(array("'id'", '"id"', '=>', '"', "'"), '', trim($tmp[0]));
                $text = str_replace(array("'text'", '"text"', '=>', '"', "'"), '', trim($tmp[1]));
                $text = substr($text, 0, -1);
                $options[$value] = $text;
            }
        } else {
            foreach (explode(',', $value) as $option) {
                $option = str_replace(array('"', "'"), '', trim($option));
                $options[$option] = $option;
            }
        }
        return $options;
    }

}
