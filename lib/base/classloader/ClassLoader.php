<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
 *
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
* A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
* OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
* SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
* LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
* DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
* THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
* OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*
* This software consists of voluntary contributions made by many individuals
* and is licensed under the LGPL. For more information, see
* <http://www.gnu.org/licenses/lgpl.html>.
*/
?>
<?php
namespace zenmagick\base\classloader;

/**
 * A PHP5.3 class loader.
 *
 * <p>Inspired by and based on:</p>
 * <ul>
 *  <li><a href="https://gist.github.com/221634">SplClassLoader</a></li>
 *  <li><a href="https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php"> Symfony UniversalClassLoader</a></li>
 *  <li><a href="https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/ClassLoader.php">Doctrine ClassLoader</a></li>
 * </ul>
 *
 * <p>The default namespace separator is '\\'.</p>
 *
 * <p>The default filename extension is '.php'.</p>
 *
 * <p>The default behaviour of registering class loader instances on the SPL stack is to prepend. That means in cases where a class with the same name and namespace
 * is available via multiple loader instances, the loader registered last will be used to resolve the class.</p>
 *
 * <p>A namespace path can be either a string (single path) or array of strings (mulitple path). That means a class loader may have multiple locations for a single
 * namespace.</p>
 *
 * <p>It is able to load classes that use either:</p>
 *
 * <ul>
 *  <li>The technical interoperability standards for PHP 5.3 namespaces and class names (http://groups.google.com/group/php-standards/web/psr-0-final-proposal);</li>
 *  <li>The PEAR naming convention for classes (http://pear.php.net/).</li>
 * </ul>
 *
 * <p>Classes from a sub-namespace or a sub-hierarchy of PEAR classes can be looked for in a list of locations to ease the vendoring of a sub-set of
 * classes for large projects.</p>
 *
 * <p>Example usage:</p>
 *
 * <code><pre>
 *
 * $loader = new ClassLoader();
 *
 * // register classes with namespaces
 * $loader->addNamespaces(array(
 *   'Symfony\Component' => __DIR__.'/component',
 *   'Symfony' => __DIR__.'/framework'
 * ));
 *
 * // register a library using the PEAR naming convention
 * $loader->addPrefixes(array(
 *   'Swift_' => __DIR__.'/Swift'
 * ));
 *
 * // activate the autoloader
 * $loader->register();
 * </pre></code>
 *
 * <p>In this example, if you try to use a class in the Symfony\Component namespace or one of its children (Symfony\Component\Console for instance),
 * the autoloader will first look for the class under the component/ directory, and it will then fallback to the framework/ directory if not
 * found before giving up.</p>
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.org>
 * @author DerManoMann <mano@zenmagick.org>
 */
class ClassLoader {
    private $namespaces;
    private $prefixes;
    private $defaults;
    private $fileExtension;
    private $namespaceSeparator;


    /**
     * Create a new class loader.
     *
     * @param array A map of namespace =&gt; path pair; default is an empty array.
     */
    public function __construct(array $namespaces=array()) {
        $this->namespaces = array();
        $this->prefixes = array();
        $this->defaults = array();
        $this->fileExtension = '.php';
        $this->namespaceSeparator = '\\';
        $this->addNamespaces($namespaces);
    }


    /**
     * Add mappings from a <em>classloader.ini</em> file.
     *
     * <p>All mappings in the <em>.ini</em> file are relative to the location of the <em>.ini</em> file.</p>
     *
     * <p>Example file:</p>
     * <code><pre>
     * [namespaces]
     * Doctrine\ORM = doctrine/lib
     * [prefixes]
     * Swift_ = swiftmailer/lib/classes
     * [default]
     * a = classes
     * b = other-classes
     * </pre></code>
     *
     * <p>To map the folder containing the <em>.ini</em> file, leave the path empty.</p>
     *
     * @param string path Path to a directory (assuming <em>classloader.ini</em>), or ini file.
     */
    public function addConfig($path) {
        // optional phar
        $phar = $path.'/'.basename($path).'.phar';
        $usePhar = file_exists($phar);
        if (is_dir($path)) {
            $ini = null;
            if ($usePhar) {
                $ini = 'phar://'.$phar.'/classloader.ini';
            }
            if (!file_exists($ini)) {
                // fallback
                $ini = $path.'/'.'classloader.ini';
            }
        } else {
            $ini = $path;
        }
        if (!empty($ini) && file_exists($ini) && is_file($ini)) {
            $baseDir = dirname($ini);
            $mappings = parse_ini_file($ini, true);
            if (array_key_exists('namespaces', $mappings)) {
                foreach ($mappings['namespaces'] as $namespace => $folder) {
                    $nsoff = null;
                    if (false !== ($at = strpos($folder, '@'))) {
                        $nsoff = substr($folder, $at+1);
                        $folder = substr($folder, 0, $at);
                    }
                    $nspath = $usePhar ? 'phar://'.$phar.'/'.str_replace('\\', '/', $folder) : (($baseDir.'/'.$folder).((!empty($nsoff) && false !== $at) ? '@'.$nsoff: ''));
                    $this->addNamespace($namespace, $nspath);
                }
            }
            if (array_key_exists('prefixes', $mappings)) {
                foreach ($mappings['prefixes'] as $prefix => $folder) {
                    $pxpath = $usePhar ? 'phar://'.$phar.'/'.str_replace('\\', '/', $folder) : ($baseDir.'/'.$folder);
                    $this->addPrefix($prefix, $pxpath);
                }
            }
            if (array_key_exists('default', $mappings)) {
                foreach ($mappings['default'] as $folder) {
                    $pxpath = $usePhar ? 'phar://'.$phar.'/'.$folder : ($baseDir.'/'.$folder);
                    $this->addPath($pxpath);
                }
            }
            if (array_key_exists('static', $mappings)) {
                foreach ($mappings['static'] as $class => $methods) {
                    if (class_exists($class)) {
                        foreach (explode(',', $methods) as $method) {
                            $class::$method();
                        }
                    }
                }
            }
        }
    }

    /**
     * Add a path to scan for default namespace classes.
     *
     * @param string path The path.
     * @param string name Optional name to register a single class for name; default is <code>null</code>.
     */
    public function addPath($path, $name=null) {
        if (file_exists($path)) {
            if (is_dir($path)) {
                $extList = array('.class.php', '.php');
                // scan and add individual files/classes
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $filename => $fileInfo) {
                    foreach ($extList as $ext) {
                        if ($fileInfo->isFile() && $ext == substr($fileInfo->getFilename(), -strlen($ext))) {
                            $this->defaults[str_replace($ext, '', $fileInfo->getFilename())] = $fileInfo->getPathname();
                            break;
                        }
                    }
                }
            } else if (null != $name) {
                $this->defaults[$name] = $path;
            }
        }
    }

    /**
     * Add a list of namespaces.
     *
     * @param array A map of namespace =&gt; path pairs.
     */
    public function addNamespaces(array $namespaces) {
        foreach ($namespaces as $namespace => $path) {
            $this->addNamespace($namespace, $path);
        }
    }

    /**
     * Add a single namespace.
     *
     * @param string namespace The namespace.
     * @param mixed path The path.
     */
    public function addNamespace($namespace, $path) {
        if (!is_array($path)) {
            $path = array($path);
        }
        if (!array_key_exists($namespace, $this->namespaces)) {
            $this->namespaces[$namespace] = array();
        }
        // handle @
        foreach ($path as $ii => $p) {
            $nsoff = null;
            if (false !== ($at = strpos($p, '@'))) {
                $nsoff = substr($p, $at+1);
                $p = substr($p, 0, $at);
            }
            $path[$ii] = array($p, $nsoff);
        }
        $this->namespaces[$namespace] = array_merge($this->namespaces[$namespace], $path);
    }

    /**
     * Get all configured namespaces for this loader.
     *
     * @return array A map of namespace =&gt; path pairs.
     */
    public function getNamespaces() {
        return $this->namespaces;
    }

    /**
     * Get all configured default namespace classes.
     *
     * @return array A map of class =&gt; path pairs.
     */
    public function getDefaults() {
        return $this->defaults;
    }

    /**
     * Add a list of non namespaced classes.
     *
     * @param array defaults A map of class =&gt; path pairs.
     */
    public function addDefaults($defaults) {
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    /**
     * Add a single non namespaced class.
     *
     * @param string class The class name.
     * @param string path The path.
     */
    public function addDefault($class, $path) {
        $this->defaults[$class] = $path;
    }

    /**
     * Add a list of PEAR packages.
     *
     * @param array prefixes A map of prefix =&gt; path pairs.
     */
    public function addPrefixes(array $prefixes) {
        $this->prefixes = array_merge($this->prefixes, $prefixes);
    }

    /**
     * Add a single PEAR package.
     *
     * @param string prefix The PEAR prefix.
     * @param string path The path.
     */
    public function addPrefix($prefix, $path) {
        $this->prefixes[$prefix] = $path;
    }

    /**
     * Get all configured PEAR locations for this loader.
     *
     * @return array A map of prefix =&gt; path pairs.
     */
    public function getPrefixes() {
        return $this->prefixes;
    }

    /**
     * Sets the namespace separator used by this class loader.
     *
     * @param char sep The separator to use.
     */
    public function setNamespaceSeparator($sep) {
        $this->namespaceSeparator = $sep;
    }

    /**
     * Gets the namespace separator used by this class loader.
     *
     * @return char The namespace separator.
     */
    public function getNamespaceSeparator() {
        return $this->namespaceSeparator;
    }

    /**
     * Sets the file extension used by this class loader.
     *
     * @param string fileExtension The file extension incl. the dot ('.').
     */
    public function setFileExtension($fileExtension) {
        $this->fileExtension = $fileExtension;
    }

    /**
     * Gets the file extension used by this class loader.
     *
     * @return string The file extension incl. the dot ('.').
     */
    public function getFileExtension() {
        return $this->fileExtension;
    }

    /**
     * Registers this class loader on the SPL autoload stack.
     *
     * @param boolean prepend The SPL stack prepend flag; default is <code>true</code>.
     */
    public function register($prepend=true) {
        spl_autoload_register(array($this, 'loadClass'), false, $prepend);
    }

    /**
     * Removes this class loader from the SPL autoload stack.
     */
    public function unregister() {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param string name The name of the class or interface to load.
     * @return boolean <code>true</code> if the class/interface has been successfully loaded, <code>false</code> otherwise.
     */
    public function loadClass($name) {
        if (null != ($file = $this->resolveClass($name))) {
            require $file;
            return true;
        }

        return false;
    }

    /**
     * Asks this class loader whether it can resolve/load the class/interface with the given name.
     *
     * @param string name The fully-qualified name of the class or interface.
     * @return boolean <code>true</code> if this class loader can resolve the class, <code>false</code> otherwise.
     */
    public function canResolve($name) {
        return null != $this->resolveClass($name);
    }

    /**
     * Checks whether a class with a given name exists. A class "exists" if it is either
     * already defined in the current request or if there is an autoloader on the SPL
     * autoload stack that is a) responsible for the class in question and b) is able to
     * load a class file in which the class definition resides.
     *
     * If the class is not already defined, each autoloader in the SPL autoload stack
     * is asked whether it is able to tell if the class exists. If the autoloader is
     * a <tt>ClassLoader</tt>, {@link canLoadClass} is used, otherwise the autoload
     * function of the autoloader is invoked and expected to return a value that
     * evaluates to TRUE if the class (file) exists. As soon as one autoloader reports
     * that the class exists, TRUE is returned.
     *
     * Note that, depending on what kinds of autoloaders are installed on the SPL
     * autoload stack, the class (file) might already be loaded as a result of checking
     * for its existence. This is not the case with a <tt>ClassLoader</tt>, who separates
     * these responsibilities.
     *
     * @param string name The fully-qualified name of the class.
     * @param boolean force Optional flag to force a check even if the class/interface already exists; default is <code>false</code>.
     * @return boolean <code>true</code> if the class exists as per the definition given above, <code>false</code> otherwise.
     */
    public static function classExists($name, $force=false) {
        if (!$force && (class_exists($name, false) || interface_exists($name, false))) {
            return true;
        }

        foreach (spl_autoload_functions() as $loader) {
            if (is_array($loader)) { // array(???, ???)
                if (is_object($loader[0])) {
                    if ($loader[0] instanceof ClassLoader) { // array($obj, 'methodName')
                        if ($loader[0]->canResolve($name)) {
                            return true;
                        }
                    } else if ($loader[0]->{$loader[1]}($name)) {
                        return true;
                    }
                } else if ($loader[0]::$loader[1]($name)) { // array('ClassName', 'methodName')
                    return true;
                }
            } else if ($loader instanceof \Closure) { // function($name) {..}
                if ($loader($name)) {
                    return true;
                }
            } else if (is_string($loader) && $loader($name)) { // "MyClass::loadClass"
                return true;
            }
        }

        return false;
    }

    /**
     * Create a normalized class names based on a name.
     *
     * <p>This is pretty much following Java conventions.</p>
     *
     * @param string name The name (file name, etc).
     * @return string A corresponding class name.
     */
    public static function className($name) {
        // strip potential file extension and dir
        $classname = str_replace('.php', '', basename($name));
        // '_' == word boundary
        $classname = str_replace(array('_', '-'), ' ', $classname);
        // capitalise words
        $classname = ucwords($classname);
        // cuddle together :)
        $classname = str_replace(' ', '', $classname);
        return $classname;
    }

    /**
     * Resolve the given class/interface name to a file.
     *
     * @param string name The name of the class or interface to load.
     * @return string A filename or <code>null</code>.
     */
    protected function resolveClass($name) {
        if ($this->namespaceSeparator === $name[0]) {
            $name = substr($name, 1);
        }

        if (false !== ($pos = strripos($name, $this->namespaceSeparator))) {
            // get namespace
            $namespace = substr($name, 0, $pos);
            // strip namespace from name
            $class = substr($name, $pos + 1);
            foreach ($this->namespaces as $ns => $arr) {
                foreach ($arr as $path) {
                    if (0 === strpos($namespace, $ns)) {
                        $finalns = $namespace;
                        if ($path[1]) {
                            // adjust
                            $finalns = substr($finalns, strlen($path[1])+1, strlen($finalns));
                        }
                        $sep = 0 === strpos($path[0], 'phar://') ? '/' : DIRECTORY_SEPARATOR;
                        $file = $path[0].$sep.str_replace($this->namespaceSeparator, $sep, $finalns).$sep.str_replace('_', $sep, $class).'.php';
                        if (file_exists($file)) {
                            return $file;
                        }
                    }
                }
            }
        } else {
            // try PEAR-like class name
            foreach ($this->prefixes as $prefix => $path) {
                if (0 === strpos($name, $prefix)) {
                    $file = $path.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $name).'.php';
                    if (file_exists($file)) {
                        return $file;
                    }
                }
            }

            // try default namespace
            foreach ($this->defaults as $clazz => $file) {
                if ($clazz == $name) {
                    return $file;
                }
            }
        }

        return null;
    }

}
