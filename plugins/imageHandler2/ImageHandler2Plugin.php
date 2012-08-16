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
namespace zenmagick\plugins\imageHandler2;

use zenmagick\apps\store\plugins\Plugin;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\http\view\ResourceManager;

/**
 * Plugin to enable support for ImageHandler2 in ZenMagick.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ImageHandler2Plugin extends Plugin {

    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        $this->addConfigValue('Disable IH img attributes', 'disableIH2Attributes', false, 'Disable IH2 showtrail/hidetrail mouseover handler and styles on img elements',
            'widget@booleanFormWidget#name=disableIH2Attributes&default=false&label=Disable&style=checkbox');
    }

    /**
     * Add resources.
     */
    public function onViewStart($event) {
        if (!Toolbox::asBoolean($this->get('disableIH2Attributes'))) {
            if (null != ($resources = $event->get('view')->getResourceManager())) {
                $resources->cssFile('ih2/style_imagehover.css');
                $resources->jsFile('ih2/jscript_imagehover.js', ResourceManager::HEADER);
            }
        }
    }

}
