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
namespace zenmagick\plugins\examplePlugin;

use zenmagick\apps\store\plugins\Plugin;
use zenmagick\base\Runtime;

/**
 * Example plugin to illustrate a few key points of the ZenMagick plugin architecture.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ExamplePluginPlugin extends Plugin {

    /**
     * As event listener a class is also automatically registered as listener for zen-cart zco events.
     */
    public function onNotifyHeaderStartIndex($event) {
        echo sprintf(_zm("Start of Zen Cart's index page event callback in %s ...<br>"), $this->getName());
    }

    /**
     * Handle final content.
     */
    public function onFinaliseContent($event) {
        $content = $event->get('content');
        $request = $event->get('request');

        if ('index' == $request->getRequestId()) {
            $content = preg_replace('/<h1>(.*)<\/h1>/', '<h1>\\1'.sprintf(_zm('(modified by %s)'), $this->getName()).'</h1>', $content, 1);
            $event->set('content', $content);
        }
    }

}
