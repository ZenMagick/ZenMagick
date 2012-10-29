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

namespace ZenMagick\plugins\cron;

use ZenMagick\plugins\cron\jobs\CronJobInterface;

use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * A cron service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CronJobs extends ZMObject
{
    private $parser;
    private $cronfile;
    private $cronhistory;
    private $history;

    /**
     * Create a new instance.
     *
     * <p>Default files are expected/created in the same location as file containing this code.</p>
     *
     * @param string cronfile The crontab filename; default is <code>null</code> to use <em>crontab.txt</em>.
     * @param string cronhistory The cron history filename; default is <code>null</code> to use <em>cronhistory.txt</em>.
     */
    public function __construct($cronfile=null, $cronhistory=null)
    {
        parent::__construct();
        $this->cronfile = $cronfile;
        if (null == $this->cronfile) {
            $this->cronfile = dirname(__FILE__).'/crontab.txt';
        }
        $this->cronhistory = $cronhistory;
        if (null == $this->cronhistory) {
            $this->cronhistory = dirname(__FILE__).'/cronhistory.txt';
        }
        // load on demand
        $this->history = null;
        $this->parser = new CronParser();
    }

    /**
     * Check if it is time to run.
     *
     * <p>This will return <code>true</code> if either the history file doesn't exist,
     * or the last access time is older than one minute.</p>
     *
     * @return boolean <code>true</code> if it is time to run jobs again.
     */
    public function isTimeToRun()
    {
        return !is_file($this->cronhistory) || (time() - 60) > filemtime($this->cronhistory);
    }

    /**
     * Update the timestamp used to decide whether it is time to run or not.
     */
    public function updateTimestamp()
    {
        $this->ensureHistory();
        file_put_contents($this->cronhistory, serialize($this->history));
    }

    /**
     * Ensure the history is loaded and initialized.
     */
    private function ensureHistory()
    {
        $this->history = unserialize(@file_get_contents($this->cronhistory));
        if (!is_array($this->history)) {
            $this->history = array();
        }
    }

    /**
     * Save the last run time for the given job.
     *
     * @param array job The job.
     */
    private function saveLastRunTime($job)
    {
        $this->ensureHistory();
        $this->history[$job['id']] = $job['runTime'];
        file_put_contents($this->cronhistory, serialize($this->history));
    }

    /**
     * Get the last run time for the given job.
     *
     * @param array job The job.
     * @return int timestamp The time(stamp).
     */
    private function getLastRunTime($job)
    {
        $this->ensureHistory();
        if (isset($this->history[$job['id']])) {
            return $this->history[$job['id']];
        }

        return 0;
    }

    /**
     * Check if the given job is ready to run now.
     *
     * @param array job The job.
     * @return boolean <code>true</code> if the job is ready ro run.
     */
    public function isReady($job)
    {
        return $this->parser->isReady($job, time());
    }

    /**
     * Parse the crontab and get all jobs.
     *
     * @param boolean all If set to <code>true</code>, all jobs are returned. If <code>false</code> only jobs ready to be executed
     *  will be considered; default is <code>false</code>.
     * @param boolean catchup If <code>true</code>, jobs that have missed a run are also returned.
     * @return array A list of jobs.
     */
    public function getJobs($all=false, $catchup=false)
    {
        $jobs = array();
        if (file_exists($this->cronfile)) {
            $lines = file($this->cronfile);
            $jobs = $this->parser->parseCrontab($lines);
        } else {
            Runtime::getLogging()->err('crontab not found: '.$this->crontab);

            return array();
        }

        $time = time();
        $now = getdate($time);
        // adjust seconds
        $time = $time-$now['seconds'];
        $now = getdate($time);

        $selected = array();
        foreach ($jobs as $job) {
            // ready this minute?
            $job['ready'] = $this->parser->isReady($job, $now);
            // save runtime to be used by runJob(..)
            $job['runTime'] = $time;
            // just store it here as it is needed a couple times..
            $job['lastRunTime'] = $this->getLastRunTime($job);

            // either all or ready and lastRunTime is not now (if called more than once per minute)
            if ($all || ($job['ready'] && $job['lastRunTime'] < $time)) {
                $selected[] = $job;
            } elseif ($catchup) {
                $job['lastReadyTime'] = $this->parser->getLastReadyTime($job);
                if ($job['lastRunTime'] < $job['lastReadyTime']) {
                    // job not ready, but missed at least one run
                    $selected[] = $job;
                }
            }
        }

        return $selected;
    }

    /**
     * Execute a given job.
     *
     * @param array job A job.
     * @return boolean Returns <code>true</code>, if the job was run (based on the <em>lastScheduled</em> time.
     */
    public function runJob($job)
    {
        try {
            Runtime::getLogging()->info("CronJobs: Running: ".$job['line']);
            $obj = Beans::getBean($job['task']);
            if ($obj instanceof CronJobInterface) {
                $status = $obj->execute();
            }
            $this->saveLastRunTime($job);
            Runtime::getLogging()->info("CronJobs: Completed ".$job['line']." with status: ".($status?"OK":"FAILED"));

            return true;
        } catch (Exception $e) {
            Runtime::getLogging()->info("CronJobs: Failed ".$job['line']." with exception: ".$e);

            return false;
        }
    }

}
