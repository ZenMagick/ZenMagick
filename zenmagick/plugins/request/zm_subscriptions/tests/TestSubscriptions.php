<?php

/**
 * Test subscriptions.
 *
 * @package org.zenmagick.plugins.zm_subscriptions
 * @author DerManoMann
 * @version $Id$
 */
class TestSubscriptions extends UnitTestCase {

    /**
     * Test copy order.
     */
    public function testCopyOrder() {
    global $zm_subscriptions;

        $zm_subscriptions->copyOrder(132);
    }

}

?>
