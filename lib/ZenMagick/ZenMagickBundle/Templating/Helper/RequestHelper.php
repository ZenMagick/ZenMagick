<?php
/*
 * This file is part of the ZenMagickBundle package.
 *
 * (c) Johnny Robeson <johnny@localmomentum.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZenMagick\ZenMagickBundle\Templating\Helper;

use Symfony\Bundle\FrameworkBundle\Templating\Helper\RequestHelper as BaseRequestHelper;

/**
 * RequestHelper provides access to the current request parameters.
 */
class RequestHelper extends BaseRequestHelper
{
    /**
     * Get current route id.
     *
     * @return string
     * @todo replace this with something that works for subrequests
     */
    public function getRouteId()
    {
        return $this->request->attributes->get('_route');
    }
}
