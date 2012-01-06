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

use zenmagick\http\view\View;

/**
 * A text area form widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TextAreaFormWidget extends FormWidget implements WysiwygEditor {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->setAttributeNames(array('id', 'name', 'class', 'cols', 'rows', 'wrap', 'title'));
        // some defaults
        $this->setRows(5);
        $this->setCols(60);
    }


    /**
     * {@inheritDoc}
     */
    public function apply($request, View $view, $idList=null) {
        // nothing
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, $view) {
        $value = $this->isEncode() ? \ZMHtmlUtils::encode($this->getValue()) : $this->getValue();
        return '<textarea'.$this->getAttributeString($request, false).'>'.$value.'</textarea>';
    }

}
