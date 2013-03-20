<?php
/*
 * This file is part of the ZenMagick package.
 *
 * (c) Johnny Robeson <johnny@localmomentum.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZenMagick\ZenMagickBundle\Templating\Helper;

use ZenMagick\Base\Locales\Locales;
use Symfony\Component\Templating\Helper\Helper;

/**
 * DateHelper adds date formatting methods to templates.
 * provided by <code>Zenmagick\Base\Locales\Locales</clode>
 *
 */
class DateHelper extends Helper
{
    protected $locales;

    /**
     * Constructor.
     *
     * @param Locales $locales A Locales instance
     */
    public function __construct(Locales $locales)
    {
        $this->locales = $locales;
    }

    /**
     * Format a short form date.
     *
     * @param DateTime
     * @see Locales::shortDate
     */
    public function short($date)
    {
        return $this->locales->shortDate($date);
    }

    /**
     * Format a long form date.
     *
     * @param DateTime
     * @see Locales::longDate
     */
    public function long($date)
    {
        return $this->locales->longDate($date);
    }

    /**
     * Get a Date format specification.
     *
     * @param string group The format group.
     * @param string type  The subtype if required; default is <code>null</code>.
     * @return string A format string or <code>null</code>.
     */
    public function getFormat($group, $type = null)
    {
        return $this->locales->getFormat($group, $type);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'date';
    }
}
