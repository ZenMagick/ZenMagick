<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace plugins\ckEditor;

use zenmagick\base\Runtime;

/**
 * CKEditor textarea form widget.
 *
 * @package org.zenmagick.plugins.ckEditor
 * @author DerManoMann <mano@zenmagick.org>
 */
class CkEditorFormWidget extends \ZMTextAreaFormWidget implements \WysiwygEditor {
    private $plugin_;
    private $editorConfig;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->plugin_ = Runtime::getContainer()->get('pluginService')->getPluginForId('ckEditor');
        $this->editorConfig = array();

        //TODO: allow for predefined 'basic', 'standard' and 'advanced' presettings in abstract ZMWysiwygEditorFormWidget base class
        $this->editorConfig['toolbar'] = array(
            array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike'),
            array('Image', 'Link', 'Unlink', 'Anchor')
        );

    }


    /**
     * Get a CK editor instance.
     *
     * @return CKEditor An editor instance or <code>null</code>.
     */
    private function getCKEditor() {
        include_once \ZMFileUtils::mkPath($this->plugin_->getPluginDirectory(), 'ckeditor-3.4', 'ckeditor_php5.php');
        if (!class_exists('CKEditor')) {
            return null;
        }

        $ckEditor = new \CKEditor();
        $ckEditor->returnOutput = true;
        return $ckEditor;
    }

    /**
     * {@inheritDoc}
     */
    public function apply($request, $view, $idList=null) {
        if (!$this->plugin_ || null == ($ckEditor = $this->getCKEditor())) {
            return null;
        }

        $out = '';
        if (null === $idList) {
            //TODO: check if this is fixed in future versions
            $ckEditor->config = array_merge($ckEditor->config, $this->editorConfig);
            $out .= $ckEditor->replaceAll();
        } else {
            foreach ($idList as $id) {
                $out .= $ckEditor->replace($id, $this->editorConfig);
            }
        }
        return $out;
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, $view) {
        if (!$this->plugin_ || null == ($ckEditor = $this->getCKEditor())) {
            // fallback
            return parent::render($request, $view);
        }

        $ckEditor->textareaAttributes = array(
            'id' => $this->getId(),
            'rows' => $this->getRows(),
            'cols' => $this->getCols(),
            'class' => $this->getClass(),
            'wrap' => $this->getWrap()
        );

        return $ckEditor->editor($this->getId(), $this->getValue(), $this->editorConfig);
    }

}
