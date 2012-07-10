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
namespace zenmagick\http\widgets\form;

use zenmagick\http\view\TemplateView;

/**
 * A wysiwyg editor.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface WysiwygEditor {
    const EDITOR_CLASS = 'wysiwyg_editor';
    const NO_EDITOR_CLASS = 'no_editor';

    /**
     * Apply editor to the given element ids.
     *
     * @param zenmagick\http\Request request The current request.
     * @param TemplateView templateView The current view.
     * @param array idList List of element ids to convert as Wysiwyg editor; default is <code>null</code> for all on the page.
     * @return string Generated code or <code>null</code>.
     */
    public function apply($request, TemplateView $templateView, $idList=null);

}
