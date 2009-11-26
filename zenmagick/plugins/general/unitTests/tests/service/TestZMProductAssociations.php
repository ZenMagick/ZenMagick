<?php

/**
 * Test <code>ZMProductAssociations</code>.
 *
 * @package org.zenmagick.plugins.zm_token.tests
 * @author DerManoMann
 * @version $Id: TestZMProductAssociations.php 2427 2009-07-14 10:24:33Z dermanomann $
 */
class TestZMProductAssociations extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();
        ZMProductAssociations::instance()->registerHandler(new SimpleProductAssociationHandler());
    }

    /**
     * Test simple handler.
     */
    public function testSimpleHandler() {
        $assoc = ZMProductAssociations::instance()->getProductAssociationsForProductId(12, 'simple');
        $this->assertEqual(array(), $assoc);

        $assoc = ZMProductAssociations::instance()->getProductAssociationsForProductId(13, 'foo');
        $this->assertEqual(array(), $assoc);

        $assoc = ZMProductAssociations::instance()->getProductAssociationsForProductId(13, 'simple');
        $this->assertEqual(1, count($assoc));
    }

}

?>
