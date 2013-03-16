<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\Http\Templating;

use Symfony\Component\Templating\EngineInterface;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\View\View;
use ZenMagick\Http\Widgets\Widget;

/**
 * The ZenMagick PHP template engine.
 *
 * <p><code>render()</code> is the starting point of all template rendering. Parameters passed into <code>render()</code> will
 * be available to all templates called within the render call.</p>
 * <p>Parameters passes into <code>fetch()</code>, in turn, are only visible within the specific template fetched.</p>
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMPhpEngine extends ZMObject implements EngineInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }



        }

    }

    /**
     * Fetch/generate the contents for a given block group id.
     *
     * @param string group The group id.
     * @param array args Optional parameter; default is an empty array.
     * @return string The contents.
     */
    public function fetchBlockGroup($groupId, $args=array())
    {
        $contents = '';
        $request = $this->container->get('request');
        foreach ($this->container->get('blockManager')->getBlocksForId($request, $groupId, $args) as $block) {
//            Runtime::getLogging()->debug(sprintf('render block, template: %s', $block->getTemplate()));
            $contents .= $block->render($request, $this->container->get('defaultView'));
        }

        return $contents;
    }

    /**
     * Render a widget.
     *
     * @param mixed widget Either a <code>Widget</code> instance or a widget bean definition.
     * @param string name Optional name; default is <code>null</code> for none.
     * @param string value Optional value; default is <code>null</code> for none.
     * @param mixed args Optional parameter; a map of widget properties;  default is <code>null</code>.
     * @return string The widget contents.
     */
    public function widget($widget, $name=null, $value=null, $args=null)
    {
        $wObj = $widget;
        if (is_string($widget)) {
            $wObj = Beans::getBean($widget);
        }
        if (!($wObj instanceof Widget)) {
            Runtime::getLogging()->debug('invalid widget: '.$widget);

            return '';
        }
        if (null !== $name) {
            $wObj->setName($name);
            if (null === $args || !array_key_exists('id', $args)) {
                // no id set, so default to name
                $wObj->setId($name);
            }
        }
        if (null !== $value) {
            $wObj->setValue($value);
        }
        if (null !== $args) {
            Beans::setAll($wObj, $args);
        }

        return $wObj->render($this->container->get('request'), $this->container->get('defaultView'));
    }

}
