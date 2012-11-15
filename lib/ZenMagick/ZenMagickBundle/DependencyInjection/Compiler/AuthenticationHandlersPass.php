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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Modify various authentication handlers.
 *
 */
class AuthenticationHandlersPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     * Switch out the authentication success handlers for our versions.
     *
     * @todo compose the service definitions from services.xml instead of
     * defining fresh here.
     */
    public function process(ContainerBuilder $container)
    {
        $adminAuthSuccess = 'security.authentication.success_handler.admin.form_login';
        if ($container->hasDefinition($adminAuthSuccess)) {
            $def = $container->findDefinition($adminAuthSuccess);
            $def->setClass('ZenMagick\AdminBundle\Security\Http\Authentication\AuthenticationSuccessHandler');
            $def->addMethodCall('setPrefService', array(new Reference('adminUserPrefService')));
        }
        $authSuccess = 'security.authentication.success_handler.storefront.form_login';
        if ($container->hasDefinition($authSuccess)) {
            $def = $container->findDefinition($authSuccess);
            $def->setClass('ZenMagick\StorefrontBundle\Security\Http\Authentication\AuthenticationSuccessHandler');
        }

    }
}
