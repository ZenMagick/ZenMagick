<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * Portions (c) 2003,2004 Kai Blankenhorn
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

/**
 * A crontab parser and handler.
 *
 * <p>Based (loosely) on pseudo-cron (v1.3) by Kai Blankenhorn http://www.bitfolge.de/pseudocron</p>
 *
 * <p>Restrictions:</p>
 * <ul>
 *  <li><em>wday</em> and <em>mday</em> are always both applied (merged)</li>
 * </ul>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CronParser
{
    /**
     * Parse a single date/time field into an on/off style list.
     *
     * <p>This will build an array of <em>size</em> elements, with those elements set to <code>true</code> that are
     * specified in the given field.</p>
     *
     * <p>In this context, a field consists of a single date/time field as defined by the Unix crontab file</p>
     *
     * <p><strong>NOTE:</strong> This will initialize elements with key <em>0</em> even if the first valid
     * corresponding date value starts with <em>1</em>. Examples are <em>mday</em> and <em>mon</em>.</p>
     *
     * @param string field The date/time field.
     * @param int size The size of the list.
     * @return array An array with those elements set to <code>true</code>, that are configured.
     */
    protected function parseDateTimeField($field, $size)
    {
        // convert dow to int, just in case...
        $field = str_replace(array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', '7'), array(0, 1, 2, 3, 4, 5, 6, 0), strtolower($field));
        $field = str_replace(array('jan', 'feb', 'mar', 'apr', 'mai', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'), array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12), $field);

        $configured = array();
        // break down into list items
        $items = explode(',', $field);
        // init array and already handle '*'
        for ($ii=0; $ii<$size; ++$ii) {
            $configured[$ii] = ('*' == $items[0]);
        }

        // parse and handle each item
        foreach ($items as $item) {
            if (false !== ($hits = preg_match("~^(\\*|([0-9]{1,2})(-([0-9]{1,2}))?)(/([0-9]{1,2}))?$~", $item, $tokens)) && 0 < $hits) {
                $tokenCount = count($tokens);
                if ('*' == $tokens[1]) {
                    // from
                    $tokens[2] = 0;
                    //to
                    $tokens[4] = $size;
                } elseif (5 > $tokenCount || '' == $tokens[4]) {
                    // single number
                    $tokens[4] = $tokens[2];
                }
                if (5 < $tokenCount && '/' == $tokens[5][0]) {
                    // increment to size
                    $tokens[4] = $size;
                } else {
                    // not a step, so increment by 1
                    $tokens[6] = 1;
                }
                $from = intval($tokens[2]);
                $to = intval($tokens[4]);
                $increment = intval($tokens[6]);
                for ($jj=$from; $jj<=$to; $jj+=$increment) {
                    $configured[$jj] = true;
                }
            }
        }

        return $configured;
    }

    /**
     * Load and parse the given crontab file.
     *
     * <p>Crontab lines are following the UNIX crontrab file format with the single exception of missing
     * the user column.</p>
     *
     * @param mixed crontab Either a single crontab line or an array of lines.
     * @return array A list of parsed cron jobs.
     */
    public function parseCrontab($crontab)
    {
        if (!is_array($crontab)) {
            $crontab = array($crontab);
        }

        $jobs = array();
        foreach ($crontab as $line) {
            if ('#' != $line[0]) {
                $token = array();
                if (preg_match("~^([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-7,/*]+|(-|/|Sun|Mon|Tue|Wed|Thu|Fri|Sat)+)\\s+([^#]*)\\s*(#.*)?$~i", $line, $token)) {
                    // parse schedule
                    $schedule = array();
                    $schedule['minutes'] = $this->parseDateTimeField($token[1], 60);
                    $schedule['hours'] = $this->parseDateTimeField($token[2], 24);
                    $schedule['mday'] = $this->parseDateTimeField($token[3], 31);
                    $schedule['mon'] = $this->parseDateTimeField($token[4], 12);
                    $schedule['wday'] = $this->parseDateTimeField($token[5], 7);

                    // build job
                    $job = array();
                    $job['minutes'] = $token[1];
                    $job['hours'] = $token[2];
                    $job['mday'] = $token[3];
                    $job['mon'] = $token[4];
                    $job['wday'] = $token[5];
                    $job['task'] = trim($token[7]);
                    // id is all date/time values plus the task
                    $job['id'] = md5(implode('|', $job));
                    $job['comment'] = 8 < count($job) ? trim(substr($job[8], 1)) : '';
                    $job['line'] = $line;
                    $job['schedule'] = $schedule;
                    $jobs[] = $job;
                }
            }
        }

        return $jobs;
    }

    /**
     * Find out if the given job is ready at the given time(-stamp).
     *
     * @param array job A job.
     * @param mixed date Either a Unix timestamp or an array as returned by <code>date()</code>.
     * @return boolean <code>true</code>, if the schedule is ready.
     */
    public function isReady($job, $date)
    {
        $schedule = $job['schedule'];

        if (!is_array($date)) {
            $date = getdate($date);
        }

        return $schedule['mon'][$date['mon']]
            && ($schedule['mday'][$date['mday']] || $schedule['mday'][$date['mday']])
            && $schedule['hours'][$date['hours']]
            && $schedule['minutes'][$date['minutes']];
    }

    /**
     * Calculate the last ready time for the given job.
     *
     * @param array job The job.
     * @return int The last ready timestamp or <em>0</em>.
     */
    public function getLastReadyTime($job)
    {
        $schedule = $job['schedule'];

        $time = time();
        $date = getdate($time);

        // find last matching month
        $useMax = false;
        $year = $date['year'];
        $mon = $date['mon'];
        for ($ii=0; $ii<12; ++$ii) {
            if ($schedule['mon'][$mon]) {
                break;
            }
            // going back, so start with latest day/hours/minutes
            $useMax = true;
            --$mon;
            if (1 > $mon) {
                $mon += 12;
                --$year;
            }
        }

        // TODO: what about the 28???
        // find the last matching day
        $days_per_month = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if ($useMax) {
            $mday = $days_per_month[$mon];
            // figure out the wday we start with
            $tmp = getdate(mktime($date['hours'], $date['minutes'], 0, $mon, $mday, $year));
            $wday = $tmp['wday'];
        } else {
            // t'is today
            $mday = $date['mday'];
            $wday = $date['wday'];
        }
        for ($ii=0; $ii<31; ++$ii) {
            if ($schedule['mday'][$mday] || $schedule['wday'][$wday]) {
                break;
            }
            $useMax = true;
            --$mday;
            if (1 > $mday) {
                $mday = $days_per_month[(1 < $mon ? $mon-1 : 12)];
            }
            --$wday;
            if (0 > $wday) {
                $wday += 7;
            }
        }

        // find last matching hours
        $hours = $useMax ? 23 : $date['hours'];
        for ($ii=0; $ii<24; ++$ii) {
            if ($schedule['hours'][$hours]) {
                break;
            }
            --$hours;
            if (0 > $hours) {
                $hours += 24;
            }
        }

        // find last matching minute
        $minutes = $useMax ? 59 : $date['minutes'];
        for ($ii=0; $ii<60; ++$ii) {
            if ($schedule['minutes'][$minutes]) {
                break;
            }
            --$minutes;
            if (0 > $minutes) {
                $minutes += 60;
            }
        }

        return mktime($hours, $minutes, 0, $mon, $mday, $year);
    }

}
