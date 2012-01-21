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
namespace zenmagick\http\view;

use zenmagick\base\Beans;
use zenmagick\base\ZMException;

/**
 * Forward view.
 *
 * <p>This will forward the request to the given controller without a redirect.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ForwardView extends View {
    private $requestId_;


    /**
     * Create a new forward view.
     */
    public function __construct() {
        parent::__construct();
        $this->requestId_ = null;
    }

    /**
     * Get the request id of the redirect.
     *
     * <p>If not set, this will default to the template name (compatibility mode).</p>
     *
     * @return string The request id.
     */
    public function getRequestId() {
        return $this->requestId_;
    }

    /**
     * Set the request id of the redirect.
     *
     * @param string requestId The request id.
     */
    public function setRequestId($requestId) {
        $this->requestId_ = $requestId;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($template, $variables=array()) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function exists($file, $type=View::TEMPLATE) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function asUrl($file, $type=View::TEMPLATE) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function file2uri($filename) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function isValid() {
        $requestId = $this->getRequestId();
        return !empty($requestId);
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request, $template=null, $variables=array()) {
        // keep reference to original request
        $request->setParameter('rootRequestId', $request->getRequestId());
        // set forward id
        $request->setRequestId($this->getRequestId());
        // reset
        $request->setController(null);

        $this->container->get('dispatcher')->dispatch($request);
        exit;
    }

}
