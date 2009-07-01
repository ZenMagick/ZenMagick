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
 * Simple email view.
 *
 * <p>Email template are expected in the directory <code>[theme-views-dir]/emails</code>.
 * Filenames follow the pattern <code>[$template].[html|text].php</code>.<p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.views
 * @version $Id: ZMEmailView.php 2347 2009-06-29 02:43:11Z dermanomann $
 */
class ZMEmailView extends ZMPageView {
    protected $args_ = null;


    /**
     * Create new email view.
     *
     * @param string template The template name.
     * @param boolean html Flag to indicate whether to use the HTML or text template; default is <code>true</code>.
     * @param array args Additional context values.
     */
    function __construct($template, $html=true, $args=array()) {
        parent::__construct();
        $this->setView('email_'.$template.($html ? '.html' : '.text'));
        $this->args_ = $args;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Returns the full view filename to be includes by a template.
     *
     * @return string The full view filename.
     */
    public function getViewFilename() {
        return $this->_getViewFilename('email');
    }

    /**
     * Check if this view is valid.
     *
     * @return boolean <code>true</code> if the view is valid, <code>false</code> if not.
     */
    public function isValid() {
        return file_exists($this->_getViewFilename('email'));
    }

    /**
     * Generate email content.
     *
     * <p>In contrast to other views, this version will actually not display anything, but rather
     * return the generated content in order to be captured and passed into the actual mail
     * code.</p>
     */
    public function generate() {
        $isAdmin = ZMSettings::get('isAdmin');
        //XXX: ugh!
        ZMSettings::set('isAdmin', false);
        $filename = $this->getViewFilename();
        if (!file_exists($filename)) {
            return "";
        }

        $controller = $this->getController();
        if (null !== $controller) {
            // *export* globals from controller into view space
            foreach ($controller->getGlobals() as $name => $instance) {
                $$name = $instance;
            }
        }
        // same for custom args
        foreach ($this->args_ as $name => $instance) {
            $$name = $instance;
        }
        // and for view data
        foreach ($this->vars_ as $name => $instance) {
            $$name = $instance;
        }

        // set for all
        $language = Runtime::getLanguage();

        ob_start();
        include($this->getViewFilename());
        ZMSettings::set('isAdmin', $isAdmin);
        return ob_get_clean();
    }

}

?>
