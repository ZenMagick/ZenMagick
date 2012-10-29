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

use ZenMagick\StoreBundle\Menu\Node;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test Node.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestNode extends TestCase
{
    public function testChildren()
    {
        $root = new Node();
        $this->assertEqual(array(), $root->getChildren());
        $children = array(new Node());
        $root->addChild($children[0]);
        $this->assertEqual($children, $root->getChildren());
    }

    public function testGetElementForId()
    {
        $root = new Node();
        $root->addChild(new Node('r1'));
        $root->addChild(new Node('r2'));
        $root->addChild(new Node('r3'));

        // check for child
        $r2 = $root->getNodeForId('r2');
        if ($this->assertNotNull($r2)) {
            $this->assertEqual('r2', $r2->getId());
        }
        $r2 = $root->getNodeForId('r2');
        if ($this->assertNotNull($r2)) {
            $this->assertEqual('r2', $r2->getId());
        }

        // check grandchild
        $r2->addChild(new Node('r2-1', 'foo'));
        $r21 = $root->getNodeForId('r2-1');
        if ($this->assertNotNull($r21)) {
            $this->assertEqual('foo', $r21->getName());
        }
    }

    public function testHasChildren()
    {
        $root = new Node();
        $root->addChild(new Node('r1'));
        $root->addChild(new Node('r2'));
        $root->addChild(new Node('r3'));
        $this->assertTrue($root->hasChildren());
    }

    public function testRemoveChild()
    {
        $root = new Node();
        $root->addChild(new Node('r1'));
        $root->addChild(new Node('r2'));
        $root->addChild(new Node('r3'));
        $root->removeChild('r2');

        $children = array();
        foreach ($root->getChildren() as $child) {
            $children[] = $child->getId();
        }
        $this->assertEqual(array('r1', 'r3'), $children);

        $root->removeChild($root->getNodeForId('r1'));
        $children = array();
        foreach ($root->getChildren() as $child) {
            $children[] = $child->getId();
        }
        $this->assertEqual(array('r3'), $children);
    }

    public function testFindNodes()
    {
        $r2 = new Node('r2', 'nr2');
        $r1 = new Node('r1', 'nr1');
        $r1->addChild($r2);
        $root = new Node();
        $root->addChild($r1);
        $this->assertEqual('nr1', $root->getNodeForId('r1')->getName());
        $this->assertEqual('nr2', $root->getNodeForId('r2')->getName());
        $nodes = $root->findNodes(function ($node) { return 'r2' == $node->getId(); });
        $this->assertNotNull($nodes);
        if ($this->assertEqual(1, count($nodes))) {
            $node1 = $nodes[0];
            $this->assertEqual('nr2', $node1->getName());
        }
    }

}
