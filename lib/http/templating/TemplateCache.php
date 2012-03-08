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
namespace zenmagick\http\templating;


/**
 * Template cache interface.
 *
 * <p>Implementations are free to cache individual templates (fetch calls) and their output.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface TemplateCache {

    /**
     * Check if the given template can be cached.
     *
     * @param string template The template name.
     * @return boolean <code>true</code> if the template can be cached, <code>false</code> if not.
     */
    public function eligible($template);

    /**
     * Get cached template.
     *
     * @param string template The template name.
     * @return string The cached content or <code>null</code>.
     */
    public function lookup($template);

    /**
     * Save template content.
     *
     * @param string template The template name.
     * @param string content The content.
     */
    public function save($template, $content);

}
