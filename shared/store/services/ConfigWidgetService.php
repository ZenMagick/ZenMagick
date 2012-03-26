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
                $widget = Beans::getBean($definition);
                if (null !== $widget) {
                    $widget->setTitle($value['name']);
                    $widget->setDescription($value['description']);
                    $widget->setValue($value['value']);
                    // needed for generic plugin config support
                    $widget->set('configurationKey', $value['key']);
                    $values[] = $widget;
                } else {
                    Runtime::getLogging()->warn('failed to create widget: '.$widgetDefinition);
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
                    $widget = Beans::getBean('textFormWidget');
                    $size = strlen($value['value'])+3;
                    $size = 64 < $size ? 64 : $size;
                    $widget->set('size', $size);
                    break;
                case 'zen_cfg_textarea':
                    $widget = Beans::getBean('textAreaFormWidget');
                    $widget->setRows(5);
                    $widget->setCols(60);
                    break;
                case 'zen_cfg_textarea_small':
                    $widget = Beans::getBean('textAreaFormWidget');
                    $widget->setRows(1);
                    $widget->setCols(35);
                    break;
                case 'zen_cfg_select_option':
                    // XXX: perhaps make radio group
                    $widget = Beans::getBean('selectFormWidget#style=radio');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    if (3 < count($widget->getOptions(null))) {
                        $widget->setStyle('select');
                    }
                    break;
                case 'zen_cfg_select_drop_down':
                    $widget = Beans::getBean('selectFormWidget');
                    $widget->setOptions($this->splitOptions($value['setFunction']));
                    break;
                case 'zen_cfg_pull_down_order_statuses':
                    $widget = Beans::getBean('orderStatusSelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list':
                    $widget = Beans::getBean('countrySelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_country_list_none':
                    $widget = Beans::getBean('countrySelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;
                case 'zen_cfg_pull_down_htmleditors':
                    $widget = Beans::getBean('textFormWidget');
                    $widget->set('readonly', true);
                    //$widget = Beans::getBean('ZMEditorSelectFormWidget');
                    break;
                case 'zen_cfg_pull_down_zone_list';
                    $widget = Beans::getBean('zoneSelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;
                case 'zen_cfg_select_coupon_id';
                    $widget = Beans::getBean('couponSelectFormWidget');
                    $widget->setOptions(array('' => _zm('None')));
                    break;

                default:
                    //echo $setFunction.": ".$value['setFunction']."<BR>";
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

}
