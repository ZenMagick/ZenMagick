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
namespace zenmagick\http\request\rewriter;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Default URL rewriter.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class DefaultUrlRewriter extends ZMObject implements UrlRewriter {
    private $requestIdKey_;
    private static $methodList_ = array(
        'default' => array('decode' => null, 'rewrite' => 'rewriteDefault'),
        'path' => array('decode' => 'decodePath', 'rewrite' => 'rewritePath'),
        'realpath' => array('decode' => 'decodePath', 'rewrite' => 'rewritePath')
    );
    private $methods_;
    private $pathBase_;
    private $index_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->index_ = Runtime::getSettings()->get('zenmagick.http.request.handler', 'index.php');
        // resolve once only
        $this->requestIdKey_ = Runtime::getSettings()->get('zenmagick.http.request.idName', \ZMRequest::DEFAULT_REQUEST_ID);
        $type = Runtime::getSettings()->get('zenmagick.http.request.urlType', 'default');
        if (!array_key_exists($type, self::$methodList_)) {
            $type = 'default';
        }
        $this->methods_ = self::$methodList_[$type];
        if ('path' == $type) {
            $this->pathBase_ = $this->index_.'/';
        } else if ('realpath' == $type) {
            $this->pathBase_ = '';
        }
    }


    /**
     * {@inheritDoc}
     */
    public function decode($request) {
        if (null != ($method = $this->methods_['decode'])) {
            return $this->$method($request);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function rewrite($request, $args) {
        $requestId = $args['requestId'];
        $params = $args['params'];
        $secure = $args['secure'];
        if (null != ($alias = \ZMUrlManager::instance()->getAlias($requestId))) {
            $requestId = $alias['requestId'];
            $params = str_replace('&&', '&', $params.'&'.$alias['parameter']);
            if ('&' == $params[0]) {
                $params = substr($params, 1);
            }
        }

        if (null != ($method = $this->methods_['rewrite'])) {
            return $this->$method($request, $requestId, $params, $secure);
        }

        return false;
    }

    /**
     * Rewrite default implementation using query parameter.
     *
     * @param ZMRequest request The current request.
     * @param string requestId The request id.
     * @param string params Optional parameter.
     * @param boolean secure Indicate whether to create a secure or non secure URL.
     * @return string The URL.
     */
    protected function rewriteDefault($request, $requestId, $params, $secure) {
        $url = $this->index_ . '?' . $this->requestIdKey_ . '=' . $requestId;
        if (!empty($params)) {
            $url .= '&'.$params;
        }

        return $request->absoluteURL($url, false, $secure);
    }

    /**
     * Decode path implementation.
     *
     * @param ZMRequest request The current request.
     * @return boolean <code>true</code> if decoded, <code>false</code> if not.
     */
    protected function decodePath($request) {
        // if requestId key already in request, do nothing
        if (null != $request->getParameter($request->getRequestIdKey())) {
            return true;
        }
        $uri = $request->getUri();
        $context = $request->getContext().'/';
        if (0 === strpos($uri, $context.$this->pathBase_) && false === strpos($uri, '?')) {
            $path = substr($uri, strlen($context.$this->pathBase_));
            $token = explode('/', $path);
            $tokenCount = count($token);
            if (1 == $tokenCount%2) {
                if (empty($token[0])) {
                    $token[0] = 'index';
                }
                $request->setRequestId($token[0]);
                for ($ii=1; $ii<$tokenCount; $ii+=2) {
                    $request->setParameter($token[$ii], $token[$ii+1]);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Rewrite path implementation using something like '[index.php/]foo/value-of-foo/bar/value-of-bar'.
     *
     * @param ZMRequest request The current request.
     * @param string requestId The request id.
     * @param string params Optional parameter.
     * @param boolean secure Indicate whether to create a secure or non secure URL.
     * @return string The URL.
     */
    protected function rewritePath($request, $requestId, $params, $secure) {
        $url = $this->pathBase_.$requestId;
        parse_str($params, $parr);

        foreach ($parr as $key => $value) {
            $url .= '/'.\ZMNetUtils::encode($key).'/'.\ZMNetUtils::encode($value);
        }

        return $request->absoluteURL($url, false, $secure);
    }

}
