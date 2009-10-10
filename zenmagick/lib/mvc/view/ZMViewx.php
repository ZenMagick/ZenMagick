<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * View interface to generate the response content.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.view
 * @version $Id$
 */
interface ZMViewx {

    /**
     * Add a variable to the template context.
     *
     * @param string name The variable name.
     * @param mixed value The value.
     */
    public function setVar($name, $value);

    /**
     * Add a list of variables to the template context.
     *
     * @param array vars The name/value pairs.
     */
    public function setVars($vars);

    /**
     * Get the template context.
     *
     * @return array The name/value pairs.
     */
    public function getVars();

    /**
     * Check if this view will generate valid content.
     *
     * <p>This is optional and implementations are free to just return <code>true</code>.</p>
     *
     * @return boolean <code>true</code> if this view is valid, <code>false</code> if not.
     */
    public function isValid();

    /**
     * Generate the template content.
     *
     * @param ZMRequest request The current request.
     * @return string The generated response.
     */
    public function generate($request);

    /**
     * Get the view id.
     *
     * @return string The view id.
     */
    public function getViewId();

    /**
     * Set the view id.
     *
     * @param string viewId The view id.
     */
    public function setViewId($viewId);

    /**
     * Get the template name.
     *
     * @return string The template name.
     */
    public function getTemplate();

    /**
     * Set the template name.
     *
     * @param string name The template name.
     */
    public function setTemplate($name);

    /**
     * Get the content type.
     *
     * @return string The content type.
     */
    public function getContentType();

    /**
     * Get the character encoding.
     *
     * @return string The encoding.
     */
    public function getEncoding();

}

?>
