<?php

/**
 * Test who's online.
 *
 * @package org.zenmagick.plugins.zm_whos_online
 * @author DerManoMann
 * @version $Id$
 */
class TestWhosOnline extends ZMTestCase {

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('zm_whos_online');
    }

    /**
     * Test get stats.
     */
    public function testGetStatsOnly() {
        $stats = $this->getPlugin()->getStats(false);
        $this->assertEqual(array(0, 1, 1), $stats);
    }

    /**
     * Test get stats.
     */
    public function testGetStatsAndExpire() {
        $stats = $this->getPlugin()->getStats();
        $this->assertEqual(array(0, 1, 1), $stats);
    }

}

?>
