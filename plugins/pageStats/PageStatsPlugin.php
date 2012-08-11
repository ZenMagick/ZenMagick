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
namespace zenmagick\plugins\pageStats;

use Plugin;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;
use zenmagick\base\events\EventDispatcher;
use zenmagick\base\logging\Logging;

/**
 * Plugin to show page stats.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PageStatsPlugin extends Plugin {
    private $pageCache_ = null;
    private $event = array();
    private $eventStats_ = array();

    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Hidden Stats', 'hideStats', 'false', 'If set to true, page stats will be hidden (as HTML comment).',
            'widget@booleanFormWidget#name=hideStats&default=false&label=Hide stats');
        $this->addConfigValue('Events', 'showEvents', 'false', 'Enable to display all fired events.',
            'widget@booleanFormWidget#name=showEvents&default=false&label=Show events');
        $this->addConfigValue('SQL', 'showSQLtiming', 'false', 'Enable to display all executed SQL and related timings.',
            'widget@booleanFormWidget#name=showSQLtiming&default=false&label=Show SQL');
        $this->addConfigValue('Limit displayed SQL', 'sqlTimingLimit', '0', 'Limit displayed SQL to the top X queries (0 for all).');
        $this->addConfigValue('Dump Queries to Error Log', 'dumpQueries', 'false', 'If set to true, all SQL queries will be dumped to error log.',
            'widget@booleanFormWidget#name=dumpQueries&default=false&label=Dump queries to error log');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        // register to log events
        $eventDispatcher = $this->container->get('eventDispatcher');
        $eventDispatcher->listen(array($this, 'logEvent'));
    }

    /**
     * Log all events.
     *
     * @param Event event An event.
     * @param mixed value Optional value for filter events.
     */
    public function logEvent($event, $value=null) {
        $source = $event->getSubject();
        if (!$source) {
            $source = 'N/A';
        } else if (is_object($source)) {
            $source = get_class($source);
        } else if (is_array($source) && is_object($source[0])) {
            $source = get_class($source[0]).':'.$source[1];
        } else {
            $source = 'unknown';
        }
        Runtime::getLogging()->info('event:('.$source.'):' . $event->getName() . '/'.Toolbox::className($event->getName()));
        // compress values
        $values = array();
        foreach (array_keys($event->all()) as $key) {
            if ('content' == $key) {
                $value = '***content***';
            } else {
                $value = $event->get($key);
            }
            if (is_array($value)) {
                $value = implode(';', $value);
            }
            if (is_object($value) && !($value instanceof ZMObject)) {
                $value = get_class($value);
            }
            $values[] = $key.'='.$value;
        }

        $this->eventStats_[] = array(
          'name' => $event->getName(),
          'memory' => $event->getMemory(),
          'timestamp' => $event->getTimestamp(),
          'method' => Toolbox::className($event->getName()),
          'values' => implode('; ', $values)
        );
        return $value;
    }

    private function getDatabaseInfo() {
        $stats = array();
        foreach (\ZMRuntime::getDatabases() as $database) {
            $stats[] = array(
                'params' => $database->getParams(),
                'provider' => get_class($database),
                'stats' => $database->getStats()
            );
        }
        return $stats;
    }

    /**
     * Generate hidden stats.
     *
     * @param zenmagick\http\Request request The current request.
     * @param View view The current view.
     */
    private function hiddenStats($request, $view) {
        ob_start();
        echo '<!--'."\n";
        echo '  Client IP: '.$request->getClientIp()."\n";
        echo '  PHP: '.phpversion()."\n";
        echo '  ZenMagick: '.Runtime::getSettings()->get('zenmagick.version')."\n";
        $application = $this->container->get('kernel');
        echo '  environment: '.$application->getEnvironment()."\n";
        echo '  total page execution: '.$this->getElapsedTime($application->getStartTime()).' secconds;'."\n";
        echo '  databases: ';
        foreach ($this->getDatabaseInfo() as $database) {
            $stats = $database['stats'];
            echo $database['params']['dbname'].'('.$database['provider'].'): SQL queries: '.$stats['queries'].', duration: '.round($stats['time'], 4).' seconds; ';
        }
        echo "\n";

        if (null != $view) {
            $vars = $view->getVariables();
            if (isset($vars['exception']) && null !== ($exception = $vars['exception'])) {
                echo "\n".$exception."\n\n";
            }
        }

        echo '-->'."\n";
        if (Toolbox::asBoolean($this->get('showEvents'))) {
            echo '<!--'."\n";
            echo '  '.'0'.' ZM_START_TIME '."\n";
            foreach ($this->eventStats_ as $eventInfo) {
                echo '  '.$this->getElapsedTime($eventInfo['timestamp']).' '.$eventInfo['method'].' / '.$eventInfo['name'].' values: '.$eventInfo['values']."\n";
            }
            echo '-->'."\n";
        }

        if (Toolbox::asBoolean($this->get('showSQLtiming'))) {
            $limit = $this->get('sqlTimingLimit');
            echo '<!--'."\n";
            echo '  SQL timings: ';
            foreach ($this->getDatabaseInfo() as $database) {
                $details = $database['stats']['details'];
                usort($details, array($this, "compareStats"));
                if (0 != $limit && count($details) > $limit) {
                    $details = array_slice($details, 0, $limit);
                }
                echo $database['params']['dbname'].'('.$database['provider'].'):'."\n";
                foreach ($details as $query) {
                    echo $query['time'].': '.$query['sql']."\n";
                }
            }
            echo '-->'."\n";
        }

        if (Toolbox::asBoolean($this->get('dumpQueries'))) {
            foreach ($this->getDatabaseInfo() as $database) {
                foreach ($database['stats']['details'] as $query) {
                    error_log("QUERYLOG: " . $query['start'] . " [" . $query['time'] . " secs] " . $query['sql']);
                }
            }
        }

        return ob_get_clean();
    }

    /**
     * Event handler for page cache hits.
     */
    public function onPluginsPageCacheContentsDone($event) {
        echo $this->hiddenStats($event->get('request'), null);
    }

    /**
     * Handle finalize content event.
     */
    public function onFinaliseContent($event) {
        $content = $event->get('content');
        $request = $event->get('request');
        $view = $event->has('view') ? $event->get('view') : null;

        if (Toolbox::asBoolean($this->get('hideStats'))) {
            $event->set('content', $content.$this->hiddenStats($request, $view));
            return;
        }

        $application = $this->container->get('kernel');
        ob_start();
        $slash = $this->container->get('settingsService')->get('zenmagick.http.html.xhtml') ? '/' : '';
        $sep = '&nbsp;&nbsp;&nbsp;';
        echo '<div id="page-stats">';
        echo 'Client IP: <strong>'.$request->getClientIp().'</strong>;';
        echo $sep.'PHP: <strong>'.phpversion().'</strong>;';
        echo $sep.'ZenMagick: <strong>'.Runtime::getSettings()->get('zenmagick.version').'</strong>;';
        echo $sep.'environment: <strong>'.$application->getEnvironment().'</strong>;';
        echo $sep.'total page execution: <strong>'.$this->getElapsedTime($application->getStartTime()).'</strong> secconds;';
        echo '<br'.$slash.'>';
        echo '&nbsp;&nbsp;<strong>databases:</strong> ';
        foreach ($this->getDatabaseInfo() as $database) {
            $stats = $database['stats'];
            echo $database['params']['dbname'].'('.$database['provider'].'): SQL queries: <strong>'.$stats['queries'].'</strong>, duration: <strong>'.round($stats['time'], 4).'</strong> seconds;';
        }
        echo '<br'.$slash.'>';
        echo $sep.'<strong>includes:</strong> '.count(get_included_files()).';';
        echo $sep.'<strong>memory:</strong> '.memory_get_usage(true);
        echo '<br'.$slash.'>';
        echo '</div>';
        if (Toolbox::asBoolean($this->get('showEvents'))) {
            echo '<div id="event-log">';
            echo '<table border="1">';
            echo '<tr>';
            echo '<td style="text-align:right;padding:4px;">'.'0'.'</td>';
            echo '<td colspan="4" style="text-align:left;padding:4px;">ZM_START_TIME</td>';
            echo '</tr>';
            foreach ($this->eventStats_ as $eventInfo) {
                echo '<tr>';
                echo '<td style="text-align:right;padding:4px;">'.$this->getElapsedTime($eventInfo['timestamp']).'</td>';
                echo '<td style="text-align:left;padding:4px;">'.$eventInfo['name'].'</td>';
                echo '<td style="text-align:left;padding:4px;">'.sprintf("%d", $eventInfo['memory']).'</td>';
                echo '<td style="text-align:left;padding:4px;">'.$eventInfo['method'].'</td>';
                $values = empty($eventInfo['values']) ? '&nbsp;' : $eventInfo['values'];
                echo '<td style="text-align:left;padding:4px;">'.$values.'</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
        }

        if (Toolbox::asBoolean($this->get('showSQLtiming'))) {
            $limit = $this->get('sqlTimingLimit');
            echo '<div id="sql-timings">';
            echo '<table border="1">';
            echo '<tr><th>Time (sec)</th><th>SQL</td></tr>';
            foreach ($this->getDatabaseInfo() as $database) {
                $details = $database['stats']['details'];
                usort($details, array($this, "compareStats"));
                if (0 != $limit && count($details) > $limit) {
                    $details = array_slice($details, 0, $limit);
                }
                echo '<tr><th colspan="2">'.$database['params']['dbname'].'('.$database['provider'].')</th></tr>'."\n";
                foreach ($details as $query) {
                    echo '<tr><td>'.$query['time'].'</td><td>'.$query['sql']."</td></tr>";
                }
            }
            echo '</table>';
        }

        if (null != $view) {
            $vars = $view->getVariables();
            if (isset($vars['exception']) && null !== ($exception = $vars['exception'])) {
                echo '<pre>';
                echo $exception;
                echo '</pre>';
            }
        }

        $info = ob_get_clean();

        $event->set('content', preg_replace('/<\/body>/', $info . '</body>', $content, 1));
        return;
    }

    /**
     * Compare sql stats.
     */
    protected function compareStats($a, $b) {
        $an = $a['time'];
        $bn = $b['time'];
        if ($an == $bn) {
            return 0;
        }
        return ($an < $bn) ? 1 : -1;
    }

    /**
     * Get the currently elapsed page execution time.
     *
     * @param int timestamp time to check against.
     * @return long The execution time in milliseconds.
     */
    protected function getElapsedTime($time) {
        $elapsedTime = microtime(true) - $time;
        return round($elapsedTime, 4);
    }

}
