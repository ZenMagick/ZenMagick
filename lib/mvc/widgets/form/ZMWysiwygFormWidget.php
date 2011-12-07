<?php
/*
 * ZenMagick - Another PHP framework.
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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;

/**
 * A wysiwyg form widget.
 *
 * <p>Will act like the configured default wysiwyg widget.</p>
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.widgets.form
 */
class ZMWysiwygFormWidget extends ZMFormWidget {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get instance of the current editor.
     *
     * @param ZMRequest request The current request.
     * @return ZMTextAreaFormWidget A text editor widget.
     */
    protected function getCurrentEditor($request) {
        $user = $request->getUser();
        if (null == $user || null == ($editor = ZMAdminUserPrefs::instance()->getPrefForName($user->getId(), 'wysiwygEditor'))) {
            $editor = Runtime::getSettings()->get('apps.store.admin.defaultEditor', 'ZMTextAreaFormWidget');
        }

        if (null != ($obj = Beans::getBean($editor))) {
            return $obj;
        }

        return Beans::getBean('ZMTextAreaFormWidget');
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, $view) {
        // get actual widget used to render and prepare
        $editor = $this->getCurrentEditor($request);
        $editor->setTitle($this->getTitle());
        $editor->setDescription($this->getDescription());
        $editor->setEnabled($this->isEnabled());
        $editor->setName($this->getName());
        $editor->setValue($this->getValue());
        $editor->setEncode($this->isEncode());
        foreach ($this->getProperties() as $name => $value) {
            $editor->set($name, $value);
        }
        return $editor->render($request, $view);
    }

}
