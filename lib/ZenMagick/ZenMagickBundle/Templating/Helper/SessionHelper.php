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

use Symfony\Bundle\FrameworkBundle\Templating\Helper\SessionHelper as BaseSessionHelper;

/**
 * SessionHelper provides read-only access to the session attributes.
 *
 */
class SessionHelper extends BaseSessionHelper
{
    /**
     * Checks if there are any messages available.
     *
     * @see ZenMagick\Http\Session\FlashBag
     * @param string ref The referencing resource; default is <code>null</code> for all.
     * @return boolean <code>true</code> if messages are available, <code>false</code> if not.
     */
    public function hasMessages($ref = null)
    {
        return $this->session->getFlashBag()->hasMessages($ref);
    }

    /**
     * Get all messages.
     *
     * @see ZenMagick\Http\Session\FlashBag
     * @param string ref The referring resource; default is <code>null</code> for all.
     * @Param boolean clear Optional flag to clear the internal buffer; default is <code>false</code>.
     * @return array List of <code>Message</code> instances.
     */
    public function getMessages($ref=null)
    {
        return $this->session->getFlashBag()->getMessages($ref);
    }
}
