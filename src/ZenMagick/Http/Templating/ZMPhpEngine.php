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

use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Http\View\View;
use ZenMagick\Http\Widgets\Widget;
use ZenMagick\Http\Toolbox\Toolbox;
use ZenMagick\Http\Toolbox\ToolboxTool;

use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Bundle\FrameworkBundle\Templating\PhpEngine;
use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables;

/**
 * The ZenMagick PHP template engine.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMPhpEngine extends PhpEngine
{
    /**
     * Constructor.
     */
    public function __construct(TemplateNameParserInterface $parser, ContainerInterface $container, LoaderInterface $loader, GlobalVariables $globals = null)
    {
        parent::__construct($parser, $container, $loader, $globals);

        /**
         *  @todo move most of these to template helpers/existing globals
         */
        $this->addGlobal('templateView', $this->container->get('templateView'));

       // find services tagged as view variables
        foreach ($container->get('containerTagService')->findTaggedServiceIds('zenmagick.http.view.variable') as $id => $args) {
            $key = null;
            foreach ($args as $elem) {
                foreach ($elem as $key => $value) {
                    if ('key' == $key && $value) {
                        $key = $value;
                        break;
                    }
                }
            }
            $obj = $this->container->get($id);
            if ($obj instanceof Toolbox) {
                foreach ($obj->getTools() as $name => $tool) {
                    if ($tool instanceof ToolboxTool) {
                        $tool->setView($this->container->get('defaultView'));
                    }
                    $this->addGlobal($name, $tool);
                }
            }
            $this->addGlobal($key, $obj);
        }

        // set all plugins
        if ($this->container->has('pluginService')) { // @todo inject this instead
            foreach ($this->container->get('pluginService')->getPluginsForContext() as $plugin) {
                $this->addGlobal($plugin->getId(), $plugin);
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
