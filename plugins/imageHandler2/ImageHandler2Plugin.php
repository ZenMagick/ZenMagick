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
namespace ZenMagick\plugins\imageHandler2;

use ZenMagick\apps\store\Plugins\Plugin;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Http\View\ResourceManager;

/**
 * Plugin to enable support for ImageHandler2 in ZenMagick.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ImageHandler2Plugin extends Plugin {

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
