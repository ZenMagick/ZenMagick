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
class ZMEmailView extends SavantView {

    /**
     * Create new email view.
     *
     * @param string template The template name.
     * @param boolean html Flag to indicate whether to use the HTML or text template; default is <code>true</code>.
     * @param array args Additional context values.
     */
    function __construct($template, $html=true, $args=array()) {
        parent::__construct();
        $this->setTemplate($template.($html ? '.html' : '.text'));
        $this->setVars($args);
        $this->setVar('language', Runtime::getLanguage());
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
    protected function getLayout() {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid() {
        return Runtime::getTheme()->themeFileExists('views/'.$this->getTemplate().ZMSettings::get('zenmagick.mvc.templates.ext', '.php'));
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request) {
        $isAdmin = ZMSettings::get('isAdmin');
        //XXX: ugh!
        ZMSettings::set('isAdmin', false);
        if (!$this->isValid()) {
            ZMLogging::instance()->trace('invalid email template', ZMLogging::ERROR);
            return "";
        }
        $toolbox = $request->getToolbox();
        $this->setVar('request', $request);
        $this->setVar('session', $request->getSession());
        $this->setVar('toolbox', $toolbox);
        //XXX: fix ($request)
        $this->setVars($toolbox->getTools($request));

        $content = parent::generate($request);

        ZMSettings::set('isAdmin', $isAdmin);

        return $content;
    }

}

?>
