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
namespace ZenMagick\StoreBundle\Toolbox;

use ZenMagick\Http\Toolbox\ToolboxTool;

/**
 * Generic utilities.
 *
 * @author DerManoMann
 */
class ToolboxUtils extends ToolboxTool
{

    /**
     * Format the given amount according to the current currency.
     *
     * @param float amount The amount.
     * @param boolean convert If <code>true</code>, consider <code>$amount</code> to be in default currency and
     *  convert before formatting.
     * @return string The formatted amount.
     */
    public function formatMoney($amount, $convert=true)
    {
        $currencyService = $this->container->get('currencyService');
        // @todo we should be be getting this from a user property
        $code = $this->getRequest()->getSession()->get('currency');
        if (null === $code) {
            $code = $this->container->get('settingsService')->get('defaultCurrency');
        }
        $currency = $currencyService->getCurrencyForCode($code);
        $money = $currency->format($amount, $convert);

        return $money;
    }

    /**
     * Get the content of a static (define) page.
     *
     * @param string pageName The page name.
     * @return string The content or <code>null</code>.
     */
    public function staticPageContent($pageName)
    {
        $languageId = $this->getRequest()->getSession()->getLanguageId();
        if (empty($languageId)) {
            // XXX: when called in admin
            $code = $this->container->get('settingsService')->get('defaultLanguageCode');
            $languageId = $this->container->get('languageService')->getLanguageForCode($code)->getLanguageId();
        }
        if (null != ($ezPage = $this->container->get('ezPageService')->getPageForName($pageName, $languageId))) {
            return $ezPage->getContent();
        }

        return null;
    }
}
