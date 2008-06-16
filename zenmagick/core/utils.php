<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 *
 * $Id$
 */
?>
<?php

    /**
     * Dispatch the current request.
     *
     * @package org.zenmagick
     * @return boolean Always <code>true</code>.
     */
    function zm_dispatch() {
        $controller = ZMLoader::make(ZMLoader::makeClassname(ZMRequest::getPageName().'Controller'));
        if (null == $controller) {
            $controller = ZMLoader::make("DefaultController");
        }

        ZMRequest::setController($controller);

        if (ZMSettings::get('isLegacyAPI')) { eval(zm_globals()); }

        // execute controller
        $view = $controller->process();

        // generate response
        if (null != $view) {
            // make header match the template
            header('Content-Type: text/html; charset='.zm_i18n('HTML_CHARSET'));

            // common view variables
            $controller->exportGlobal('zm_view', $view);
            $controller->exportGlobal('zm_theme', ZMRuntime::getTheme());

            ZMEvents::instance()->fireEvent(null, ZM_EVENT_VIEW_START, array('view' => $view));
            $view->generate();
            ZMEvents::instance()->fireEvent(null, ZM_EVENT_VIEW_DONE, array('view' => $view));
        }

        return true;
    }

    /**
     * Custom error handler.
     *
     * @package org.zenmagick
     * @param int errno The error level.
     * @param string errstr The error message.
     * @param string errfile The source filename.
     * @param int errline The line number.
     * @param array errcontext All variables of scope when error triggered.
     */
    function zm_error_handler($errno, $errstr, $errfile, $errline, $errcontext) { 
        // get current level
        $level = error_reporting(E_ALL);
        error_reporting($level);
        // disabled or not configured?
        if (0 == $level || $errno != ($errno&$level)) {
            return;
        }

        $time = date("d M Y H:i:s"); 
        // Get the error type from the error number 
        $errtypes = array (1    => "Error",
                           2    => "Warning",
                           4    => "Parsing Error",
                           8    => "Notice",
                           16   => "Core Error",
                           32   => "Core Warning",
                           64   => "Compile Error",
                           128  => "Compile Warning",
                           256  => "User Error",
                           512  => "User Warning",
                           1024 => "User Notice",
                           2048 => "Strict",
                           4096 => "Recoverable Error"
        ); 


        if (isset($errtypes[$errno])) {
            $errlevel = $errtypes[$errno]; 
        } else {
            $errlevel = "Unknown";
        }

        if (null != ($handle = fopen(ZMSettings::get('zmLogFilename'), "a"))) {
            fputs($handle, "\"$time\",\"$errfile: $errline\",\"($errlevel) $errstr\"\r\n"); 
            fclose($handle); 
        }
    } 

?>
