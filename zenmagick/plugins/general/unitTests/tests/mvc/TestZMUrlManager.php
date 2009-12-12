<?php

/**
 * Test url manager.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMUrlManager extends ZMTestCase {

    /**
     * Test global.
     */
    public function testGlobal() {
        $manager = new ZMUrlManager();
        $manager->setMapping(null, array('error' => array('template' => 'error')));
        $mapping = $manager->findMapping('foo', 'error');
        $this->assertEqual(array('controller'=>null,'formId'=>null,'form'=>null,'view'=>null,'template'=>'error'), $mapping);

        // test store mapping
        $mapping = ZMUrlManager::instance()->findMapping('foo', 'empty_cart');
        $this->assertEqual(array('controller'=>null,'formId'=>null,'form'=>null,'view'=>'RedirectView','template'=>'shopping_cart'), $mapping);
    }

}

?>
