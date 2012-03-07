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
namespace zenmagick\http\view;

/**
 * A view.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface View {
    const TEMPLATE = 'template:';
    const RESOURCE = 'resource:';

    /**
     * Generate the view.
     *
     * @param ZMRequest request The current request.
     * @param string template Optional template override; default is <code>null</code>.
     * @param array variables Optional additional template variables; default is an empty array.
     * @return string The contents.
     */
    public function generate($request, $template=null, $variables=array());

    /**
     * Check if this view is valid.
     *
     * @return boolean <code>true</code> if the view is valid.
     */
    public function isValid();

}
