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
 * It simply strips the zenmagick prefix so we can work inside a directory
 * not named zenmagick.
 *
 * @todo revaluate once the classmap generation pull request is merged
 *       <link>https://github.com/composer/composer/pull/811</link>
 */
spl_autoload_register(function ($class) {
    if (0 === strpos($class, 'zenmagick')) {
        $class = substr($class, 10);
        if (false !== $pos = strrpos($class, '\\')) {
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $classPath .= substr($class, $pos + 1).'.php';
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
require_once __DIR__.'/lib/zenmagick/base/locales/functions.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
