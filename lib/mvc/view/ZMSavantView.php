<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * <p>This class also introduced support for layouts.</p>
 *
 * <p>The default <code>viewDir</code> value is <em>views/</em>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.view
 */
class ZMSavantView extends ZMView {
    private $savant_;
    private $config_;
    private $layout_;
    private $viewDir_;
    private $filters_;
    private $utils_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->savant_ = null;
        $this->config_ = array();
        $this->layout_ = array();
        $this->filters_ = null;
        $this->setViewDir('views/');
        
        // special view bits
        $viewUtils = $this->getViewUtils();
        $this->setVar('resources', $viewUtils);

    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the views dir.
     *
     * @return string The views folder name, relative to the content folder.
     */
    public function getViewDir() {
        return $this->viewDir_;
    }

    /**
     * Set the views dir.
     *
     * @param string viewDir The views folder name, relative to the content folder.
     */
    public function setViewDir($viewDir) {
        $this->viewDir_ = $viewDir;
    }

    /**
     * Get the configured filters.
     *
     * @return string A list of filter classes or <code>null</code>.
     */
    public function getFilters() {
        return $this->filters_;
    }

    /**
     * Set the filters.
     *
     * @param string filters Comma separated list of filter classes/bean definitions.
     */
    public function setFilters($filters) {
        $this->filters_ = $filters;
    }

    /**
     * Get a list of filter instances.
     *
     * @return array List of filter objects.
     */
    protected function getFilterList() {
        if (null == $this->filters_) {
            return array();
        }
        $list = array();
        foreach (explode(',', $this->filters_) as $class) {
            if (null != ($filter = ZMBeanUtils::getBean(trim($class)))) {
                $list[] = array($filter, 'filter');
            }
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplate() {
        return $this->getViewDir().parent::getTemplate();
    }

    /**
     * Get the array of locations to search for templates.
     *
     * @param ZMRequest request The current request.
     * @return array List of locations to look for templates.
     */
    public function getTemplatePath($request) {
        $path = array();

        // add plugins as well
        foreach (explode(',', ZMSettings::get('zenmagick.core.plugins.groups')) as $group) {
            foreach (ZMPlugins::instance()->getPluginsForGroup($group, 0) as $plugin) {
                $path[] = $plugin->getPluginDirectory().'content'.DIRECTORY_SEPARATOR;
            }
        }

        $path[] = $request->getTemplatePath();

        return $path;
    }

    /**
     * Get the array of locations to search for resources.
     *
     * <p>This default implementation will just return <code>getTemplatePath($request)</code>.</p>
     *
     * @param ZMRequest request The current request.
     * @return array List of locations to look for resources.
     */
    public function getResourcePath($request) {
        $path = array();

        // available locale
        $localeCodes = array_reverse(ZMLocales::instance()->getValidLocaleCodes());

        // add plugins as well
        foreach (explode(',', ZMSettings::get('zenmagick.core.plugins.groups')) as $group) {
            foreach (ZMPlugins::instance()->getPluginsForGroup($group, ZMSettings::get('zenmagick.core.plugins.context', 0)) as $plugin) {
                $ppath = $plugin->getPluginDirectory().'content'.DIRECTORY_SEPARATOR;
                $path[] = $ppath;
                foreach ($localeCodes as $code) {
                    $path[] = ZMFileUtils::mkpath($ppath, 'locale', $code);
                }
            }
        }

        $path[] = $request->getWebPath();

        // add path for locale specific resources
        foreach ($localeCodes as $code) {
            $path[] = ZMFileUtils::mkpath($request->getWebPath(), 'locale', $code);
        }

        return $path;
    }

    /**
     * Set savant specific configuration options.
     *
     * @param mixed config Anything that can be converted into a map.
     */
    public function setConfig($config) {
        $this->config_ = ZMLangUtils::toArray($config);
        foreach ($this->config_ as $key => $value) {
            if (('compiler' == $key || 'cache' == $key) && !is_object($value)) {
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
    public function getSavant($request) {
        if (null === $this->savant_) {
            $config = array();
            $config['autoload'] = true;
            $config['exceptions'] = true;
            $config['extract'] = true;
            $config['template_path'] = $this->getTemplatePath($request);
            $config['resource_path'] = $this->getResourcePath($request);
            $config = array_merge($config, $this->config_);
            $this->savant_ = ZMLoader::make('Savant', $config);
            // config doesn't support multiple filter
            foreach ($this->getFilterList() as $filter) {
                $this->savant_->addFilters($filter);
            }
        }

        return $this->savant_;
    }

    /**
     * Set the layout name.
     *
     * @param string layout The layout name.
     */
    public function setLayout($layout) {
        $this->layout_ = $layout;
    }

    /**
     * Get the layout name.
     *
     * @return string The layout name.
     */
    public function getLayout() {
        return $this->layout_;
    }

    /**
     * {@inheritDoc}
     */
    public function getViewUtils() {
        if (null == $this->utils_) {
            $this->utils_ = ZMLoader::make('ViewUtils', $this);
        }
        return $this->utils_;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request) {
        $savant = $this->getSavant($request);

        // assign view to toolbox tools
        foreach ($this->getVars() as $var) {
            if ($var instanceof ZMToolboxTool) {
                $var->setView($this);
            }
        }

        // load template...
        $template = null;
        try {
            $this->setVar('view', $this);
            if (!ZMLangUtils::isEmpty($this->getLayout())) {
                $template = $this->getLayout();
                $view = $this->getTemplate().ZMSettings::get('zenmagick.mvc.templates.ext', '.php');
                $this->setVar('viewTemplate', $view);
            } else {
                $template = $this->getTemplate();
            }

            // put all vars into local scope
            $savant->assign($this->getVars());

            $template .= ZMSettings::get('zenmagick.mvc.templates.ext', '.php');
            $contents = $savant->fetch($template);
            $viewUtils = $this->getViewUtils();
            if (null !== ($resources = $viewUtils->getResourceContents())) {
                // apply resources...
                $contents = preg_replace('/<\/head>/', $resources['header'] . '</head>', $contents, 1);
                $contents = preg_replace('/<\/body>/', $resources['footer'] . '</body>', $contents, 1);
            }
            return $contents;
        } catch (Exception $e) {
            ZMLogging::instance()->dump($e, 'failed to fetch template: '.$template, ZMLogging::ERROR);
            throw new ZMException('failed to fetch template: '.$template, 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($request, $template, $vars=array()) {
        $savant = $this->getSavant($request);

        // put all general vars into local scope
        $savant->assign($this->getVars());
        // also assign custom vars
        $savant->assign($vars);

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
    public function exists($request, $filename, $type=ZMView::TEMPLATE) {
        return $this->getSavant($request)->exists($filename, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function asUrl($request, $filename, $type=ZMView::TEMPLATE) {
        return $this->getSavant($request)->asUrl($filename, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function path($filename, $type=ZMView::TEMPLATE) {
        return $this->getSavant($request)->path($filename, $type);
    }

}
