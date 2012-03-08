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
namespace zenmagick\apps\store\bundles\ZenCartBundle;

use zenmagick\base\classloader\ClassLoader;
use zenmagick\base\Runtime;

/**
 * Zencart class loader.
 *
 * @author DerManoMann
 */
class ZenCartClassLoader extends ClassLoader {
    private $baseDirectories;
    private $classFileMap;


    /**
     * {@inheritDoc}
     */
    public function __construct(array $namespaces=array()) {
        parent::__construct($namespaces);
        $this->baseDirectories = array(dirname(__FILE__).'/bridge/includes/classes', dirname(Runtime::getInstallationPath()).'/includes/classes');
        $this->classFileMap = array(
            // ZenMagick
            'base' => 'class.base',
            'httpClient' => 'http_client',
            'messageStack' => 'message_stack', // admin overrides storefront
            'navigationHistory' => 'navigation_history',
            'notifier' => 'class.notifier',
            'queryFactory' => 'db/mysql/query_factory',
            'queryFactoryResult' => 'db/mysql/query_factory',
            'shoppingCart' => 'shopping_cart',
            // ZenCart admin/storefront
            'breadcrumb' => 'breadcrumb',
            'category_tree' => 'category_tree',
            'language' => 'language',
            'products' => 'products',
            'sniffer' => 'sniffer',
            'splitPageResults' => 'split_page_results', // admin overrides storefront
            'template_func' => 'template_func',
            'PHPMailer' => 'class.phpmailer', // @todo remove legacy mailer support
            'SMTP' => 'class.smtp',
            // ZenCart Admin
            'box' => 'box',
            'objectInfo' => 'object_info',
            'tableBlock' => 'table_block',
            'upload' => 'upload',
        );
    }

    /**
     * Get the base directories used to find classes.
     *
     * @return array
     */
    public function getBaseDirectories() {
        return $this->baseDirectories;
    }

    /**
     * Set base directories to search for classes.
     *
     * @param array baseDirectories
     */
    public function setBaseDirectories($baseDirectories) {
        $this->baseDirectories = $baseDirectories;
    }

    /**
     * {@inheritDoc}
     */
    protected function resolveClass($name) {
        if (array_key_exists($name, $this->classFileMap)) {
            $name = $this->classFileMap[$name];
        }

        foreach ($this->baseDirectories as $dir) {
            $file = $dir.'/'.$name.'.php';
            if (file_exists($file)) {
                return $file;
            }
        }

        return null;
    }

}
