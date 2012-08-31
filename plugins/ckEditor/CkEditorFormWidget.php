<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\plugins\ckEditor;

use ZenMagick\Http\Widgets\Form\TextAreaFormWidget;
use ZenMagick\Http\Widgets\Form\WysiwygEditor;
use ZenMagick\Http\View\ResourceManager;
use ZenMagick\Http\View\TemplateView;

/**
 * CKEditor textarea form widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.plugins.ckEditor
 */
class CkEditorFormWidget extends TextAreaFormWidget implements WysiwygEditor {
    private $idList;
    private $editorConfig;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->addClasses(array(self::EDITOR_CLASS, 'ckeditor_editor'));
        $this->idList = array();
        $this->editorConfig = array('toolbar' => array(
                array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike'),
                array('Image', 'Link', 'Unlink', 'Anchor')
            )
        );

    }


    /**
     * Init editor.
     *
     * @param ResourceManager resourceManager The resourceManager.
     */
    private function initEditor(ResourceManager $resourceManager) {
        // add required js
        $resourceManager->jsFile('ckeditor/jquery.CKEditor.pack.js', ResourceManager::HEADER);
        // create init script code at the end once we know all the ids
        $this->container->get('event_dispatcher')->addListener('finalise_content', array($this, 'onFinaliseContent'));
    }

    /**
     * {@inheritDoc}
     */
    public function apply($request, TemplateView $templateView, $idList=null) {
        $this->initEditor($templateView->getResourceManager());
        if (null === $idList) {
            $this->idList = null;
        } else {
            $this->idList = array_merge($this->idList, $idList);
        }
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, TemplateView $templateView) {
        if (null == $this->container->get('pluginService')->getPluginForId('ckEditor')) {
            // fallback
            return parent::render($request, $templateView);
        }

        $this->initEditor($templateView->getResourceManager());

        $this->idList[] = $this->getId();

        return parent::render($request, $templateView);
    }

    /**
     * Add init code.
     */
    public function onFinaliseContent($event) {
        $ckEditor = $this->container->get('pluginService')->getPluginForId('ckEditor');
        $basePath = $ckEditor->pluginURL('content/ckeditor');
        $noEditorClass = self::NO_EDITOR_CLASS;
        if (0 < count($this->idList) || null === $this->idList) {
            if (null === $this->idList) {
                $selector = 'textarea';
            } else {
                $selector = '#'.implode(',#', $this->idList);
            }
            $editorConfig = json_encode($this->editorConfig);
            $jsInit = <<<EOT
<script type="text/javascript">
var CKEDITOR_BASEPATH = '$basePath/';
$(function(){
$.ckeditor.path = CKEDITOR_BASEPATH;
$('$selector').ckeditor($editorConfig);
});
</script>
EOT;
            $content = $event->get('content');
            $content = preg_replace('/<\/body>/', $jsInit . '</body>', $content, 1);
            $event->set('content', $content);
            // clear to create js only once
            $this->idList = array();
        }
    }

}
