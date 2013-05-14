<?php
/*
 * This file is part of ZenMagick.
 *
 * (c) 2013 Johnny Robeson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZenMagick\Twig\Node\Expression\Test;

/**
 * Checks if a variable is an instanceof a class.
 *
 * <pre>
 *  {% if object is aninstanceof('Some\\Class') %}
 * </pre>
 */
class Aninstanceof extends \Twig_Node_Expression_Test
{
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('node'))
            ->raw(' instanceof ')
            ->raw($this->getNode('arguments')->getNode(0)->getAttribute('value'))
            ->raw(')')
        ;
    }
}
