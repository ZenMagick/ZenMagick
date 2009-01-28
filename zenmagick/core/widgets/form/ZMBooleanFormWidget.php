<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * <p>A widget to make a boolean selection (true/false).</p>
 *
 * <p>Style can be: <em>radio</em>, <em>select</em> or <em>checkbox</em>. Default is <em>radio</em>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.widgets.form
 * @version $Id$
 */
class ZMBooleanFormWidget extends ZMWidget {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        // defaults
        $this->set('style', 'radio');
        $this->set('true', 'Yes');
        $this->set('false', 'No');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Overload to evaluate as boolean.
     *
     * @return boolean The value.
     */
    public function getValue() {
        return ZMTools::asBoolean($this->get('value'));
    }

    /**
     * Render as radiobox group.
     *
     * @return The rendered HTML.
     */
    public function renderRadio() {
        $html = ZMToolbox::instance()->html;
        $slash = ZMSettings::get('isXHTML') ? '/' : '';
        $checked = ZMSettings::get('isXHTML') ? ' checked="checked"' : ' checked';
        $idBase = $html->encode($this->get('id'), false);
        $name = $html->encode($this->get('name'), false);
        if (empty($idBase)) {
            // default to name; we need this to make label work
            $idBase = $name;
        }
        $value = $this->getValue();

        ob_start();
        echo '<input type="radio" id="'.$idBase.'_true" name="'.$name.'" value="true"'.($value ? $checked : '').$slash.'>';
        echo ' <label for="'.$idBase.'_true">'.zm_l10n_get($this->get('true')).'</label>';
        echo '<input type="radio" id="'.$idBase.'_false" name="'.$name.'" value="false"'.(!$value ? $checked : '').$slash.'>';
        echo ' <label for="'.$idBase.'_false">'.zm_l10n_get($this->get('false')).'</label>';
        return ob_get_clean();
    }

    /**
     * Render as select box.
     *
     * @return The rendered HTML.
     */
    public function renderSelect() {
        $html = ZMToolbox::instance()->html;
        $slash = ZMSettings::get('isXHTML') ? '/' : '';
        $selected = ZMSettings::get('isXHTML') ? ' selected="selected"' : ' selected';
        $id = $html->encode($this->get('id'), false);
        $name = $html->encode($this->get('name'), false);
        $value = $this->getValue();

        ob_start();
        echo '<select '.(!empty($id) ? ' id="'.$id.'"' : '').' name="'.$name.'">';
        echo '  <option value="true"'.(!$value ? $selected : '').'>'.zm_l10n_get($this->get('true')).'</option>';
        echo '  <option value="false"'.(!$value ? $selected : '').'>'.zm_l10n_get($this->get('false')).'</option>';
        echo '</select>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function render() {
        switch ($this->get('style')) {
            default:
                ZMLogging::instance()->log('invalid style "'.$this->get('style').'" - using default', ZMLogging::DEBUG);
            case 'radio':
                return $this->renderRadio();
                break;
            case 'select':
                return $this->renderSelect();
                break;
            case 'checkbox':
                //TODO: $this->renderCheckbox();
                return $this->renderSelect();
                break;
        }
    }

}

?>
