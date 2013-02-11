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
namespace ZenMagick\ZenMagickBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ZenMagick\StoreBundle\Services\Locale\LanguageService;

class LocaleSessionListener implements EventSubscriberInterface
{
    private $defaultLocale;
    private $languageService;

    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function setLanguageService(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * This event uses a stored session var to set the locale on the request.
     *
     * This is equivalent to what symfony 2.0 did.
     */
    public function setLocale(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        $session = $request->getSession();
        if ($locale = $request->attributes->get('_locale')) {
            $session->set('_locale', $locale);
        } else {
            $request->setLocale($session->get('_locale', $this->defaultLocale));
        }
    }

    /**
     * Set Language session variables needed for ZM and ZC.
     */
    public function setLanguage(GetResponseEvent $event)
    {
         $request = $event->getRequest();
         $session = $request->getSession();

         if ((null != $this->languageService) && !$session->has('languages_id')) {
            // by now we should always have a code?
            $language = $this->languageService->getLanguageForCode($request->getLocale());
            if (null == $language) {
                $language = $this->languageService->getLanguageForCode('en');
            }
            $session->set('language', $language->getDirectory());
            $session->set('languages_id', $language->getId());
            $session->set('languages_code', $language->getCode());
        }

    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('setLocale', 17), // before HttpKernel LocaleListener
                array('setLanguage', 15), // after HttpKernel LocaleListener
            ),
        );
    }
}
