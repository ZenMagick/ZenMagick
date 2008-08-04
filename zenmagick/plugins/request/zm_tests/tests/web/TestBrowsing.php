<?php

class TestBrowsing extends WebTestCase {
    protected $secure = false;
    
    /**
     * Test homepage.
     */
    public function testHomepage() {
        $this->get(ZMToolbox::instance()->net->url(FILENAME_DEFAULT, '', $this->secure, false));
        $this->assertText('ZenMagick');
    }
}

?>
