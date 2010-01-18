<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * A WYSIWYG editor form widget.
 *
 * <p>This widget may be used to display a WYSIWYG editor (if available). The actually used
 * implementation depends on the following (in decending priority):</p>
 * <ul>
 *  <li>An editor set in the current request/session.</li>
 *  <li>The configured default editor.</li>
 *  <li>A standard <em>textarea</em> element.</li>
 * </ul>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.widgets.form
 * @version $Id$
 */
class ZMWysiwygEditorFormWidget extends ZMTextAreaFormWidget {
    private $config_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->editor_ = null;
        $this->config_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get a list of all available editors.
     *
     * @return array A class/name map of editors.
     */
    public static function getEditorMap() {
        $map = array();
        $tokens = explode(',', ZMSettings::get('zenmagick.mvc.widgets.editors'));
        foreach ($tokens as $token) {
            $nc = explode(':', $token);
            $map[$nc[1]] = $nc[0];
        }

        return $map;
    }

    /**
     * Get the current editor class.
     *
     * @param ZMRequest request The current request.
     * @return ZMTextAreaFormWidget A text editor widget.
     */
    public static function getCurrentEditor($request) {
        //TODO: session name
        if (null == ($editor = $request->getSession()->getValue(''))) {
            $editor = self::getDefaultEditor();
        }
        echo $editor;

        return ZMBeanUtils::getBean($editor);
    }

    /**
     * Get the default editor class.
     *
     * @return string The default editor class name.
     */
    public static function getDefaultEditor() {
        return ZMSettings::get('zenmagick.mvc.widgets.defaultEditor', 'TextAreaFormWidget');
    }

    /**
     * Set optional configuration settings.
     *
     * @param array config A configuration map.
     */
    public function setConfig($config) {
        $this->config_ = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function render($request) {
        $editor = self::getCurrentEditor($request);
        return $editor->render($request);
    }

}

?>
