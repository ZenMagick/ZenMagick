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
namespace zenmagick\plugins\minify;

use Plugin;
use zenmagick\base\Runtime;

/**
 * Plugin to use minify.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MinifyPlugin extends Plugin {

    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('URL Limit', 'urlLimit', '900', 'Length limit for minify URLs.');

        $this->addConfigValue('Short Urls', "shortUrls", 'true', 'Generate short urls that rely on URL rewriting.',
            'widget@booleanFormWidget#name=shortUrls&default=true&label=Use short URLs&style=checkbox');

        // create minify cache dir
        $this->container->get('filesystem')->mkdir(dirname(Runtime::getInstallationPath()).'/cache/zenmagick/minify', 0755);
    }

    //TODO: install options: =f only, create dynamica groups, enable js, enable/disable css, PHP support: off, simple, ZenMagick Context (controller)
    // minify logger
    // controller for ZM context?
    // how to allow simple PHP?
    // delegate minify config to plugin? generate static config via plugin admin page?
}
