<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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


/**
 * Widget base class.
 *
 * <p>Widgets are simple UI element container. They have some basic meta data (<em>title</em>,
 * <em>description</em>) and can either be enabled or disabled.</p>
 *
 * <p>Depending on the nature of the widgets, subclasses might implement custom properties as
 * needed.</p>
 *
 * <p>Since widgets are used in the context of an HTML page, the <code>render()</code> method is expected
 * to return valid HTML that will display the widget.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.widgets
 * @version $Id: ZMWidget.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
abstract class ZMWidget extends ZMObject {
    private $title_;
    private $description_;
    private $enabled_;

/*
zen_cfg_select_option(array(\'shipping\', \'billing\'),');
zen_cfg_select_option(array(\'ProductId\', \'Model\'),');
zen_cfg_select_drop_down(array(array('id'=>'order', 'text'=>'Order Address'), array('id'=>'account', 'text'=>'Account Address')), ");
zen_cfg_select_drop_down(array(array('id'=>'false', 'text'=>'Ignore'), array('id'=>'true', 'text'=>'Catch-up')), ");
zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name');
==================
zen_cfg_select_coupon_id($coupon_id, $key = '')
zen_cfg_pull_down_country_list($country_id, $key = '')
zen_cfg_pull_down_country_list_none($country_id, $key = '')
zen_cfg_pull_down_zone_list($zone_id, $key = '')
zen_cfg_pull_down_tax_classes($tax_class_id, $key = '')
zen_cfg_textarea($text, $key = '')
zen_cfg_textarea_small($text, $key = '')
zen_cfg_get_zone_name($zone_id)
zen_cfg_pull_down_htmleditors($html_editor, $key = '')
zen_cfg_password_input($value, $key = '')
zen_cfg_password_display($value)
zen_cfg_select_option($select_array, $key_value, $key = '')
zen_cfg_select_drop_down($select_array, $key_value, $key = '')
zen_cfg_pull_down_zone_classes($zone_class_id, $key = '')
zen_cfg_pull_down_order_statuses($order_status_id, $key = '')
zen_cfg_select_multioption($select_array, $key_value, $key = '')
*/

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->title_ = '';
        $this->enabled_ = true;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the title.
     *
     * @param string title The title.
     */
    public function setTitle($title) {
        $this->title_ = $title;
    }

    /**
     * Get the title.
     *
     * @return string The title.
     */
    public function getTitle() {
        return $this->title_;
    }

    /**
     * Set the description.
     *
     * @param string description The description.
     */
    public function setDescription($description) {
        $this->description_ = $description;
    }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function getDescription() {
        return $this->description_;
    }

    /**
     * Controls whether this widget is enabled or not.
     *
     * @param boolean enabled The enabled state.
     */
    public function setEnabled($enabled) {
        $this->enabled_ = $enabled;
    }

    /**
     * Check if this widget is enabled.
     *
     * @return boolean The enabled state.
     */
    public function isEnabled() {
        return $this->enabled_;
    }

    /**
     * Get the HTML to render this widget.
     *
     * @return string The HTML.
     */
    public abstract function render();

}

?>
