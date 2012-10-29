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
namespace ZenMagick\StoreBundle\Rss;

use Iterator;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Rss\RssItem;

/**
 * Product RSS item iterator.
 *
 * @author DerManoMann
 */
class CatalogProductRssItemIterator extends ZMObject implements Iterator {
    private $productInfo;
    private $languageId;
    private $index;
    private $fullFeed;
    private $multiCurrency;

    /**
     * Create new instance.
     *
     * @param array productInfo List of product info for products to iterate.
     * @param int languageId The language id.
     * @param boolean fullFeed Optional flag to enable/disable full feed details; default is <code>true</code>.
     * @param boolean multiCurrency Optional flag to enable/disable multi currency details; default is <code>true</code>.
     */
    public function __construct(array $productInfo, $languageId, $fullFeed=true, $multiCurrency=true) {
        parent::__construct();
        $this->productInfo = $productInfo;
        $this->languageId = $languageId;
        $this->index = 0;
        $this->fullFeed = $fullFeed;
        $this->multiCurrency = $multiCurrency;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind() {
        $this->index = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function current() {
        return $this->rssItem($this->productInfo[$this->index], $this->languageId, $this->fullFeed, $this->multiCurrency);
    }

    /**
     * {@inheritDoc}
     */
    public function key() {
        return $this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function next() {
        ++$this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function valid() {
        return isset($this->productInfo[$this->index]);
    }

    /**
     * Generate RSS item for the given product.
     *
     * @param array productInfo The product info.
     * @param int languageId The language id.
     * @param boolean fullFeed Optional flag to enable/disable full feed details.
     * @param boolean multiCurrency Optional flag to enable/disable multi currency details.
     * @return RssItem The RSS item.
     */
    protected function rssItem($productInfo, $languageId, $fullFeed, $multiCurrency) {
        $categoryService = $this->container->get('categoryService');
        $currencyService = $this->container->get('currencyService');
        $product = $this->container->get('productService')->getProductForId($productInfo['id'], $languageId);
        $item = new RssItem();
        $item->setTitle($product->getName());
        $item->setLink($productInfo['url']);
        $html = $this->container->get('htmlTool');
        $desc = $html->strip($product->getDescription());
        if (!$fullFeed) {
            $desc = $html->more($desc, 60);
        }
        $item->setDescription($desc);
        $item->setPubDate($product->getDateAdded());

        $item->addTag('id', $product->getId());
        $item->addTag('model', $product->getModel());
        if (null != ($defaultCategory = $product->getDefaultCategory())) {
            // build id/name path
            $idPath = array();
            $namePath = array();
            foreach ($defaultCategory->getPath() as $categoryId) {
                if (null != ($category = $categoryService->getCategoryForId($categoryId, $languageId))) {
                    $idPath[] = $category->getId();
                    $namePath[] = $category->getName();
                }
            }
            $item->addTag('category', array(
                'id' => $defaultCategory->getId(),
                'name' => $defaultCategory->getName(),
                'path' => array('idPath' => implode('|', $idPath), 'namePath' => implode('|', $namePath))
            ));
        }

        if ($fullFeed) {
            $offers = $product->getOffers();
            $tax = true;
            $pricing = array(
                'basePrice' => $offers->getBasePrice($tax),
                'price' => $offers->getCalculatedPrice(),
                  // starting at ...
                'staggered' => ($offers->isAttributePrice() ? 'true' : 'false'),
                'free' => ($product->isFree() ? 'true' : 'false'),
            );

            if (!$product->isFree()) {
                if ($offers->isSale()) {
                    $pricing['sale'] = $offers->getSalePrice($tax);
                } elseif ($offers->isSpecial()) {
                    $pricing['special'] = $offers->getSpecialPrice($tax);
                }
            }

            if ($multiCurrency) {
                $currencyPricings = array();
                foreach ($currencyService->getCurrencies() as $currency) {
                    $cp = array();
                    foreach ($pricing as $key => $value) {
                        if (!is_string($value)) {
                            // convert to currency
                            $value = $currency->convertTo($value);
                        }
                        $cp[$key] = $value;
                    }
                    $currencyPricings[$currency->getCode()] = $cp;
                }
                $pricing = $currencyPricings;
            }

            $item->addTag('pricing', $pricing);

            $item->addTag('type', 'product');
            if (null != ($manufacturer = $product->getManufacturer())) {
                $item->addTag('brand', $manufacturer->getName());
            }
            if (null != ($imageInfo = $product->getImageInfo())) {
                $item->addTag('img', $imageInfo->getDefaultImage());
            }

            $reviewService = $this->container->get('reviewService');
            $ar = round($reviewService->getAverageRatingForProductId($product->getId(), $languageId));
            $item->addTag('rating', $ar);
        }

        if ($product->hasAttributes()) {
            $attributes = array();
            foreach ($product->getAttributes() as $attribute) {
                $values = array();
                foreach ($attribute->getValues() as $value) {
                    $values[] = $value->getName();
                }
                $attributes[$attribute->getName()] = $values;
            }
            $item->addTag('attributes', $attributes);
        }

        return $item;
    }

}
