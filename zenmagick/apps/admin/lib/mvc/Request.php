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
 * Admin request wrapper.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.mvc
 */
class Request extends ZMRequest {

    /**
     * Create new instance.
     *
     * @param array parameter Optional request parameter; if <code>null</code>,
     *  <code>$_GET</code> and <code>$_POST</code> will be used.
     */
    function __construct($parameter=null) {
        parent::__construct($parameter);
        $this->setSession(ZMLoader::make('Session', null, 'zmAdmin'));
        if ('db' == ZMSettings::get('sessionPersistence')) {
            $this->getSession()->registerSessionHandler(ZMLoader::make('ZenCartSessionHandler'));
        }
    }


    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the selected language.
     *
     * <p>Determine the currently active language, with respect to potentially selected language from a dropdown in admin UI.</p>
     *
     * @return ZMLanguage The selected language.
     */
    public function getSelectedLanguage() {
        if (null == ($selectedLanguageId = $this->getParameter('languageId'))) {
            // fallback to session
            if (null == ($selectedLanguageId = $this->getSession()->getValue('languages_id'))) {
                //todo: this should be store language??
                $selectedLanguageId = 1;
            }
        }

        return ZMLanguages::instance()->getLanguageForId($selectedLanguageId);
    }

    /**
     * Deal with demo user.
     *
     * <p>Will create a message that the requested functionallity is not availale for demo users.</p>
     *
     * @return boolean <code>true</code> if the current user is a demo user.
     */
    public function handleDemo() {
        if ($this->getUser()->isDemo()) {
            ZMMessages::instance()->warn(_zm('Sorry, the action you tried to excute is not available to demo users'));
            return true; 
        }

        return false;
    }

    /**
     * Get the category path arry.
     *
     * @return array The current category path broken into an array of category ids.
     */
    public function getCategoryPathArray() {
        $path = $this->getParameter('cPath');
        $cPath = array();
        if (null !== $path) {
            $path = explode('_', $path);
            foreach ($path as $categoryId) {
                $categoryId = (int)$categoryId;
                if (!in_array($categoryId, $cPath)) {
                    $cPath[] = $categoryId;
                }
            }
        }
        return $cPath;
    }

}
