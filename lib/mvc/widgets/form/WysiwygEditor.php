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

/**
 * A wysiwyg editor.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.widgets.form
 */
interface WysiwygEditor {

    /**
     * Apply editor to the given element ids.
     *
     * @param ZMRequest request The current request.
     * @param ZMView view The current view.
     * @param array idList List of element ids to convert as Wysiwyg editor; default is <code>null</code> for all on the page.
     * @return string Generated code or <code>null</code>.
     */
    public function apply($request, $view, $idList=null);

}
