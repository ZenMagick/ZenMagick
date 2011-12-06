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

use zenmagick\base\Runtime;

use Symfony\Component\Yaml\Yaml;

/**
 * Abstract base class for <code>Locale</code> implementations.
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.locale
 */
abstract class ZMAbstractLocale extends \ZMObject implements \ZMLocale {
    private $locale_;
    private $name_;
    private $formats_;


    /**
     * Create new instance.
     */
    public function __construct() {
        $this->locale_ = null;
        $this->name_ = null;
        // absolute defaults for DateTime::format()
        $this->formats_ = array(
            'date' => array(
                'short' => 'd/m/Y',
                'long' => 'D, d M Y'
            ),
            'time' => array(
                'short' => 'H:i:s',
                'long' => 'H:i:s u'
            )
        );
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
    public function init($locale, $path=null) {
        $token = explode('_', $locale);
        if (false == setlocale(LC_ALL, $locale)) {
            // try first token
            setlocale(LC_ALL, $token[0]);
        }

        $this->locale_ = $locale;
        $this->name_ = $locale;

        if (null == $path) {
            $path = \ZMFileUtils::mkPath(Runtime::getApplicationPath(), 'locale', $locale);
            if (null == ($path = \ZMLocaleUtils::resolvePath($path, $locale))) {
                Runtime::getLogging()->debug('unable to resolve path for locale = "'.$locale.'"');
                return null;
            }
        }

        $yaml = array();
        $filename = \ZMFileUtils::mkPath($path, 'locale.yaml');
        if (file_exists($filename)) {
            $yaml = Yaml::parse(\ZMFileUtils::mkPath($path, 'locale.yaml'));
            if (is_array($yaml)) {
                if (array_key_exists('name', $yaml)) {
                    $this->name_ = $yaml['name'];
                }
                if (array_key_exists('formats', $yaml)) {
                    $this->formats_ = \ZMLangUtils::arrayMergeRecursive($this->formats_, $yaml['formats']);
                }
            }
        }

        return array($path, $yaml);
    }

    /**
     * {@inheritDoc}
     */
    public function getFormat($group, $type=null) {
        if (array_key_exists($group, $this->formats_)) {
            if (null == $type) {
                return $this->formats_[$group];
            } else if (array_key_exists($type, $this->formats_[$group])) {
                return $this->formats_[$group][$type];
            }
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setFormats($formats) {
        $this->formats_ = \ZMLangUtils::arrayMergeRecursive($this->formats_, $formats);
    }

}
