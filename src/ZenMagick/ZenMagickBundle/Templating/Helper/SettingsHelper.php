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

use ZenMagick\Base\Settings;
use Symfony\Component\Templating\Helper\Helper;

/**
 * SettingsHelper provides access to settings
 * provided by <code>Zenmagick\Base\Settings</clode>
 *
 */
class SettingsHelper extends Helper
{
    protected $settings;

    /**
     * Constructor.
     *
     * @param Settings $settings A Settings instance
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get a setting.
     *
     * @param string $name    name of the setting
     * @param mixed  $default default value if setting is missing
     * @see Settings
     */
    public function get($name, $default = null)
    {
        return $this->settings->get($name, $default);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'settings';
    }
}
