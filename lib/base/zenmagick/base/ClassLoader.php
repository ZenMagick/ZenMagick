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
namespace zenmagick\base;

/**
 * A PHP5.3 class loader.
 *
 * <p>Inspired by and based on:</p>
 * <ul>
 *  <li><a href="https://gist.github.com/221634">SplClassLoader</a></li>
 *  <li><a href="https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php"> Symfony UniversalClassLoader</a></li>
 *  <li><a href="https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/ClassLoader.php">Doctrine ClassLoader</a></li>
 * <ul>
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
 * @author DerManoMann
 * @package zenmagick.base
 */
class ClassLoader {
    private $namespaces;
    private $prefixes;
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
        $this->fileExtension = '.php';
        $this->namespaceSeparator = '\\';
        $this->addNamespaces($namespaces);
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
     * @param string path The path.
     */
    public function addNamespace($namespace, $path) {
        if (!is_array($path)) {
            $path = array($path);
        }
        if (!array_key_exists($namespace, $this->namespaces)) {
            $this->namespaces[$namespace] = array();
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
     * Resolve the given class/interface name to a file.
     *
     * @param string name The name of the class or interface to load.
     * return string A filename or <code>null</code>.
     */
    protected function resolveClass($name) {
        if ($this->namespaceSeparator === $name[0]) {
            $name = substr($name, 1);
        }

        if (false !== ($pos = strripos($name, $this->namespaceSeparator))) {
            // namespaced class name
            $namespace = substr($name, 0, $pos);
            foreach ($this->namespaces as $ns => $arr) {
                foreach ($arr as $path) {
                    if (0 === strpos($namespace, $ns)) {
                        $name = substr($name, $pos + 1);
                        $file = $path.DIRECTORY_SEPARATOR.str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $name).'.php';
                        if (file_exists($file)) {
                            return $file;
                        }
                    }
                }
            }
        } else {
            // PEAR-like class name
            foreach ($this->prefixes as $prefix => $path) {
                if (0 === strpos($name, $prefix)) {
                    $file = $path.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $name).'.php';
                    if (file_exists($file)) {
                        return $file;
                    }
                }
            }
        }

        return null;
    }

}
