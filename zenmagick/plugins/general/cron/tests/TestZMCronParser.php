<?php

/**
 * Test the cron parser.
 *
 * @package org.zenmagick.plugins.cron.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMCronParser extends ZMTestCase {
    protected static $TEXT_CRONTAB = array(
        '0    5    0    *    Sun      ZMSimpleCronJob         # every sunday at 5 am',
        '40   5    2    *    -        ZMSimpleCronJob         # 5:40 am on the second day of each month',
        '*    8-19 *    *    Mon-Fri  ZMSimpleCronJob         # Every minute on workdays between 8am and 7pm',
        '0-59 *    1-15 3,4  Mon-Fri  ZMSimpleCronJob         # Every minute of every day, in the first half of March and April',
        '*/15 *    *    *    -        ZMUpdateFacetsCronJob   # every 15 minutes'
    );

    /**
     * Get a parser instance.
     *
     * @retun ZMCronParser A parser instance.
     */
    protected function getParser() {
        $parser = ZMLoader::make('CronParser');
        $this->assertNotNull($parser);
        return $parser;
    }

    /**
     * Test sunday
     */
    public function testSunday() {
        $parser = $this->getParser();
        $result = $this->getParser()->parseCronTab(TestZMCronParser::$TEXT_CRONTAB[0]);
        $this->assertTrue(is_array($result));
        $this->assertEqual(1, count($result));
        $this->assertEqual(0, $result[0]['minutes']);
        $this->assertEqual(5, $result[0]['hours']);
        $this->assertEqual(0, $result[0]['mday']);
        $this->assertEqual('Sun', $result[0]['wday']);
        $this->assertEqual('ZMSimpleCronJob', $result[0]['task']);
        $this->assertEqual(TestZMCronParser::$TEXT_CRONTAB[0], $result[0]['line']);
    }

    /**
     * Test range
     */
    public function testRange() {
        $parser = $this->getParser();
        $result = $this->getParser()->parseCronTab(TestZMCronParser::$TEXT_CRONTAB[4]);
        $this->assertTrue(is_array($result));
        $this->assertEqual(1, count($result));
        $this->assertEqual('*/15', $result[0]['minutes']);
        $this->assertEqual('*', $result[0]['hours']);
        $this->assertEqual('*', $result[0]['mday']);
        $this->assertEqual('-', $result[0]['wday']);
        $this->assertEqual('ZMUpdateFacetsCronJob', $result[0]['task']);
        $this->assertEqual(TestZMCronParser::$TEXT_CRONTAB[4], $result[0]['line']);

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

?>
