<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\base\utils;

use zenmagick\base\ClassLoader;
use zenmagick\base\Runtime;

use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * Static init code.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.base.utils
 */
class StaticInit {

    /**
     * Init swift auto loader.
     */
    public static function initSwiftAutoLoader() {
        try {
            $rclass = new \ReflectionClass('Swift');
            \Swift::$initPath = realpath(dirname($rclass->getFilename()).'/../swift_init.php');
            // ZenMagick class loader append, so the last one wins
            spl_autoload_register(array('Swift', 'autoload'), false, true);
        } catch (Exception $e) {
            Runtime::getLogging()->error(sprintf('swift init failed %s', $e->getMessage()));
        }
    }

    /**
     * Init Doctrine annotations registry.
     */
    public static function initDoctrineAnnotationRegistry() {
        /**
         * @link http://www.doctrine-project.org/docs/common/2.1/en/reference/annotations.html Doctrine Annotations
         * Notes: Annotations requires a silent autoloader.
         * We must manually register the Doctrine ORM specific annotations.
         */
        AnnotationRegistry::registerLoader(function($class) {
            ClassLoader::classExists($class);
            return class_exists($class, false);
        });
        // TODO: do we really need this?
        //   all it does is to require a file and that will break if vendors is in a .phar
        //   if we need to foce loading the DoctrineAnnotaions class we could instead do this:
        //class_exists('Doctrine\ORM\Mapping\DoctrineAnnotations');

        //AnnotationRegistry::registerFile(ZM_BASE_PATH . '/vendor/doctrine/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
    }

}
