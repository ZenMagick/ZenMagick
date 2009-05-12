<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Scaffolding.
 *
 * @package org.zenmagick.plugins.zm_scaffold
 * @author DerManoMann
 * @version $Id$
 */
class zm_scaffold extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Scaffold', 'Provides a generic scaffolding controller and environment.');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        if (null !== ($path = $this->getPathInfo())) {
            $mappings = $this->getMappings();
            if (array_key_exists($path[0], $mappings)) {
                // get configured pages and see if we need to do anything
                switch (count($path)) {
                case 3:
                    ZMRequest::setParameter('key', $path[2]);
                case 2:
                    ZMRequest::setParameter('action', $path[1]);
                case 1:
                    ZMRequest::setParameter(ZM_PAGE_KEY, $path[0]);
                    break;
                }

                // set mappings for CRUD
                $mapping = $mappings[$path[0]];
                $urlMapper = ZMUrlMapper::instance();
                foreach (array('create', 'edit', 'update', 'delete') as $crud) {
                    $urlMapper->setMappingInfo($mapping['page'], array(
                        'viewId' => $crud,
                        'view' => $crud,
                        'viewDefinition' => 'PageView', 
                        'controllerDefinition' => $mapping['controllerDefinition'].'#table='.$mapping['table'].'&method='.$crud, 
                        'formId' => $mapping['formId']
                    ));
                }
                // success mapping
                $urlMapper->setMappingInfo($mapping['page'], array(
                    'viewId' => 'success',
                    'view' => 'index',
                    'viewDefinition' => 'RedirectView', 
                    'controllerDefinition' => $mapping['controllerDefinition'].'#table='.$mapping['table'].'&method=index' 
                ));
                // default index
                $urlMapper->setMappingInfo($mapping['page'], array(
                    'view' => 'index',
                    'viewDefinition' => 'PageView', 
                    'controllerDefinition' => $mapping['controllerDefinition'].'#table='.$mapping['table'].'&method=index' 
                ));
            }
        }
    }

    /**
     * Get the path array.
     *
     * <p>This will look at the current request and try to figure out if a rewrite url is used.</p>
     *
     * @return array The path array or <code>null</code>.
     */
    protected function getPathInfo() {
        $rewriteUrl = null;
        if (array_key_exists('rewriteUrl', $_GET)) {
            // rewritten from mod_rewrite
            $rewriteUrl = $_GET['rewriteUrl'];
        } else if (false !== strpos($_SERVER['REQUEST_URI'], 'index.php/') || array_key_exists('rewriteUrl', $_GET)) {
            // path like URL; example: index.php/page/action/key
            $rewriteUrl =  preg_replace('/.*index.php\/(.*)/', '\1', $_SERVER['REQUEST_URI']);
        }
        if (null !== $rewriteUrl) {
            return explode('/', $rewriteUrl);
        }

        return null;
    }

    /**
     * Get the configured mappings.
     *
     * @return array Associative map of page =&gt; table.
     */
    protected function getMappings() {
        $mappings = array();
        foreach (explode(',', ZMSettings::get('plugins.zm_scaffold.pages')) as $mapping) {
            if (!ZMTools::isEmpty($mapping)) {
                $token = explode(':', $mapping);
                switch (count($token)) {
                case 4:
                    $controllerDefinition = $token[3];
                    $formId = $token[2];
                    $table = $token[1];
                    $page = $token[0];
                    break;
                case 3:
                    $controllerDefinition = 'ScaffoldController';
                    $formId = $token[2];
                    $table = $token[1];
                    $page = $token[0];
                    break;
                case 2:
                    $controllerDefinition = 'ScaffoldController';
                    $formId = null;
                    $table = $token[1];
                    $page = $token[0];
                    break;
                case 1:
                    $controllerDefinition = 'ScaffoldController';
                    $formId = null;
                    $table = $token[0];
                    $page = $token[0];
                    break;
                }
                $mappings[$page] = array('page' => $page, 'table' => $table, 'formId' => $formId, 'controllerDefinition' => $controllerDefinition);
            }
        }

        return $mappings;
    }

}

?>
