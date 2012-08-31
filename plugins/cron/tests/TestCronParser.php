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
namespace ZenMagick\plugins\cron\tests;

use ZenMagick\Base\Beans;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test the cron parser.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestCronParser extends TestCase {
    protected static $TEXT_CRONTAB = array(
        '0    5    0    *    Sun      ZenMagick\plugins\cron\jobs\SimpleCronJob         # every sunday at 5 am',
        '40   5    2    *    -        ZenMagick\plugins\cron\jobs\SimpleCronJob         # 5:40 am on the second day of each month',
        '*    8-19 *    *    Mon-Fri  ZenMagick\plugins\cron\jobs\SimpleCronJob         # Every minute on workdays between 8am and 7pm',
        '0-59 *    1-15 3,4  Mon-Fri  ZenMagick\plugins\cron\jobs\SimpleCronJob         # Every minute of every day, in the first half of March and April',
        '*/15 *    *    *    -        ZenMagick\plugins\cron\jobs\UpdateFacetsCronJob   # every 15 minutes'
    );

    /**
     * Get a parser instance.
     *
     * @retun CronParser A parser instance.
     */
    protected function getParser() {
        $parser = Beans::getBean('ZenMagick\plugins\cron\CronParser');
        $this->assertNotNull($parser);
        return $parser;
    }

    /**
     * Test sunday
     */
    public function testSunday() {
        $parser = $this->getParser();
        $result = $this->getParser()->parseCronTab(TestCronParser::$TEXT_CRONTAB[0]);
        $this->assertTrue(is_array($result));
        $this->assertEqual(1, count($result));
        $this->assertEqual(0, $result[0]['minutes']);
        $this->assertEqual(5, $result[0]['hours']);
        $this->assertEqual(0, $result[0]['mday']);
        $this->assertEqual('Sun', $result[0]['wday']);
        $this->assertEqual('ZenMagick\plugins\cron\jobs\SimpleCronJob', $result[0]['task']);
        $this->assertEqual(TestCronParser::$TEXT_CRONTAB[0], $result[0]['line']);
    }

    /**
     * Test range
     */
    public function testRange() {
        $parser = $this->getParser();
        $result = $this->getParser()->parseCronTab(TestCronParser::$TEXT_CRONTAB[4]);
        $this->assertTrue(is_array($result));
        $this->assertEqual(1, count($result));
        $this->assertEqual('*/15', $result[0]['minutes']);
        $this->assertEqual('*', $result[0]['hours']);
        $this->assertEqual('*', $result[0]['mday']);
        $this->assertEqual('-', $result[0]['wday']);
        $this->assertEqual('ZMUpdateFacetsCronJob', $result[0]['task']);
        $this->assertEqual(TestCronParser::$TEXT_CRONTAB[4], $result[0]['line']);

        // schedule tests
        foreach ($result[0]['schedule']['minutes'] as $ii => $value) {
            if (0 == ($ii%15)) {
                $this->assertTrue($value);
            } else {
                $this->assertFalse($value);
            }
        }
    }

}
