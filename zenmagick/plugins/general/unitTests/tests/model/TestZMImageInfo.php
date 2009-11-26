<?php

/**
 * Test image info.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestZMImageInfo.php 2610 2009-11-20 02:45:25Z dermanomann $
 */
class TestZMImageInfo extends ZMTestCase {

    /**
     * Test split image name.
     */
    public function testSplitImagename() {
        ZMLoader::resolve('ZMImageInfo');
        $info = ZMImageInfo::splitImageName('/foo/bar/image.png');
        if ($this->assertTrue(is_array($info))) {
            if ($this->assertEqual(3, count($info))) {
                $this->assertEqual('/foo/bar/', $info[0]);
                $this->assertEqual('.png', $info[1]);
                $this->assertEqual('/foo/bar/image', $info[2]);
            }
        }
    }

}

?>
