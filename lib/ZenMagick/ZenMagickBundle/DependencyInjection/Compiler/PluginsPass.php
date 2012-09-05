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
namespace ZenMagick\ZenMagickBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Finder\Finder;

/**
 * Allow plugins to modify the container.
 *
 * @todo The big question is how much of this will be necessary
 *       if we are more tightly integrated with bundles.
 */
class PluginsPass implements CompilerPassInterface {

    /**
     * Process container configuration for plugins (if enabled)
     */
    public function process(ContainerBuilder $container) {
        if (!$container->has('pluginService')) return;

        // @todo still wrong place.
        $context = $container->getParameter('kernel.context');
        $plugins = $container->get('pluginService')->getPluginsForContext($context);

        if ($container->hasDefinition('translator.default')) {
            $this->registerTranslatorConfiguration($plugins, $container);
        }
    }

    /**
     * Register plugin translations with the container.
     *
     * This is more or less equivalent to how FrameworkBundle does it.
     */
    protected function registerTranslatorConfiguration(array $plugins = array(), ContainerBuilder $container) {
        $translator = $container->findDefinition('translator');

        $dirs = array();
        foreach ($plugins as $plugin) {
            if (is_dir($dir = $plugin->getPluginDirectory().'/translations')) {
                $dirs[] = $dir;
            }
        }
        if (empty($dirs)) return;

        $finder = Finder::create()
            ->files()
            ->filter(function (\SplFileInfo $file) {
                return 2 === substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
            })->in($dirs);

        foreach ($finder as $file) {
            // filename is domain.locale.format
            list($domain, $locale, $format) = explode('.', $file->getBasename(), 3);
            $translator->addMethodCall('addResource', array($format, (string) $file, $locale, $domain));
        }

    }

}
