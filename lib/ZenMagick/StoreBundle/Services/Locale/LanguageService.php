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
namespace ZenMagick\StoreBundle\Services\Locale;

use ZMRuntime;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * Languages service.
 *
 * @author DerManoMann
 */
class LanguageService extends ZMObject {
    private $languages;


    /**
     * Load languages.
     */
    protected function load() {
        $sql = "SELECT *
                FROM %table.languages%
                ORDER BY sort_order";
        $this->languages = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array(), 'languages', 'ZenMagick\StoreBundle\Entity\Locale\Language') as $language) {
            $this->languages[$language->getCode()] = $language;
        }
    }

    /**
     * Get all languages.
     *
     * @return array List of <code>Language</code> instances.
     */
    public function getLanguages() {
        if (null === $this->languages) {
            $this->load();
        }

        return $this->languages;
    }

    /**
     * Get language for the given code.
     *
     * @param string code The language code.
     * @return ZMLanguage A language or <code>null</code>.
     */
    public function getLanguageForCode($code) {
        if (null === $this->languages) {
            $this->load();
        }
        $locale = explode('_', $code);
        $code = $locale[0];
        return isset($this->languages[$code]) ? $this->languages[$code] : null;
    }

    /**
     * Get language for the given id.
     *
     * @param int id The language id.
     * @return ZMLanguage A language or <code>null</code>.
     */
    public function getLanguageForId($id) {
        if (null === $this->languages) {
            $this->load();
        }

        foreach ($this->languages as $language) {
            if ($language->getId() == $id) {
                return $language;
            }
        }

        return null;
    }

    /**
     * Get the default language.
     *
     * @return ZMLanguage The default language for this store.
     */
    public function getDefaultLanguage() {
        return Runtime::getContainer()->get('languageService')->getLanguageForId(Runtime::getSettings()->get('storeDefaultLanguageId'));
    }

}
