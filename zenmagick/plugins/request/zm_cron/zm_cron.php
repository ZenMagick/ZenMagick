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
 * Plugin to allow cron like execution of <code>ZMCronJob</code> classes.
 *
 * @package org.zenmagick.plugins.zm_cron
 * @author DerManoMann
 * @version $Id$
 */
class zm_cron extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ZenMagick CronJobs', 'Allows to configure and execute cron jobs', '${plugin.version}');
        $this->setLoaderSupport('ALL');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();
    }


    /**
     * Run cron.
     *
     * <p>This method is used by all methods to execute cron jobs.</p>
     */
    public function runCron() {
        //TODO: check for config options and CLI setting to avoid people running this via manual URL guessing
        //TODO: seet catchup via config

        $folder = $this->getPluginDir();
        $cron = ZMLoader::make('ZMCronJobs', $folder.'/etc/crontab.txt', $folder.'etc/cronhistory.txt');
        if ($cron->isTimeToRun()) {
            foreach ($cron->getJobs(false, false) as $job) {
                $cron->runJob($job);
            }
        }
    }

}

?>
