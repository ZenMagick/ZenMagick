<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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


/**
 * CKEditor textarea form widget.
 *
 * @package org.zenmagick.plugins.ckeditor
 * @author DerManoMann
 * @version $Id$
 */
class ZMCKEditorFormWidget extends ZMTextAreaFormWidget {
    private $plugin_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->plugin_ = ZMPlugins::instance()->getPluginForId('ckeditor');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function render($request) {
        if (!$this->plugin_) {
            // fallback
            return parent::render($request);
        }

        require_once ZMFileUtils::mkPath(array($this->plugin_->getPluginDirectory(), 'ckeditor-3.1', 'ckeditor_php5.php'));
        $CKEditor = new CKEditor();
        $CKEditor->returnOutput = true;
        $CKEditor->textareaAttributes = array(
            'rows' => $this->getRows(),
            'cols' => $this->getCols(),
            'class' => $this->getClass(),
            'wrap' => $this->getWrap()
        );

        $config = array();

        // scale based on rows/cols
        $config['height'] = (1.3 * $this->getRows()).'em';
        $config['width'] = (1.1 * $this->getCols()).'em';

        //TODO: allow for predefined 'basic', 'standard' and 'advanced' presettings in abstract ZMWysiwygEditorFormWidget base class
        $config['toolbar'] = array(
            array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike'),
            array('Image', 'Link', 'Unlink', 'Anchor')
        );

        return $CKEditor->editor($this->getId(), $this->getValue(), $config);
    }

}

?>
