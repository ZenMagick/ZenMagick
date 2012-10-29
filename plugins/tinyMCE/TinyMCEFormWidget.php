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
namespace ZenMagick\plugins\tinyMCE;

use ZenMagick\Http\Widgets\Form\TextAreaFormWidget;
use ZenMagick\Http\Widgets\Form\WysiwygEditor;
use ZenMagick\Http\View\ResourceManager;
use ZenMagick\Http\View\TemplateView;

/**
 * TinyMCE textarea form widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.plugins.tinyMCE
 */
class TinyMCEFormWidget extends TextAreaFormWidget implements WysiwygEditor {
    private $idList;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->addClasses(array(self::EDITOR_CLASS, 'tinymce_editor'/*,'mceEditable'*/));
        $this->idList = array();
    }

    /**
     * Init editor.
     *
     * @param ResourceManager resourceManager The resourceManager.
     */
    private function initEditor(ResourceManager $resourceManager) {
        // add required js
        $resourceManager->jsFile('tinymce/jscripts/tiny_mce/jquery.tinymce.js', ResourceManager::HEADER);
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
        if (null == $this->container->get('pluginService')->getPluginForId('tinyMCE')) {
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
        $tinyMce = $this->container->get('pluginService')->getPluginForId('tinyMCE');
        $scriptUrl = $tinyMce->pluginURL('content/tinymce/jscripts/tiny_mce/tiny_mce.js');
        $noEditorClass = self::NO_EDITOR_CLASS;
        if (0 < count($this->idList) || null === $this->idList) {
            if (null === $this->idList) {
                $selector = 'textarea';
            } else {
                $selector = '#'.implode(',#', $this->idList);
            }
            $jsInit = <<<EOT
<script type="text/javascript">
jQuery(function() {
    jQuery('$selector').not('.$noEditorClass').each(function() {
        $(this).tinymce({
            // Location of TinyMCE script
            script_url : '$scriptUrl',
            theme : "advanced",
            plugins : "paste, save",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,"
            + "justifyleft,justifycenter,justifyright,justifyfull,formatselect,"
            + "bullist,numlist,outdent,indent",
            theme_advanced_buttons2 : "link,unlink,anchor,image,separator,"
            +"undo,redo,cleanup,separator,sub,sup,charmap",
            theme_advanced_buttons3 : ""
        });
    });
});
</script>
EOT;
            $content = $event->getArgument('content');
            $content = preg_replace('/<\/body>/', $jsInit . '</body>', $content, 1);
            $event->setArgument('content', $content);
            // clear to create js only once
            $this->idList = array();
        }
    }

}
