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
namespace zenmagick\plugins\cron;

use Plugin;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;

/**
 * Plugin to allow cron like execution of <code>CronJobInterface</code> classes.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CronPlugin extends Plugin {

    /**
     * Install this plugin.
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Trigger', 'image', 'false', 'Enable image trigger',
            'widget@booleanFormWidget#name=image&default=false&label=Enable image trigger&style=checkbox');
        $this->addConfigValue('Image trigger pages', 'triggerPages', 'index', 'List of pages (separated by comma \',\') to be used for imger trigger');
        $this->addConfigValue('Missed run policy', 'missedRuns', 'false', 'Select what should happen when one or more runs have been missed',
            'widget@booleanFormWidget#name=missedRuns&default=false&style=select&label_true=Catch-up&label_false=Ignore');
    }

    /**
     * Handle event.
     */
    public function onFinaliseContent($event) {
        $request = $event->get('request');

        if ($this->isEnabled() && Toolbox::asBoolean($this->get('image'))) {
            $pages = explode(',', $this->get('triggerPages'));
            if (empty($pages) || in_array($request->getRequestId(), $pages)) {
                $slash = $this->container->get('settingsService')->get('zenmagick.http.html.xhtml') ? '/' : '';
                $img = '<div><img src="'.$request->url('cron_image').'" alt=""'.$slash.'></div>';
                $content = $event->get('content');
                $content = preg_replace('/<\/body>/', $img . '</body>', $content, 1);
                $event->set('content', $content);
            }
        }
    }

    /**
     * Get a plugin config file path.
     *
     * <p>Return a fully qualified filename; resolved either against the plugin directory or <code>config/</code>.
     * If neither file exists, the <code>config/</code> based filename is returned.</p>
     *
     * @param string file The filename.
     * @return string A fully qualified filename.
     */
    public function getConfigPath($file) {
        $configPath = Runtime::getInstallationPath().'config'.DIRECTORY_SEPARATOR;
        $configFile = $configPath.$this->getId().DIRECTORY_SEPARATOR.$file;

        if (file_exists($configFile) || !file_exists($this->getPluginDirectory().'/'.$file)) {
            return $configFile;
        }

        return $this->getPluginDirectory().'/'.$file;
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
        $cron = new CronJobs($this->getConfigPath('etc/crontab.txt'), $this->getConfigPath('etc/cronhistory.txt'));
        if ($cron->isTimeToRun()) {
            // update timestamp to stop other instances from running
            $cron->updateTimestamp();
            foreach ($cron->getJobs(false, Toolbox::asBoolean($this->get('missedRuns'))) as $job) {
                $cron->runJob($job);
            }
        }
        Runtime::getLogging()->debug('Cron: '.ob_get_clean());
    }

}
