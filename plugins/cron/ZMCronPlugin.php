<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * Plugin to allow cron like execution of <code>ZMCronJob</code> classes.
 *
 * @package org.zenmagick.plugins.cron
 * @author DerManoMann
 */
class ZMCronPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('CronJobs', 'Allows to configure and execute cron jobs', '${plugin.version}');
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
    public function install() {
        parent::install();

        $this->addConfigValue('Trigger', 'image', 'false', 'Enable image trigger',
            'widget@ZMBooleanFormWidget#name=image&default=false&label=Enable image trigger&style=checkbox');
        $this->addConfigValue('Image trigger pages', 'triggerPages', 'index', 'List of pages (separated by comma \',\') to be used for imger trigger');
        $this->addConfigValue('Missed run policy', 'missedRuns', 'false', 'Select what should happen when one or more runs have been missed',
            'widget@ZMBooleanFormWidget#name=missedRuns&default=false&style=select&label_true=Catch-up&label_false=Ignore');
    }

    /**
     * Init this plugin.
     */
    public function init() {
        parent::init();
        zenmagick\base\Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Handle event.
     */
    public function onFinaliseContent($event) {
        $request = $event->get('request');

        if ($this->isEnabled() && ZMLangUtils::asBoolean($this->get('image'))) {
            $pages = $this->get('triggerPages');
            if (empty($pages) || ZMLangUtils::inArray($request->getRequestId(), $pages)) {
                $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
                $img = '<div><img src="'.$request->url('cron_image').'" alt=""'.$slash.'></div>';
                $content = $event->get('content');
                $content = preg_replace('/<\/body>/', $img . '</body>', $content, 1);
                $event->set('content', $content);
            }
        }
    }

    /**
     * Run cron.
     *
     * <p>This method is used by all methods to execute cron jobs.</p>
     *
     * <p>All output is captured and logged.</p>
     */
    public function runCron() {
        ob_start();
        $cron = new ZMCronJobs($this->getConfigPath('etc/crontab.txt'), $this->getConfigPath('etc/cronhistory.txt'));
        if ($cron->isTimeToRun()) {
            // update timestamp to stop other instances from running
            $cron->updateTimestamp();
            foreach ($cron->getJobs(false, ZMLangUtils::asBoolean($this->get('missedRuns'))) as $job) {
                $cron->runJob($job);
            }
        }
        Runtime::getLogging()->debug('ZMCron: '.ob_get_clean());
    }

}
