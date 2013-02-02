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

namespace ZenMagick\Http;

use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

use ZenMagick\Base\Toolbox;

/**
 * A wrapper around Symfony 2's <code>Symfony\Component\HttpFoundation\Request</code>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Request extends HttpFoundationRequest
{
    /**
     * {@inheritDoc}
     *
     * @todo move custom checkbox handling out of here
     */
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->initialize($query, $request, $attributes, $cookies, $files, $server, $content);

        // @todo could move into custom parameter bag class?
        foreach (array($this->query, $this->request) as $parameterBag) {
            foreach ($parameterBag->keys() as $key) {
                if (0 === strpos($key, '_') && !$parameterBag->has(substr($key, 1))) {
                    $parameterBag->set(substr($key, 1), false);
                }
            }
        }
    }

    /**
     * Check if this request is an Ajax request.
     *
     * <p>This default implementation will check for a 'X-Requested-With' header. Subclasses are free to
     * extend and override this method for custom Ajax detecting.</p>
     *
     * @return boolean <code>true</code> if this request is considered an Ajax request.
     */
    public function isXmlHttpRequest()
    {
        $ajax = $this->getParameter('ajax', null);

        return $ajax != null ? Toolbox::asBoolean($ajax) : parent::isXmlHttpRequest();
    }

    /**
     * Get the user (if any) for authentication.
     *
     * @see ZenMagick\Http\Session\Session::getAccount()
     */
    public function getAccount()
    {
        return $this->getSession()->getAccount();
    }

    /**
     * Get the complete parameter map.
     *
     * GET and POST.
     * @todo change all users ?
     * @param boolean sanitize If <code>true</code>, sanitze value; default is <code>true</code>.
     * @return array Map of all request parameters
     */
    public function getParameterMap($sanitize=true)
    {
        $map = array();
        $params = array_unique(array_merge($this->request->keys(), $this->query->keys()));
        foreach ($params as $key) {
            $map[$key] = $this->getParameter($key, null, $sanitize);
        }

        return $map;
    }

    /**
     * Get the request id.
     *
     * <p>The request id is the main criteria for selecting the controller and view to process this
     * request.</p>
     *
     * @return string The request id of this request.
     */
    public function getRequestId()
    {
        return $this->attributes->get('_route');
    }

    /**
     * Set the request id.
     *
     * @param string requestId The new request id.
     */
    public function setRequestId($requestId)
    {
        $this->attributes->set('_route', $requestId);
    }

    /**
     * Generic access method for request parameter.
     *
     * <p>This method is evaluating both <code>GET</code> and <code>POST</code> parameter.</p>
     *
     * <p>There is a special case for when a parameter is not found, but _[name] is found. In this
     * case <code>false</code> is returned. This allows to handle checkboxes same as any other form element
     * by adding a hidden field _[name] with the original value.</p>
     *
     * @param string name The paramenter name.
     * @param mixed default An optional default parameter (if not provided, <code>null</code> is used).
     * @param boolean sanitize If <code>true</code>, sanitze value; default is <code>true</code>.
     * @return mixed The parameter value or the default value or <code>null</code>.
     */
    public function getParameter($name, $default=null, $sanitize=true)
    {
        // try GET, then POST
        // @todo we could also just rely on parent::get() as it searches these as well
        foreach (array('query', 'request', 'attributes') as $parameterBag) {
            if ($this->$parameterBag->has($name)) {
                return $sanitize ? self::sanitize($this->$parameterBag->get($name)) : $this->$parameterBag->get($name);
            }
        }

        return $default;
    }

    /**
     * Redirect to the given url.
     *
     * @param string url A fully qualified url.
     * @param int status Optional status; default is <em>302 - FOUND</em>.
     * @todo use RedirectResponse throughout.
     * @deprecated
     */
    public function redirect($url, $status=302)
    {
        $url = str_replace('&amp;', '&', $url);

        $this->getSession()->save();
        if (!empty($status)) {
            header('Location: ' . $url, true, $status);
        } else {
            header('Location: ' . $url, true);
        }
        exit;
    }

    /**
     * Save this request as follow up URL.
     *
     * <p>Typically this happends when a request is received without valid authority.
     * The saved URL will be forwarded to, once permissions is gained (user logged in).</p>
     */
    public function saveFollowUpUrl()
    {
        $params = $this->query->all();
        // @todo unpack route attributes?

        $data = array('requestId' => $this->getRequestId(), 'params' => $params, 'secure' => $this->isSecure());
        $this->getSession()->set('http.followUpUrl', $data);
    }

    /**
     * Check if a follow up url exists that should be loaded (after a login).
     *
     * @param boolean clear Optional flag to keep or clear the follow up url; default is <code>true</code> to clear.
     * @return string The url to go to or <code>null</code>.
     */
    public function getFollowUpUrl($clear=true)
    {
        if (null != ($data = $this->getSession()->get('http.followUpUrl'))) {
            $params = array();
            foreach ($data['params'] as $key => $value) {
                $params[] = $key.'='.$value;
            }
            if ($clear) {
                $this->getSession()->set('http.followUpUrl', null);
            }

            return $this->url($data['requestId'], implode('&', $params), $data['secure']);
        }

        return null;
    }

    /**
     * Sanitize a given value.
     *
     * @param mixed value A string or array.
     * @return mixed A sanitized version.
     */
    public static function sanitize($value)
    {
        if (is_string($value)) {
            //$value = preg_replace('/ +/', ' ', $value);
            $value = preg_replace('/[<>]/', '_', $value);
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                $value = stripslashes($value);
            }

            return trim($value);
        } elseif (is_array($value)) {
            while (list($key, $val) = each($value)) {
                $value[$key] = self::sanitize($val);
            }

            return $value;
        }

        return $value;
    }

    /**
     * Get the selected language.
     *
     * @see ZenMagick\Http\Session\Session::getSelectedLanguage()
     * @todo REMOVE! very temporary
     */
    public function getSelectedLanguage()
    {
        return $this->getSession()->getSelectedLanguage();
    }

}
