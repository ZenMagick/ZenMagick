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
namespace ZenMagick\Http\Toolbox;

use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Container for template related utilities.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Toolbox extends ContainerAware {
    private $request;
    private $tools;


    /**
     * Create new instance.
     */
    public function __construct() {
        $this->request = null;
        $this->tools = null;
    }


    /**
     * Set the request.
     *
     * @param ZenMagick\Http\Request request The current request.
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    /**
     * Get a map of all tools.
     *
     * @return array A map of all available tools.
     */
    public function getTools() {
        return $this->tools;
    }

    /**
     * Init all tools.
     *
     * @return array Map of all tools.
     */
    protected function initTools() {
        $tools = array();
        foreach ($this->container->get('containerTagService')->findTaggedServiceIds('zenmagick.http.toolbox.tool') as $id => $args) {
            $key = null;
            foreach ($args as $elem) {
                foreach ($elem as $key => $value) {
                    if ('key' == $key && $value) {
                        $key = $value;
                        break;
                    }
                }
            }

            $tool = $this->container->get($id);
            if ($tool instanceof ToolboxTool) {
                $tool->setToolbox($this);
                $tool->setRequest($this->request);
            }

            // set as member
            $this->$key = $tool;
            // and keep in list
            $tools[$key] = $tool;
        }

        return $tools;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container=null) {
        parent::setContainer($container);
        if (null === $this->tools) {
            $this->tools = $this->initTools();
        }
    }

}
