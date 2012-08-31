<?php
/*
 * ZenMagick - Another PHP framework.
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

/**
 * Simplified ZenCart langauge class.
 */
class language {
    public $catalog_languages = array(); // this tends to be accessed directly, so it must stay public

    function __construct() {
        $languages = ZenMagick\Base\Runtime::getContainer()->get('languageService')->getLanguages();
        foreach ($languages as $language) {
            $this->catalog_languages[$language->getCode()] =
                array('id' => $language->getId(),
                      'name' => $language->getName(),
                      'image' => $language->getImage(),
                      'code' => $language->getCode(),
                      'directory' => $language->getDirectory());
        }
    }
}
