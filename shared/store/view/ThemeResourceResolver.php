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
?>
<?php
namespace zenmagick\apps\store\view;

use zenmagick\base\Runtime;
use zenmagick\http\view\ResourceResolver;

/**
 * Theme resource resolver.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ThemeResourceResolver extends ResourceResolver {
    private $request;
    private $languageId;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->request = null;
        $this->languageId = Runtime::getSettings()->get('storeDefaultLanguageId');
    }


    /**
     * Set the request.
     *
     * @param ZMRequest request The request.
     */
    public function setRequest(ZMRequest $request) {
        $this->request = $request;
        $this->languageId = $request->getSession()->getLanguageId();
    }

    /**
     * Get the request.
     *
     * @return ZMRequest The request.
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Get a list of application template locations.
     *
     * @return array List of template locations.
     */
    protected function getApplicationTemplateLocations() {
        $locations = parent::getApplicationTemplateLocations();

        // available locale
        $localeCodes = array_reverse($this->container->get('localeService')->getValidLocaleCodes());

        foreach ($this->container->get('themeService')->getThemeChain($this->languageId) as $theme) {
            $locations[] = $theme->getContentDir();
            foreach ($localeCodes as $code) {
                $locations[] = $theme->getContentDir().'/locale/'.$code;
            }
        }

        return $locations;
    }

}
