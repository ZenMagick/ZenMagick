<?php

/**
 * Test who's online.
 *
 * @package org.zenmagick.plugins.whoIsOnline
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestWhoIsOnline extends ZMTestCase {

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return $this->container->get('pluginService')->getPluginForId('whoIsOnline');
    }

    /**
     * Test get stats.
     */
    public function testGetStatsOnly() {
        $stats = $this->getPlugin()->getStats(false);
        $this->assertEqual(array('anonymous' => 1, 'registered' => 0, 'total' => 1), $stats);
    }

    /**
     * Test get stats.
     */
    public function testGetStatsAndExpire() {
        $stats = $this->getPlugin()->getStats(true);
        $this->assertEqual(array('anonymous' => 1, 'registered' => 0, 'total' => 1), $stats);
    }

    /**
     * Test sidebox full.
     */
    public function testFull() {
        /**
         * anonymous  registered   text
         *     0           0       There is currently no one online :).
         *
         *     1           0       There is currently one guest online.
         *     0           1       There is currently one registered user online.
         *
         *    >1           0       There are currently %s guests online.
         *     0          >1       There are currently %s registered users online.
         *    >1           1       There are currently %s guests and one registered user online.
         *    >1          >1       There are currently %s guests and %s registered users online.
         *
         *     1           1       There are currently one guest and one registered user online.
         *     1          >1       There are currently one guest and %s registered users online.
         */
        $statsVariations = array(
            //array('anonymous' => 0, 'registered' => 0, 'total' => 0, 'expected' => 'There is currently no one online :)'),

            array('anonymous' => 1, 'registered' => 0, 'total' => 1, 'expected' => 'There is currently one guest online.'),
            array('anonymous' => 0, 'registered' => 1, 'total' => 1, 'expected' => 'There is currently one registered user online.'),

            array('anonymous' => 5, 'registered' => 0, 'total' => 5, 'expected' => 'There are currently 5 guests online.'),
            array('anonymous' => 0, 'registered' => 3, 'total' => 3, 'expected' => 'There are currently 3 registered users online.'),
            array('anonymous' => 5, 'registered' => 1, 'total' => 6, 'expected' => 'There are currently 5 guests and one registered user online.'),
            array('anonymous' => 5, 'registered' => 3, 'total' => 8, 'expected' => 'There are currently 5 guests and 3 registered users online.'),

            array('anonymous' => 1, 'registered' => 1, 'total' => 2, 'expected' => 'There are currently one guest and one registered user online.'),
            array('anonymous' => 1, 'registered' => 3, 'total' => 4, 'expected' => 'There are currently one guest and 3 registered users online.')
        );

        $sample1 = file_get_contents($this->getPlugin()->getPluginDirectory().'content/boxes/who_is_online_full.php');
        $sample1 = str_replace('$stats = $whoIsOnline->getStats();', '//$stats = $whoIsOnline->getStats();', $sample1);
        $whoIsOnline = $this->getPlugin();

        foreach ($statsVariations as $stats) {
            ob_start();
            eval('?>'.$sample1);
            $html = ob_get_clean();
            $this->assertTrue(false !== strpos($html, $stats['expected']), '%s '.serialize($stats).$html);
        }
    }

    /**
     * Test sidebox simple.
     */
    public function testSimple() {
        $statsVariations = array(
            array('anonymous' => 1, 'registered' => 0, 'total' => 1, 'expected' => 'There are currently 1 guests and 0 registered users online.'),
            array('anonymous' => 0, 'registered' => 1, 'total' => 1, 'expected' => 'There are currently 0 guests and 1 registered users online.'),
            array('anonymous' => 5, 'registered' => 0, 'total' => 5, 'expected' => 'There are currently 5 guests and 0 registered users online.'),
            array('anonymous' => 0, 'registered' => 3, 'total' => 3, 'expected' => 'There are currently 0 guests and 3 registered users online.'),
            array('anonymous' => 5, 'registered' => 1, 'total' => 6, 'expected' => 'There are currently 5 guests and 1 registered users online.'),
            array('anonymous' => 5, 'registered' => 3, 'total' => 8, 'expected' => 'There are currently 5 guests and 3 registered users online.'),
            array('anonymous' => 1, 'registered' => 1, 'total' => 2, 'expected' => 'There are currently 1 guests and 1 registered users online.'),
            array('anonymous' => 1, 'registered' => 3, 'total' => 4, 'expected' => 'There are currently 1 guests and 3 registered users online.')
        );

        $sample2 = file_get_contents($this->getPlugin()->getPluginDirectory().'content/boxes/who_is_online_simple.php');
        $sample2 = str_replace('$stats = $whoIsOnline->getStats();', '//$stats = $whoIsOnline->getStats();', $sample2);
        $whoIsOnline = $this->getPlugin();

        foreach ($statsVariations as $stats) {
            ob_start();
            eval('?>'.$sample2);
            $html = ob_get_clean();
            $this->assertTrue(false !== strpos($html, $stats['expected']), '%s '.serialize($stats).$html);
        }
    }

}
