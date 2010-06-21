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
 * Simple email view.
 *
 * <p>Email template are expected in the directory <code>[theme-views-dir]/emails</code>.
 * Filenames follow the pattern <code>[$template].[html|text].php</code>.<p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.views
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
    public function getLayout() {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request) {
        //XXX: ugh!
        $isAdmin = ZMSettings::get('isAdmin');
        ZMSettings::set('isAdmin', false);

        if (!$this->isValid($request)) {
            ZMLogging::instance()->trace('invalid email template: '.$this->getTemplate(), ZMLogging::ERROR);
            ZMSettings::set('isAdmin', $isAdmin);
            return "";
        }
        $toolbox = $request->getToolbox();
        $this->setVar('request', $request);
        $this->setVar('session', $request->getSession());
        $this->setVar('toolbox', $toolbox);
        $this->setVars($toolbox->getTools());

        $content = parent::generate($request);

        //XXX: ugh!
        ZMSettings::set('isAdmin', $isAdmin);

        return $content;
    }

}
