<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\http\widgets\form;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;

/**
 * A widget to make a boolean selection (true/false).
 *
 * <p>Style can be: <em>radio</em>, <em>select</em> or <em>checkbox</em>. Default is <em>radio</em>.</p>
 *
 * <p>If style is <em>checkbox</em>, the custom property <code>label</code> might be set to override the use
 * of the title as label text.</p>
 *
 * <p>Radiobox and select label for <code>true</code> and <code>false</code> may be set via <em>label_true</em> and
 * <em>label_false</em>, respectively.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class BooleanFormWidget extends FormWidget {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        // defaults
        $this->set('style', 'checkbox');
        // use underscore as zc sanitize converts '.' to '_'
        $this->set('label_true', 'True');
        $this->set('label_false', 'False');
    }


    /**
     * Overload to evaluate as boolean.
     *
     * @return boolean The value.
     */
    public function getValue() {
        return Toolbox::asBoolean(parent::getValue());
    }

    /**
     * Build the hidden value element name used for checkbox rendering.
     *
     * @param string name The name.
     * @return string The generated name for the hidden element.
     */
    protected function getCheckboxHiddenValueName($name) {
        if (false === strpos($name, '[')) {
            return '_'.$name;
        }
        // XXX: does this work with multi value parameters; eg. name = foo[] ??
        return preg_replace('/\[/', '[_', $name, 1);
    }

    /**
     * Render as checkbox.
     *
     * @param ZMRequest request The current request.
     * @return The rendered HTML.
     */
    protected function renderCheckbox($request) {
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        $checked = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? ' checked="checked"' : ' checked';
        $idBase = \ZMHtmlUtils::encode($this->get('id'));
        $name = $this->getName();
        if (empty($idBase)) {
            // default to name; we need this to make label work
            $idBase = $name;
        }
        $value = $this->getValue();
        $label = $this->get('label');
        if (empty($label)) {
            $label = $this->getTitle();
        }

        ob_start();
        echo '<input type="hidden" name="'.$this->getCheckboxHiddenValueName($name).'" value="'.($value ? 'true' : 'false').'"'.$slash.'>';
        echo '<input type="checkbox" id="'.$idBase.'" name="'.$name.'" value="true"'.($value ? $checked : '').$slash.'>';
        if (!empty($label)) {
            echo ' <label for="'.$idBase.'">'.\ZMHtmlUtils::encode(_zm($label)).'</label>';
        }
        return ob_get_clean();
    }

    /**
     * Render as radiobox group.
     *
     * @param ZMRequest request The current request.
     * @return The rendered HTML.
     */
    protected function renderRadio($request) {
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        $checked = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? ' checked="checked"' : ' checked';
        $idBase = \ZMHtmlUtils::encode($this->get('id'));
        $name = $this->getName();
        if (empty($idBase)) {
            // default to name; we need this to make label work
            $idBase = $name;
        }
        $value = $this->getValue();

        ob_start();
        echo '<input type="radio" id="'.$idBase.'_true" name="'.$name.'" value="true"'.($value ? $checked : '').$slash.'>';
        echo ' <label for="'.$idBase.'_true">'.\ZMHtmlUtils::encode(_zm($this->get('label_true'))).'</label>';
        echo '<input type="radio" id="'.$idBase.'_false" name="'.$name.'" value="false"'.(!$value ? $checked : '').$slash.'>';
        echo ' <label for="'.$idBase.'_false">'.\ZMHtmlUtils::encode(_zm($this->get('label_false'))).'</label>';
        return ob_get_clean();
    }

    /**
     * Render as select box.
     *
     * @param ZMRequest request The current request.
     * @return The rendered HTML.
     */
    protected function renderSelect($request) {
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        $selected = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? ' selected="selected"' : ' selected';
        $id = \ZMHtmlUtils::encode($this->get('id'));
        $name = $this->getName();
        $value = $this->getValue();

        ob_start();
        echo '<select '.(!empty($id) ? ' id="'.$id.'"' : '').' name="'.$name.'">';
        echo '  <option value="true"'.(!$value ? $selected : '').'>'.\ZMHtmlUtils::encode(_zm($this->get('label_true'))).'</option>';
        echo '  <option value="false"'.(!$value ? $selected : '').'>'.\ZMHtmlUtils::encode(_zm($this->get('label_false'))).'</option>';
        echo '</select>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, $view) {
        switch ($this->get('style')) {
            default:
                Runtime::getLogging()->debug('invalid style "'.$this->get('style').'" - using default');
            case 'checkbox':
                return $this->renderCheckbox($request);
            case 'radio':
                return $this->renderRadio($request);
            case 'select':
                return $this->renderSelect($request);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function compare($value) {
        return Toolbox::asBoolean($value) == $this->getValue();
    }

}
