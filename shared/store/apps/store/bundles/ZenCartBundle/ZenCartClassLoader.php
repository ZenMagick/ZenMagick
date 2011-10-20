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
namespace apps\store\bundles\ZenCartBundle;

use zenmagick\base\ClassLoader;
use zenmagick\base\Runtime;

/**
 * Zencart class loader.
 *
 * @author DerManoMann
 * @package apps.store.bundles.ZenCartBundle
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
            'httpClient' => 'http_client',
            'messageStack' => 'message_stack',
            'navigationHistory' => 'navigation_history',
            'shoppingCart' => 'shopping_cart',
            'base' => 'class.base',
            'notifier' => 'class.notifier',
            'queryFactory' => 'db/mysql/query_factory'
        );
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
