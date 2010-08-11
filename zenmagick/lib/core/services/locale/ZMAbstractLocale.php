<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Abstract base class for <code>Locale</code> implementations.
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.locale
 */
abstract class ZMAbstractLocale extends ZMObject implements ZMLocale {
    private $locale_;
    private $name_;
    private $formats_;


    /**
     * Create new instance.
     */
    public function __construct() {
        $this->locale_ = null;
        $this->name_ = null;
        // absolute defaults
        $this->formats_ = array('date' => array('short' => '%d/%m/%y', 'long' => '%d/%m/%y'), 'time' => array('short' => '%h:%M:%S', 'long' => '%h:%M:%S'));
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
    }


    /**
     * {@inheritDoc}
     */
    public function getCode() {
        return $this->locale_;
    }

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return $this->name_;
    }

    /**
     * {@inheritDoc}
     */
    public function init($locale) {
        $token = explode('_', $locale);
        if (false == setlocale(LC_ALL, $locale)) {
            // try first token
            setlocale(LC_ALL, $token[0]);
        }

        $this->locale_ = $locale;
        $this->name_ = $locale;

        $path = ZMFileUtils::mkPath(ZMRuntime::getApplicationPath(), 'locale', $locale);
        if (null == ($path = ZMLocaleUtils::resolvePath($path, $locale))) {
            ZMLogging::instance()->log('unable to resolve path for locale = "'.$locale.'"', ZMLogging::DEBUG);
            return null;
        }

        $yaml = ZMRuntime::yamlLoad(@file_get_contents(ZMFileUtils::mkPath($path, 'locale.yaml')));
        if (is_array($yaml)) {
            if (array_key_exists('name', $yaml)) {
                $this->name_ = $yaml['name'];
            }
            if (array_key_exists('formats', $yaml)) {
                $this->formats_ = ZMLangUtils::arrayMergeRecursive($this->formats_, $yaml['formats']);
            }
        }

        return $path;
    }

    /**
     * {@inheritDoc}
     *
     * <p>Since the underlying <code>locale.yaml</code> file has no fixed format it is possible to add
     * your own formats and types by just following the existing structures.</p>
     */
    public function getFormat($group, $type) {
        return $this->formats_[$group][$type];        
    }

}
