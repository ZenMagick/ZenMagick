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
 */
?>
<?php

/**
 * Custom simpletest <code>HtmlReporter</code> implementation.
 *
 * @package org.zenmagick.plugins.zm_tests
 * @author DerManoMann
 * @version $Id$
 */
class ZMHtmlReporter extends HtmlReporter {

    /**
     * Create new instance.
     */
    function __construct() {
        $this->HtmlReporter('ISO-8859-1');
    }

    /**
     * {@inheritDoc}
     */
    public function paintHeader($name) {
    }

    /**
     * {@inheritDoc}
     */
    public function paintFooter($test_name) {
        // paint, but we only want the actual view contents
        ob_start();
        parent::paintFooter($test_name);
        $html = ob_get_clean();
        echo str_replace("</body>\n</html>", '', $html);
    }

    /**
     * {@inheritDoc}
     */
    public function shouldInvoke($test, $method) {
        //TODO: manage exclusions
        //echo $test.": ".$method."<BR>";
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function paintException($exception) {
        parent::paintException($exception);
        //var_dump($exception);
    }

}

?>
