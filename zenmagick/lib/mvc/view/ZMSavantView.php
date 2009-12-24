<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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


/**
 * A Savant(3) view.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.view
 * @version $Id$
 */
class ZMSavantView extends ZMView {
    private $savant_;
    private $config_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->savant_ = null;
        $this->config_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the array of locations to search for templates.
     *
     * @param ZMRequest request The current request.
     */
    public function getTemplatePath($request) {
        return array($request->getTemplatePath());
    }

    /**
     * Get the array of locations to search for resources.
     *
     * <p>This default implementation will just return <code>getTemplatePath($request)</code>.</p>
     *
     *
     * @param ZMRequest request The current request.
     */
    public function getResourcePath($request) {
        return $this->getTemplatePath($request);
    }

    /**
     * Set savant specific configuration options.
     *
     * @param mixed config Anything that can be converted into a map.
     */
    public function setConfig($config) {
        $this->config_ = ZMLangUtils::toArray($config);
        // XXX: add generic code to ZMBeanUtils?? handle bean references...
        foreach ($this->config_ as $key => $value) {
            if ('compiler' == $key) {
                $this->config_[$key] = ZMBeanUtils::getBean($value);
            }
        }
    }

    /**
     * Get a preconfigured Savant3 instance.
     *
     * @param ZMRequest request The current request.
     * @return Savant3 A ready-to-use instance.
     */
    protected function getSavant($request) {
        if (null === $this->savant_) {
            $config = array();
            $config['autoload'] = true;
            $config['exceptions'] = true;
            $config['extract'] = true;
            $config['template_path'] = $this->getTemplatePath($request);
            $config['resource_path'] = $this->getResourcePath($request);
            $config = array_merge($config, $this->config_);
            $this->savant_ = ZMLoader::make('Savant', $config);
        }

        return $this->savant_;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($request, $template) {
        $savant = $this->getSavant($request);

        // put all vars into local scope
        $savant->assign($this->getVars());

        // load template...
        try {
            return $savant->fetch($template);
        } catch (Exception $e) {
            ZMLogging::instance()->dump($e, 'failed to fetch template: '.$template, ZMLogging::ERROR);
            throw new ZMException('failed to fetch template: '.$template, 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request) {
        return $this->fetch($request, $this->getTemplate().ZMSettings::get('zenmagick.mvc.templates.ext', '.tpl'));
    }

}

?>
