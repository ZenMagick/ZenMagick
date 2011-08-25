<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?>
<?php

use zenmagick\base\Runtime;

/**
 * Test MinifyViewUtils implementation.
 *
 * @package org.zenmagick.plugins.minify
 * @author DerManoMann
 */
class TestMinifyViewUtils extends ZMTestCase {
    const MIN_BASE = '/zmdev/zenmagick/plugins/minify/min/f=';
    const COMMON_JS = '/zmdev/zenmagick/themes/default/content/common.js';
    const JQUERY_JS = '/zmdev/zenmagick/plugins/unitTests/content/js/jquery-1.2.1.pack.js';
    const EXT_JQUERY_JS = '//ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js';
    const EXT_JQUERY_VAL_JS = '//ajax.aspnetcdn.com/ajax/jquery.validate/1.8.1/jquery.validate.min.js';

    /**
     * Get a ready-to-use instance.
     *
     * @return MinifyViewUtils
     */
    protected function getViewUtils() {
        $view = new SavantView();
        $view->setVar('request', ZMRequest::instance());
        $viewUtils = new MinifyViewUtils($view);
        $viewUtils->setResourcesAsTemplates(true);
        return $viewUtils;
    }

    /**
     * Test empty.
     */
    public function testEmpty() {
        $viewUtils = $this->getViewUtils();
        $resources = $viewUtils->getResourceContents();
        $this->assertNull($resources);
    }

    /**
     * Test invalid.
     */
    public function testInvalid() {
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile('foo');

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' => '', 'footer' => ''), $resources);
    }

    /**
     * Test single local.
     */
    public function testSingleLocalJS() {
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile('common.js');

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test duplicate local.
     */
    public function testDuplicateLocalJS() {
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile('common.js');
        $viewUtils->jsFile('common.js');

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test multiple local.
     */
    public function testMultipleLocalJS() {
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile('common.js');
        $viewUtils->jsFile('js/jquery-1.2.1.pack.js');

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.','.self::JQUERY_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test single external.
     */
    public function testSingleExternalJS() {
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile(self::EXT_JQUERY_JS);

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::EXT_JQUERY_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test duplicate single external.
     */
    public function testDuplicateExternalJS() {
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile(self::EXT_JQUERY_JS);
        $viewUtils->jsFile(self::EXT_JQUERY_JS);

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::EXT_JQUERY_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test multiple external.
     */
    public function testMultipleExternalJS() {
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile(self::EXT_JQUERY_JS);
        $viewUtils->jsFile(self::EXT_JQUERY_VAL_JS);

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::EXT_JQUERY_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::EXT_JQUERY_VAL_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test mixed #1.
     */
    public function testMixed1JS() {
        // mixed #1
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile(self::EXT_JQUERY_JS);
        $viewUtils->jsFile('common.js');
        $viewUtils->jsFile('js/jquery-1.2.1.pack.js');
        $viewUtils->jsFile(self::EXT_JQUERY_VAL_JS);

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::EXT_JQUERY_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.','.self::JQUERY_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::EXT_JQUERY_VAL_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test mixed #2.
     */
    public function testMixed2JS() {
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile(self::EXT_JQUERY_JS);
        $viewUtils->jsFile(self::EXT_JQUERY_VAL_JS);
        $viewUtils->jsFile('common.js');
        $viewUtils->jsFile('js/jquery-1.2.1.pack.js');

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::EXT_JQUERY_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::EXT_JQUERY_VAL_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.','.self::JQUERY_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test mixed #3.
     */
    public function testMixed3JS() {
        $viewUtils = $this->getViewUtils();
        $viewUtils->jsFile(self::EXT_JQUERY_JS);
        $viewUtils->jsFile('common.js');
        $viewUtils->jsFile(self::EXT_JQUERY_VAL_JS);
        $viewUtils->jsFile('js/jquery-1.2.1.pack.js');

        $resources = $viewUtils->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::EXT_JQUERY_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::EXT_JQUERY_VAL_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::MIN_BASE.self::JQUERY_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

}
