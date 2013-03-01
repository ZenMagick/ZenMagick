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
namespace ZenMagick\StorefrontBundle\EventListener;

use ZenMagick\Base\Beans;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\View\TemplateView;
use ZenMagick\Http\Session\FlashBag;
use ZenMagick\StoreBundle\Widgets\StatusCheck;
use ZenMagick\StoreBundle\Services\Account\Accounts;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Fixes and stuff that are (can be) event driven.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class StorefrontListener extends ZMObject
{
    /**
     * Handle 'showAll' parameter for result lists and provide empty address for guest checkout if needed.
     */
    public function onViewStart($event)
    {
        $request = $event->getArgument('request');
        $view = $event->getArgument('view');
        if ($view instanceof TemplateView) {
            $requestId = $request->getRequestId();
            if (null !== $request->query->get('showAll')) {
                if (null != ($resultList = $view->getVariable('resultList'))) {
                    $resultList->setPagination(0);
                }
            }
            if ('login' == $requestId && $this->container->get('settingsService')->get('isGuestCheckoutAskAddress')) {
                if (null == $view->getVariable('guestCheckoutAddress')) {
                    $address = Beans::getBean('ZenMagick\StoreBundle\Entity\Address');
                    $address->setPrimary(true);
                    $view->setVariable('guestCheckoutAddress', $address);
                }
            }
            // @todo where should this really live?
            $view->setVariable('isCheckout', !(false === strpos($requestId, 'checkout_')));

        }

    }

    /**
     * Need to load themes before the container freezes...
     * @todo: what to do???
     */
    public function onRequestReady($event)
    {
        $this->container->get('themeService')->initThemes();
        $theme = $this->container->get('themeService')->getActiveTheme();
        $args = array_merge($event->getArguments(), array('theme' => $theme, 'themeId' => $theme->getId()));
        $event->getDispatcher()->dispatch('theme_resolved', new GenericEvent($this, $args));

    }

    /**
     * More store startup code.
     */
    public function onContainerReady($event)
    {
        $request = $event->getArgument('request');

        $session = $request->getSession();
        // in case we came from paypal or some other external location.
        // @todo it should probably be some sort of session attribute.
        if (null == $session->get('customers_ip_address')) {
            $session->set('customers_ip_address', $request->getClientIp());
        }

        $this->fixCategoryPath($request);
        $this->checkAuthorization($request);
        $this->configureLocale($request);

        $this->dfm($request);
        $this->addStatusMessages($request);

        $theme = $this->container->get('themeService')->getActiveTheme();
        $args = array('theme' => $theme, 'themeId' => $theme->getId());
        $event->getDispatcher()->dispatch('theme_loaded', new GenericEvent($this, $args));
    }

    /**
     * Handle down for maintenance
     */
    public function dfm($request)
    {
        $settingsService = $this->container->get('settingsService');
        $downForMaintenance = $settingsService->get('apps.store.downForMaintenance', false);
        $adminIps = $settingsService->get('apps.store.adminOverrideIPs');

        if ($downForMaintenance && !in_array($request->getClientIp(), $adminIps)) {
            // @todo this would be more appropriately placed in the controller or dispatcher,
            // but also needs to work if  don't get that far due to application errors and
            // should only work on storefront.
            header('HTTP/1.1 503 Service Unavailable');
            $dfmPages = $settingsService->get('apps.store.downForMaintenancePages');
            $dfmRoute = $settingsService->get('apps.store.downForMaintenanceRoute');
            $dfmPages[] = $dfmRoute;
            if (!in_array($request->getRequestId(), $dfmPages)) {
                $url = $this->container->get('router')->generate($dfmRoute);
                $request->redirect($url);
                exit;
            }
        }
    }

    /**
     * Add storefront status messages
     */
    public function addStatusMessages($request)
    {
        $messages = array();
        foreach ($this->container->get('containerTagService')->findTaggedServiceIds('apps.store.storefront.dashboard.widget.statusCheck') as $id => $args) {
            $statusCheck = $this->container->get($id);
            $messages = array_merge($messages, $statusCheck->getStatusMessages());
        }
        $statusMap = array(
            StatusCheck::STATUS_DEFAULT => FlashBag::T_MESSAGE,
            StatusCheck::STATUS_INFO => FlashBag::T_MESSAGE,
            StatusCheck::STATUS_NOTICE => FlashBag::T_WARN,
            StatusCheck::STATUS_WARN => FlashBag::T_WARN,
        );
        $messageService = $request->getSession()->getFlashBag();
        foreach ($messages as $details) {
            $messageService->addMessage($details[1], $statusMap[$details[0]]);
        }
    }

    /**
     * Set up theme and block manager.
     *
     * @todo how much closer can we move it to the view layer?
     */
    public function onThemeLoaded($event)
    {
        $settingsService = $this->container->get('settingsService');
        $templateManager = $this->container->get('templateManager');
        // TODO: do via admin and just load mapping from somewhere
        // sidebox blocks
        $mappings = array();
        if ($templateManager->isLeftColEnabled()) {
            $index = 1;
            $mappings['leftColumn'] = array();
            foreach ($templateManager->getLeftColBoxNames() as $boxName) {
                // avoid duplicates by using $box as key
                $mappings['leftColumn'][$boxName] = 'blockWidget#template=boxes/'.$boxName.'.html.php&sortOrder='.$index++;
            }
        }
        if ($templateManager->isRightColEnabled()) {
            $index = 1;
            $mappings['rightColumn'] = array();
            foreach ($templateManager->getRightColBoxNames() as $boxName) {
                // avoid duplicates by using $box as key
                $mappings['rightColumn'][$boxName] = 'blockWidget#template=boxes/'.$boxName.'.html.php&sortOrder='.$index++;
            }
        }
        // general banners block group - if used, the group needs to be passed into fetchBlockGroup()
        $mappings['banners'] = array();
        $mappings['banners'][] = 'ZenMagick\StoreBundle\Widgets\BannerBlockWidget';

        // individual banner groups as per current convention
        $defaultBannerGroupNames = array(
            'banners.header1', 'banners.header2', 'banners.header3',
            'banners.footer1', 'banners.footer2', 'banners.footer3',
            'banners.box1', 'banners.box2',
            'banners.all'
        );
        foreach ($defaultBannerGroupNames as $blockGroupName) {
            // the banner group name is configured as setting..
            $bannerGroup = $settingsService->get($blockGroupName);
            $mappings[$blockGroupName] = array('ZenMagick\StoreBundle\Widgets\BannerBlockWidget#group='.$bannerGroup);
        }

        // shopping cart options
        $mappings['shoppingCart.options'] = array();
        $mappings['shoppingCart.options'][] = 'ZenMagick\StoreBundle\Widgets\PayPalECButtonBlockWidget';
        $mappings['mainMenu'] = array();
        $mappings['mainMenu'][] = 'ref::browserIDLogin';

        $this->container->get('blockManager')->setMappings($mappings);
    }

    /**
     * Create ZenMagick order created event that contains the order id.
     */
    public function onNotifyCheckoutProcessAfterOrderCreateAddProducts($event)
    {
        $args = array_merge($event->getArguments(), array('request' => $this->container->get('request'), 'orderId' => $_SESSION['order_number_created']));
        $event->getDispatcher()->dispatch('create_order', new GenericEvent($this, $args));
    }

    /**
     * Fix category path.
     */
    protected function fixCategoryPath($request)
    {
        $languageId = $request->getSession()->getLanguageId();
        if (0 != ($productId = $request->query->get('productId'))) {
            if ($request->attributes->get('categoryIds')) {
                // set default based on product default category
                if (null != ($product = $this->container->get('productService')->getProductForId($productId, $languageId))) {
                    $defaultCategory = $product->getDefaultCategory($languageId);
                    if (null != $defaultCategory) {
                        // @todo ZCSMELL
                        $request->query->set('cPath', implode('_', $defaultCategory->getPath()));
                        $request->attributes->set('categoryIds', $defaultCategory->getPath());
                    }
                }
            }
        }

        if ($this->container->get('settingsService')->get('apps.store.verifyCategoryPath')) {
            if ($request->attributes->has('categoryIds')) {
                $path = array_reverse((array) $request->attributes->get('categoryIds'));
                $last = count($path) - 1;
                $valid = true;
                foreach ($path as $ii => $categoryId) {
                    $category = $this->container->get('categoryService')->getCategoryForId($categoryId, $languageId);
                    if ($ii < $last) {
                        if (null == ($parent = $category->getParent())) {
                            // can't have top level category in the middle
                            $valid = false;
                            break;
                        } elseif ($parent->getId() != $path[$ii+1]) {
                            // not my parent!
                            $valid = false;
                            break;
                        }
                    } elseif (null != $category->getParent()) {
                        // must start with a root category
                        $valid = false;
                        break;
                    }
                }
                if (!$valid) {
                    $category = $this->container->get('categoryService')->getCategoryForId(array_pop($request->attributes->get('categoryIds'), $languageId));
                    if (is_array($category->getPath())) {
                        // @todo ZCSMELL
                        $request->query->set('cPath', implode('_', $category->getPath()));
                        $request->attributes->set('categoryIds', $category->getPath());
                    } else {
                        $this->container->get('logger')->err('invalid cPath: ' . $cPath);
                    }
                }
            }
        }
    }

    /**
     * Check authorization for the current account.
     */
    protected function checkAuthorization($request)
    {
        $account = $request->getAccount();
        if (null != $account && Accounts::AUTHORIZATION_PENDING == $account->getAuthorization()) {
            // @todo shouldn't use a hardcoded list.
            $unrestrictedPaged = array('conditions', 'cookie_usage', 'down_for_maintenance', 'contact_us',
                'customers_authorization', 'login', 'logoff', 'password_forgotten', 'privacy',
                'shippinginfo', 'unsubscribe');
            if (!in_array($request->getRequestId(), $unrestrictedPages)) {
                $request->redirect($this->container->get('router')->generate('customers_authorization'));
            }
        }
    }

    /**
     * Set locale based on browser settings.
     *
     * @todo move redirects to a controller (which one?)
     */
    public function configureLocale($request)
    {
        $settingsService = $this->container->get('settingsService');
        $session = $request->getSession();

        // ** currency **
        // Models rely on currency session variable via $session->get('currency'), so this has to happen first!
        if (null != ($currencyCode = $request->query->get('currency'))) {
            // @todo error on bad request currency?
            if (null != $this->container->get('currencyService')->getCurrencyForCode($currencyCode)) {
                $session->set('currency', $currencyCode);
            }
            // @todo better way to do this? perhaps we'd be better off setting a redirect_url form key or always set SetLastUrl?
            $request->query->remove('currency');
            $request->redirect($request->headers->get('referer'));
        }
        if (null == $session->get('currency')) {
            $session->set('currency', $settingsService->get('defaultCurrency'));
        }

        // ** language **
        $languageService = $this->container->get('languageService');
        if (null != ($languageCode = $request->query->get('language'))) {
            // @todo error on bad request language?
            if (null != ($language = $languageService->getLanguageForCode($languageCode))) {
                $this->set('language', $language->getDirectory());
                $this->set('languages_id', $language->getId());
                $this->set('languages_code', $language->getCode());
            }
           // @todo better way to do this? perhaps we'd be better off setting a redirect_url form key or always set SetLastUrl?
           $params = $request->query->remove('language');
           $request->redirect($request->headers->get('referer'));
        }

    }

}
