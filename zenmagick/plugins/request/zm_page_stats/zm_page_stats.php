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
 * Plugin to show page stats.
 *
 * @package org.zenmagick.plugins.zm_stats
 * @author DerManoMann
 * @version $Id$
 */
class zm_page_stats extends ZMPlugin {


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Page Stats', 'Show page stats', '${plugin.version}');
        $this->setLoaderSupport('ALL');
        $this->pageCache_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    function filterResponse($contents) {
        $info = '<div id="page-stats">';
        $info .= 'Client IP: <strong>'.$_SERVER['REMOTE_ADDR'].'</strong>;';
        $db = ZMRuntime::getDB();
        $info .= '&nbsp;&nbsp;&nbsp;SQL queries: <strong>'.$db->queryCount().'</strong>, duration: <strong>'.round($db->queryTime(), 4).'</strong> seconds;';
        $info .= '&nbsp;&nbsp;&nbsp;total page execution: <strong>'.ZMRuntime::getExecutionTime().'</strong> secconds;';
        $info .= '</div>';
        $info .= '<div id="event-log">';
        $info .= '<table border="1">';
        foreach (ZMEvents::instance()->getEventLog() as $event) {
            $info .= '<tr>';
            $info .= '<td style="text-align:right;padding:4px;">'.$event['time'].'</td>';
            $info .= '<td style="text-align:left;padding:4px;">'.$event['id'].'</td>';
            $info .= '<td style="text-align:left;padding:4px;">'.$event['method'].'</td>';
            $info .= '</tr>';
        }
        $info .= '</table>';
        $info .= '</div>';


        return preg_replace('/<\/body>/', $info . '</body>', $contents, 1);
    }

}

?>
