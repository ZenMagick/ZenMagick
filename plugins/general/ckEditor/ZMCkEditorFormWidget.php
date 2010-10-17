<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package org.zenmagick.plugins.ckEditor
 * @author DerManoMann
 */
class ZMCkEditorFormWidget extends ZMTextAreaFormWidget {
    private $plugin_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->plugin_ = ZMPlugins::instance()->getPluginForId('ckEditor');
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
    public function render($request, $view) {
        if (!$this->plugin_) {
            // fallback
            return parent::render($request, $view);
        }

        include_once ZMFileUtils::mkPath($this->plugin_->getPluginDirectory(), 'ckeditor-3.4', 'ckeditor_php5.php');
        if (!class_exists('CKEditor')) {
            // fallback
            return parent::render($request, $view);
        }
        $CKEditor = new CKEditor();
        $CKEditor->returnOutput = true;
        $CKEditor->textareaAttributes = array(
            'id' => $this->getId(),
            'rows' => $this->getRows(),
            'cols' => $this->getCols(),
            'class' => $this->getClass(),
            'wrap' => $this->getWrap()
        );

        $config = array();

        //TODO: allow for predefined 'basic', 'standard' and 'advanced' presettings in abstract ZMWysiwygEditorFormWidget base class
        $config['toolbar'] = array(
            array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike'),
            array('Image', 'Link', 'Unlink', 'Anchor')
        );

        return $CKEditor->editor($this->getId(), $this->getValue(), $config);
    }

}
