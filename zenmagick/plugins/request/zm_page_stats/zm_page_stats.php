<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
class zm_page_stats extends Plugin {
    private $pageCache_;


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
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Hidden Stats', 'hideStats', 'false', 'If set to true, page stats will be hidden (as HTML comment).',
            'widget@BooleanFormWidget#name=hideStatus&default=false&label=Hide status');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        ZMEvents::instance()->attach($this);
    }

    /**
     * Generate hidden stats.
     *
     * @param ZMRequest request The current request.
     */
    private function hiddenStats($request) {
        ob_start();
        echo '<!--'."\n";
        echo '  Client IP: '.$_SERVER['REMOTE_ADDR']."\n";
        echo '  total page execution: '.Runtime::getExecutionTime().' secconds;'."\n";
        $db = ZMRuntime::getDB();
        echo '  db: SQL queries: '.$db->queryCount().', duration: '.round($db->queryTime(), 4).' seconds;';
        $stats = ZMRuntime::getDatabase()->getStats();
        echo '  database ('.ZMSettings::get('zenmagick.core.database.provider').'): SQL queries: '.$stats['queries'].', duration: '.round($stats['time'], 4).' seconds;'."\n";

        $vars = $request->getController()->getView()->getVars();
        if (null !== ($exception = $vars['exception'])) {
            echo "\n".$exception."\n\n";
        }

        echo '-->'."\n";
        if (ZMSettings::get('plugins.zm_page_stats.showEventLog', true)) {
            echo '<!--'."\n";
            echo '  '.Runtime::getExecutionTime(ZM_START_TIME).' ZM_START_TIME '."\n";
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
        echo $this->hiddenStats($args['request']);
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $request = $args['request'];
        $contents = $args['contents'];

        if (ZMLangUtils::asBoolean($this->get('hideStats'))) {
            $args['contents'] = $contents.$this->hiddenStats($args['request']);
            return $args;
        }

        ob_start();
        $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
        echo '<div id="page-stats">';
        echo 'Client IP: <strong>'.$_SERVER['REMOTE_ADDR'].'</strong>;';
        echo '&nbsp;&nbsp;&nbsp;total page execution: <strong>'.Runtime::getExecutionTime().'</strong> secconds;<br'.$slash.'>';
        $db = Runtime::getDB();
        echo '<strong>db</strong>: SQL queries: <strong>'.$db->queryCount().'</strong>, duration: <strong>'.round($db->queryTime(), 4).'</strong> seconds;';
        $stats = ZMRuntime::getDatabase()->getStats();
        echo '&nbsp;&nbsp;<strong>database ('.ZMSettings::get('zenmagick.core.database.provider').')</strong>: SQL queries: <strong>'.$stats['queries'].'</strong>, duration: <strong>'.round($stats['time'], 4).'</strong> seconds;<br'.$slash.'>';
        $lstats = ZMLoader::instance()->getStats(true);
        echo 'ZMLoader: '.$lstats['static'].' static and '.$lstats['class'].' class files loaded.<br'.$slash.'>';
        echo '</div>';
        if (ZMSettings::get('plugins.zm_page_stats.showEventLog', true)) {
            echo '<div id="event-log">';
            echo '<table border="1">';
            echo '<tr>';
            echo '<td style="text-align:right;padding:4px;">'.Runtime::getExecutionTime(ZM_START_TIME).'</td>';
            echo '<td colspan="4" style="text-align:left;padding:4px;">ZM_START_TIME</td>';
            echo '</tr>';
            foreach (ZMEvents::instance()->getEventLog() as $event) {
                echo '<tr>';
                echo '<td style="text-align:right;padding:4px;">'.$event['time'].'</td>';
                echo '<td style="text-align:left;padding:4px;">'.$event['id'].'</td>';
                echo '<td style="text-align:left;padding:4px;">'.sprintf("%d", $event['memory']).'</td>';
                echo '<td style="text-align:left;padding:4px;">'.$event['method'].'</td>';
                $eargs = is_array($event['args']) ? $event['args'] : array($event['args']);
                if (Events::FINALISE_CONTENTS == $event['id']) {
                    $eargs['contents'] = '**response**';
                }
                // handle array eargs
                foreach ($eargs as $key => $value) {
                    if (is_array($value)) {
                        $eargs[$key] = implode(',', $value);
                    }
                }
                $argsInfo = implode(',', $eargs);
                $argsInfo = empty($argsInfo) ? '&nbsp;' : $argsInfo;
                echo '<td style="text-align:left;padding:4px;">'.$argsInfo.'</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        }

        $controller = $request->getController();
        $view = $controller->getView();
        if (null != $view) {
            $vars = $view->getVars();
            if (null !== ($exception = $vars['exception'])) {
                echo '<pre>';
                echo $exception;
                echo '</pre>';
            }
        }

        $info = ob_get_clean();

        $args['contents'] = preg_replace('/<\/body>/', $info . '</body>', $contents, 1);
        return $args;
    }

}

?>
