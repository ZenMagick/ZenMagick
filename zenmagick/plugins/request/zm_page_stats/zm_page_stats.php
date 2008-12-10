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
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
        $this->pageCache_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Hidden Stats', 'hideStats', 'false', 'If set to true, page stats will be hidden (as HTML comment).', 'zen_cfg_select_option(array(\'true\',\'false\'),');
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();
        if (defined('ZM_EVENT_PLUGINS_PAGE_CACHE_STATS')) {
            // page cache active
            $this->zcoSubscribe();
        }
    }

    /**
     * Generate hidden stats.
     */
    private function hiddenStats() {
        ob_start();
        echo '<!--'."\n";
        echo '  Client IP: '.$_SERVER['REMOTE_ADDR']."\n";
        echo '  total page execution: '.ZMRuntime::getExecutionTime().' secconds;'."\n";
        $db = ZMRuntime::getDB();
        echo '  db: SQL queries: '.$db->queryCount().', duration: '.round($db->queryTime(), 4).' seconds;';
        $stats = ZMRuntime::getDatabase()->getStats();
        echo '  database ('.ZMSettings::get('dbProvider').'): SQL queries: '.$stats['queries'].', duration: '.round($stats['time'], 4).' seconds;'."\n";

        if (null !== ($exception = ZMRequest::getController()->getGlobal('exception'))) {
            echo "\n".$exception."\n\n";
        }

        echo '-->'."\n";
        if (ZMSettings::get('plugins.zm_page_stats.showEventLog', true)) {
            echo '<!--'."\n";
            echo '  '.ZMRuntime::getExecutionTime(ZM_START_TIME).' ZM_START_TIME '."\n";
            foreach (ZMEvents::instance()->getEventLog() as $event) {
                echo '  '.$event['time'].' '.$event['method'].' / '.$event['id'].' args: '.implode(',', array_keys($event['args']))."\n";
            }
            echo '-->'."\n";
        }

        return ob_get_clean();
    }

    /**
     * Event handler for page cache hits.
     *
     * @param array args Optional parameter.
     */
    public function onZMPluginsPageCacheStats($args=array()) {
        echo $this->hiddenStats();
    }

    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    public function filterResponse($contents) {
        if (ZMTools::asBoolean($this->get('hideStats'))) {
            return $contents.$this->hiddenStats();
        }

        ob_start();
        $slash = ZMSettings::get('isXHTML') ? '/' : '';
        echo '<div id="page-stats">';
        echo 'Client IP: <strong>'.$_SERVER['REMOTE_ADDR'].'</strong>;';
        echo '&nbsp;&nbsp;&nbsp;total page execution: <strong>'.ZMRuntime::getExecutionTime().'</strong> secconds;<br'.$slash.'>';
        $db = ZMRuntime::getDB();
        echo '<strong>db</strong>: SQL queries: <strong>'.$db->queryCount().'</strong>, duration: <strong>'.round($db->queryTime(), 4).'</strong> seconds;';
        $stats = ZMRuntime::getDatabase()->getStats();
        echo '&nbsp;&nbsp;<strong>database ('.ZMSettings::get('dbProvider').')</strong>: SQL queries: <strong>'.$stats['queries'].'</strong>, duration: <strong>'.round($stats['time'], 4).'</strong> seconds;<br'.$slash.'>';
        echo '</div>';
        if (ZMSettings::get('plugins.zm_page_stats.showEventLog', true)) {
            echo '<div id="event-log">';
            echo '<table border="1">';
            echo '<tr>';
            echo '<td style="text-align:right;padding:4px;">'.ZMRuntime::getExecutionTime(ZM_START_TIME).'</td>';
            echo '<td colspan="3" style="text-align:left;padding:4px;">ZM_START_TIME</td>';
            echo '</tr>';
            foreach (ZMEvents::instance()->getEventLog() as $event) {
                echo '<tr>';
                echo '<td style="text-align:right;padding:4px;">'.$event['time'].'</td>';
                echo '<td style="text-align:left;padding:4px;">'.$event['id'].'</td>';
                echo '<td style="text-align:left;padding:4px;">'.$event['method'].'</td>';
                $args = is_array($event['args']) ? $event['args'] : array($event['args']);
                $argsInfo = implode(',', $args);
                $argsInfo = empty($argsInfo) ? '&nbsp;' : $argsInfo;
                echo '<td style="text-align:left;padding:4px;">'.$argsInfo.'</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        }

        if (null !== ($exception = ZMRequest::getController()->getGlobal('exception'))) {
            echo '<pre>';
            echo $exception;
            echo '</pre>';
        }

        $info = ob_get_clean();

        return preg_replace('/<\/body>/', $info . '</body>', $contents, 1);
    }

}

?>
