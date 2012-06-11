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
namespace zenmagick\http\request;


/**
 * Interface for classes that want to rewrite urls.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface UrlRewriter {

    /**
     * Decode a given request if this rewriter can decode it.
     *
     * @param Request request The current request.
     * @return boolean <code>true</code> if, and only if, the request was decoded.
     */
    public function decode($request);

    /**
     * Generate a SEO url for the given parameter.
     *
     * <p>The default implementation of <code>Request::url()</code> will set the following args:</p>
     * <ul>
     *  <li><strong>requestId</strong>: The request id.</li>
     *  <li><strong>params</strong>: Query string type URL parameter(s).</li>
     *  <li><strong>secure</strong>: Boolean flag as to whether the URL needs to be secure or not.</li>
     * </ul>
     *
     * @param Request request The current request.
     * @param array args Optional parameter.
     * @return string Either a rewritten usable URL, or <code>null</code>.
     */
    public function rewrite($request, $args);

}
