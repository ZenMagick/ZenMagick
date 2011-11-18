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
use apps\store\menu\Menu;
use apps\store\menu\MenuLoader;
use apps\store\menu\MenuElement;

use Symfony\Component\Yaml\Yaml;
/**
 * Test Menu.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestMenu extends ZMTestCase {

    public function testChildren() {
        $menu = new Menu();
        $root = $menu->getRoot();
        $this->assertNotNull($root);
        $this->assertEqual(array(), $root->getChildren());
        $children = array(new MenuElement());
        $root->addChild($children[0]);
        $this->assertEqual($children, $root->getChildren());
    }

    public function testGetElementForId() {
        $menu = new Menu();
        $root = $menu->getRoot();
        $root->addChild(new MenuElement('r1'));
        $root->addChild(new MenuElement('r2'));
        $root->addChild(new MenuElement('r3'));

        // check for child
        $r2 = $root->getElementForId('r2');
        if ($this->assertNotNull($r2)) {
            $this->assertEqual('r2', $r2->getId());
        }
        $r2 = $menu->getElement('r2');
        if ($this->assertNotNull($r2)) {
            $this->assertEqual('r2', $r2->getId());
        }

        // check grandchild
        $r2->addChild(new MenuElement('r2-1', 'foo'));
        $r21 = $root->getElementForId('r2-1');
        if ($this->assertNotNull($r21)) {
            $this->assertEqual('foo', $r21->getName());
        }

        // same on Menu
        $r21 = $menu->getElement('r2-1');
        if ($this->assertNotNull($r21)) {
            $this->assertEqual('foo', $r21->getName());
        }
    }

    public function testInsert() {
        $menu = new Menu();
        $root = $menu->getRoot();
        $root->addChild(new MenuElement('r1'));
        $root->addChild(new MenuElement('r2'));
        $root->addChild(new MenuElement('r3'));
        $menu->insertBefore('r2', new MenuElement('r2-pre'));
        // build child id list
        $children = array();
        foreach ($root->getChildren() as $child) {
            $children[] = $child->getId();
        }
        $this->assertEqual(array('r1', 'r2-pre', 'r2', 'r3'), $children);

        $menu = new Menu();
        $root = $menu->getRoot();
        $root->addChild(new MenuElement('r1'));
        $root->addChild(new MenuElement('r2'));
        $root->addChild(new MenuElement('r3'));
        $menu->insertAfter('r2', new MenuElement('r2-post'));
        // build child id list
        $children = array();
        foreach ($root->getChildren() as $child) {
            $children[] = $child->getId();
        }
        $this->assertEqual(array('r1', 'r2', 'r2-post', 'r3'), $children);

        $menu = new Menu();
        $root = $menu->getRoot();
        $root->addChild(new MenuElement('r1'));
        $root->addChild(new MenuElement('r2'));
        $root->addChild(new MenuElement('r3'));
        $menu->addChild('r2', new MenuElement('r2-c'));

        $r2 = $menu->getElement('r2');
        if ($this->assertNotNull($r2)) {
            $this->assertTrue($r2->hasChildren());
            $this->assertNotNull($menu->getElement('r2-c'));
        }

        $r2c = $menu->getElement('r2-c');
        $r2cc = new MenuElement('r2cc');
        $r2c->addChild($r2cc);
        $this->assertEqual(array('r2', 'r2-c', 'r2cc'), $r2cc->getPath(true));
        $this->assertEqual(array('r2', 'r2-c'), $r2cc->getPath(false));
    }

    public function testHasChildren() {
        $menu = new Menu();
        $root = $menu->getRoot();
        $root->addChild(new MenuElement('r1'));
        $root->addChild(new MenuElement('r2'));
        $root->addChild(new MenuElement('r3'));
        $this->assertTrue($root->hasChildren());
    }

    public function testLoader() {
        $menuLoader = new MenuLoader();
        //$menuLoader->load($this->getTestsBaseDirectory().'/misc/config/menu.yaml');
        $menu = $menuLoader->load($this->getTestsBaseDirectory().'/misc/config/menu2.yaml');
        $menu = $menuLoader->load($this->getTestsBaseDirectory().'/misc/config/menu3.yaml', $menu);
        $this->dumpMenu($menu->getRoot());
    }

    protected function dumpMenu($elem, $l=1) {
        $indent = '&nbsp;';
        for ($ii=0; $ii < $l; ++$ii) {
            $indent .= '&nbsp;&nbsp;';
        }
        echo $indent.' * '.$elem->getName().'/'.$elem->getRequestId()."<br>";
        foreach ($elem->getChildren() as $child) {
            $this->dumpMenu($child, $l+1);
        }
    }

}
