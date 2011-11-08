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


/**
 * A Savant(3) view with theme support and adjustment for theme folder structures (viewDir).
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.view
 */
class SavantView extends ZMSavantView {

    /**
     * {@inheritDoc}
     *
     * <p>The template path will contain each active plugin's base directory, the default theme's content
     * directory and the active theme's content directory.</p>
     */
    public function getTemplatePath($request) {
        $path = array();

        // available locale
        $localeCodes = array_reverse($this->container->get('localeService')->getValidLocaleCodes());

        // bundles
        foreach ($this->container->getParameterBag()->get('kernel.bundles') as $bundleName => $bundleClass) {
            $rclass = new ReflectionClass($bundleClass);
            $bundlePath = dirname($rclass->getFilename());
            $path[] = $bundlePath.'/Resources';
        }

        // add plugins as fallback fallback
        foreach ($this->container->get('pluginService')->getAllPlugins('storefront') as $plugin) {
            $ppath = $plugin->getPluginDirectory().'content'.DIRECTORY_SEPARATOR;
            $path[] = $ppath;
            foreach ($localeCodes as $code) {
                $path[] = ZMFileUtils::mkpath($ppath, 'locale', $code);
            }
        }

        // available locale
        $localeCodes = array_reverse($this->container->get('localeService')->getValidLocaleCodes());

        foreach ($this->container->get('themeService')->getThemeChain($request->getSession()->getLanguageId()) as $theme) {
            $path[] = $theme->getContentDir();
            foreach ($localeCodes as $code) {
                $path[] = ZMFileUtils::mkpath($theme->getContentDir(), 'locale', $code);
            }
        }

        return $path;
    }

}
