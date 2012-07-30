<?php
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/vendor/autoload.php';

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';
    $loader->add('', __DIR__.'/vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs');
}
/**
 * ZenMagick modified PSR-0 class loader.
 *
 * This is to support classes grouped under the zenmagick
 * namespace.
 * Example:
 *   zenmagick\plugins\foo\controller\FooController => zmroot/plugins/foo/lib/controller/FooController
 *   where the basename of zmroot is not zenmagick.
 *
 * @todo fix up the melded app\store namespace
 * @todo remove regexes
 * @todo probably remove plugin and theme lib directories
 * @todo maybe remove app/appName/lib too?
 * @todo revaluate once the classmap generation pull request is merged
 *       <link>https://github.com/composer/composer/pull/811</link>
 */
spl_autoload_register(function ($class) {
    if (0 === strpos($class, 'zenmagick')) {
        $class = substr($class, 10);
        if (false !== $pos = strrpos($class, '\\')) {
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
            $classPath .= $className.'.php';
            $fix = array( // @todo having the melded apps\store makes things difficult.
                '|^apps/sample/|' => 'apps/sample/lib/',
                '|^apps/store-installer/|' => 'apps/store-installer/lib/',
                '|^apps/store/|' => 'shared/store/',
            );
            $classPath = preg_replace(array_keys($fix), array_values($fix), $classPath);
            $file = __DIR__.DIRECTORY_SEPARATOR.$classPath;
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
});
/**
 * Preload Locales class to make translation functions available.
 */
class_exists('zenmagick\base\locales\handler\pomo\POMO');
class_exists('zenmagick\base\locales\Locales');

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
