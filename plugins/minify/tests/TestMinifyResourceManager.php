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

use ZenMagick\Base\Runtime;
use ZenMagick\apps\store\view\ThemeResourceResolver;
use ZenMagick\plugins\minify\view\MinifyResourceManager;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test MinifyResourceManager implementation.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package plugins.minify
 */
class TestMinifyResourceManager extends TestCase {
    const MIN_BASE = '/zmdev/zenmagick/plugins/minify/min/f=';
    const COMMON_JS = '/zmdev/zenmagick/themes/base/content/common.js';
    const JQUERY_JS = '/zmdev/zenmagick/plugins/unitTests/content/js/jquery-1.2.1.pack.js';
    const EXT_JQUERY_JS = '//ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js';
    const EXT_JQUERY_VAL_JS = '//ajax.aspnetcdn.com/ajax/jquery.validate/1.8.1/jquery.validate.min.js';
    const SITE_CSS = '/zmdev/zenmagick/themes/base/content/site.css';
    const POPUP_CSS = '/zmdev/zenmagick/themes/base/content/popup.css';
    const EXT_JQUERY_CSS1 = '//ajax.aspnetcdn.com/ajax/jquery.ui/1.8.9/themes/blitzer/jquery-ui.css';
    const EXT_JQUERY_CSS2 = 'http://ajax.aspnetcdn.com/ajax/jquery.ui/1.8.9/themes/ui-lightness/jquery-ui.css';

    /**
     * Get a ready-to-use instance.
     *
     * @return MinifyResourceManager
     */
    protected function getMinifyResourceManager() {
        $view = $this->container->get('defaultView');
        $resourceManager = new MinifyResourceManager();
        $resourceManager->setContainer($this->container);
        $resourceManager->setResourcesAsTemplates(true);
        $view->setResourceManager($resourceManager);
        $themeResourceResolver = new ThemeResourceResolver();
        $themeResourceResolver->setContainer($this->container);
        $view->setResourceResolver($themeResourceResolver);
        return $resourceManager;
    }

    /**
     * Test empty.
     */
    public function testEmpty() {
        $resourceManager = $this->getMinifyResourceManager();
        $resources = $resourceManager->getResourceContents();
        $this->assertNull($resources);
    }

    /**
     * Test invalid.
     */
    public function testInvalid() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile('foo');

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' => '', 'footer' => ''), $resources);
    }

    /**
     * Test single local.
     */
    public function testSingleLocalJS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile('common.js');

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test duplicate local.
     */
    public function testDuplicateLocalJS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile('common.js');
        $resourceManager->jsFile('common.js');

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test multiple local.
     */
    public function testMultipleLocalJS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile('common.js');
        $resourceManager->jsFile('js/jquery-1.2.1.pack.js');

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.','.self::JQUERY_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test single external.
     */
    public function testSingleExternalJS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile(self::EXT_JQUERY_JS);

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::EXT_JQUERY_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test duplicate single external.
     */
    public function testDuplicateExternalJS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile(self::EXT_JQUERY_JS);
        $resourceManager->jsFile(self::EXT_JQUERY_JS);

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::EXT_JQUERY_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test multiple external.
     */
    public function testMultipleExternalJS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile(self::EXT_JQUERY_JS);
        $resourceManager->jsFile(self::EXT_JQUERY_VAL_JS);

        $resources = $resourceManager->getResourceContents();
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
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile(self::EXT_JQUERY_JS);
        $resourceManager->jsFile('common.js');
        $resourceManager->jsFile('js/jquery-1.2.1.pack.js');
        $resourceManager->jsFile(self::EXT_JQUERY_VAL_JS);

        $resources = $resourceManager->getResourceContents();
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
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile(self::EXT_JQUERY_JS);
        $resourceManager->jsFile(self::EXT_JQUERY_VAL_JS);
        $resourceManager->jsFile('common.js');
        $resourceManager->jsFile('js/jquery-1.2.1.pack.js');

        $resources = $resourceManager->getResourceContents();
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
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->jsFile(self::EXT_JQUERY_JS);
        $resourceManager->jsFile('common.js');
        $resourceManager->jsFile(self::EXT_JQUERY_VAL_JS);
        $resourceManager->jsFile('js/jquery-1.2.1.pack.js');

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          '<script type="text/javascript" src="'.self::EXT_JQUERY_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::MIN_BASE.self::COMMON_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::EXT_JQUERY_VAL_JS.'"></script>'."\n".
          '<script type="text/javascript" src="'.self::MIN_BASE.self::JQUERY_JS.'"></script>'."\n",
          'footer' => ''), $resources);
    }

    /**
     * Test single local.
     */
    public function testSingleLocalCSS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->cssFile('site.css');

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          "\n".
          '<link rel="stylesheet" type="text/css" href="'.self::MIN_BASE.self::SITE_CSS.'"/>'."\n".
          "\n",
          'footer' => ''), $resources);
    }

    /**
     * Test duplicate local.
     */
    public function testDuplicateLocalCSS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->cssFile('site.css');
        $resourceManager->cssFile('site.css');

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          "\n".
          '<link rel="stylesheet" type="text/css" href="'.self::MIN_BASE.self::SITE_CSS.'"/>'."\n".
          "\n",
          'footer' => ''), $resources);
    }

    /**
     * Test multiple local.
     */
    public function testMultipleLocalCSS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->cssFile('site.css');
        $resourceManager->cssFile('popup.css');

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          "\n".
          '<link rel="stylesheet" type="text/css" href="'.self::MIN_BASE.self::SITE_CSS.','.self::POPUP_CSS.'"/>'."\n".
          "\n",
          'footer' => ''), $resources);
    }

    /**
     * Test single external.
     */
    public function testSingleExternalCSS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->cssFile(self::EXT_JQUERY_CSS1);

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          "\n".
          '<link rel="stylesheet" type="text/css" href="'.self::EXT_JQUERY_CSS1.'"/>'."\n".
          "\n",
          'footer' => ''), $resources);
    }

    /**
     * Test duplicate single external.
     */
    public function testDuplicateExternalCSS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->cssFile(self::EXT_JQUERY_CSS1);
        $resourceManager->cssFile(self::EXT_JQUERY_CSS1);

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          "\n".
          '<link rel="stylesheet" type="text/css" href="'.self::EXT_JQUERY_CSS1.'"/>'."\n".
          "\n",
          'footer' => ''), $resources);
    }

    /**
     * Test multiple external.
     */
    public function testMultipleExternalCSS() {
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->cssFile(self::EXT_JQUERY_CSS1);
        $resourceManager->cssFile(self::EXT_JQUERY_CSS2);

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          "\n".
          '<link rel="stylesheet" type="text/css" href="'.self::EXT_JQUERY_CSS1.'"/>'."\n".
          '<link rel="stylesheet" type="text/css" href="'.self::EXT_JQUERY_CSS2.'"/>'."\n".
          "\n",
          'footer' => ''), $resources);
    }

    /**
     * Test mixed #1.
     */
    public function testMixed1CSS() {
        // mixed #1
        $resourceManager = $this->getMinifyResourceManager();
        $resourceManager->cssFile(self::EXT_JQUERY_CSS1);
        $resourceManager->cssFile('site.css');
        $resourceManager->cssFile('popup.css');
        $resourceManager->cssFile(self::EXT_JQUERY_CSS2);

        $resources = $resourceManager->getResourceContents();
        $this->assertNotNull($resources);
        $this->assertEqual(array('header' =>
          "\n".
          '<link rel="stylesheet" type="text/css" href="'.self::EXT_JQUERY_CSS1.'"/>'."\n".
          '<link rel="stylesheet" type="text/css" href="'.self::MIN_BASE.self::SITE_CSS.','.self::POPUP_CSS.'"/>'."\n".
          '<link rel="stylesheet" type="text/css" href="'.self::EXT_JQUERY_CSS2.'"/>'."\n".
          "\n",
          'footer' => ''), $resources);
    }

}
