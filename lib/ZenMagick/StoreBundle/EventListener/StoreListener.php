<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\StoreBundle\EventListener;


use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Shared store event listener.
 *
 * <p>This is the ZenMagick store bootstrapper.</p>
 *
 * @author DerManoMann
 */
 class StoreListener extends ZMObject implements EventSubscriberInterface {

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();

        $cPath = array();
        if (null !== ($path = $request->get('cPath'))) {
            $path = explode('_', $path);
            foreach ($path as $categoryId) {
                $categoryId = (int)$categoryId;
                if (!in_array($categoryId, $cPath)) {
                    $cPath[] = $categoryId;
                }
            }
        }
        $request->attributes->set('categoryIds', $cPath);
        $currentCategoryId = end($cPath);
        $request->attributes->set('categoryId', (int)$currentCategoryId);

    }

    /**
     * Run various post termination events.
     * @todo pass in list of services to run tasks on (perhaps via cron)
     */
    public function onKernelTerminate(PostResponseEvent $event) {
        $this->container->get('bannerService')->runTasks();
        $this->container->get('salemakerService')->runTasks();
        $this->container->get('productFeaturedService')->runTasks();
        $this->container->get('productSpecialsService')->runTasks();
    }

    public static function getSubscribedEvents() {
        return array(
            'kernel.request' => array(
                array('onKernelRequest'),
            ),
            'kernel.terminate' => array(
                array('onKernelTerminate'),
            )
        );
    }

}
