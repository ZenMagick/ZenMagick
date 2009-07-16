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
 * Plugin to allow cron like execution of <code>ZMCronJob</code> classes.
 *
 * @package org.zenmagick.plugins.zm_cron
 * @author DerManoMann
 * @version $Id$
 */
class zm_cron extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('CronJobs', 'Allows to configure and execute cron jobs', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
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

        $this->addConfigValue('Trigger', 'image', 'false', 'Enable image trigger', 'zen_cfg_select_option(array(\'true\',\'false\'),');
        $this->addConfigValue('Image trigger pages', 'triggerPages', 'index', 'List of pages (separated by comma \',\') to be used for imger trigger');
        $this->addConfigValue('Missed run policy', 'missedRuns', 'false', 'Select what should happen when one or more runs have been missed', 
            "zen_cfg_select_drop_down(array(array('id'=>'false', 'text'=>'Ignore'), array('id'=>'true', 'text'=>'Catch-up')), ");
    }

    /**
     * Init this plugin.
     */
    public function init() {
        parent::init();

        $this->zcoSubscribe();

        // register tests
        if (null != ($tests = ZMPlugins::instance()->getPluginForId('zm_tests'))) {
            // add class path only now to avoid errors due to missing ZMTestCase
            ZMLoader::instance()->addPath($this->getPluginDirectory().'tests/');
            $tests->addTest('TestZMCronParser');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $contents = $args['contents'];

        if ($this->isEnabled() && ZMLangUtils::asBoolean($this->get('image'))) {
            $pages = $this->get('triggerPages');
            if (empty($pages) || ZMLangUtils::inArray(ZMRequest::instance()->getRequestId(), $pages)) {
                $slash = ZMSettings::get('zenmagick.mvc.xhtml') ? '/' : '';
                $img = '<div><img src="'.ZMToolbox::instance()->net->url('cron_image', '', false, false).'" alt=""'.$slash.'></div>';
                $contents = preg_replace('/<\/body>/', $img . '</body>', $contents, 1);
            }
        }

        $args['contents'] = $contents;
        return $args;
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
        ZMLogging::instance()->log('ZMCron: '.ob_get_clean(), ZMLogging::DEBUG);
    }

}

?>
