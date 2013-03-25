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
namespace ZenMagick\StoreBundle\Widgets;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Http\Widgets\Widget;
use ZenMagick\Http\View\TemplateView;

/**
 * A banner block widget.
 *
 * @author DerManoMann
 */
class BannerBlockWidget extends Widget
{
    private $group;
    private $trackDisplay;
    private $showAll;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->group = null;
        $this->trackDisplay = true;
        $this->showAll = false;
        $this->setTitle('Banner Block');
    }

    /**
     * Get the group name.
     *
     * @return string The group name.
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the group name.
     *
     * @param string group The group name.
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * Enable/disable display tracking.
     *
     * @param boolean trackDisplay The new value.
     */
    public function setTrackDisplay($trackDisplay)
    {
        $this->trackDisplay = $trackDisplay;
    }

    /**
     * Check if display tracking is enabled.
     *
     * @return boolean <code>true</code> if tracking is enabled.
     */
    public function isTrackDisplay()
    {
        return $this->trackDisplay;
    }

    /**
     * Enable/disable displaying all available banners in the given group.
     *
     * @param boolean value The new value.
     */
    public function setShowAll($value)
    {
        $this->showAll = Toolbox::asBoolean($value);
    }

    /**
     * Check if all banners in the group should be displayed.
     *
     * @return boolean <code>true</code> if all banners are to be displayed.
     */
    public function isShowAll()
    {
        return $this->showAll;
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, TemplateView $templateView)
    {
        if (!$this->container->get('settingsService')->get('apps.store.banners.enabled', true)) {
            return '';
        }
        $bannerService = $this->container->get('bannerService');
        // try to load banners for the given group
        if (empty($this->group) || null == ($banners = $bannerService->getBannersForGroupName($this->group, $request->isSecure()))) {
            return '';
        }

        // make random
        shuffle($banners);

        // first or all
        if (!$this->showAll) {
            $banners = array(array_pop($banners));
        }

        // render banner(s)
        $bannerContentList = array();
        foreach ($banners as $banner) {
            $content = '';
            if (!Toolbox::isEmpty($banner->getText())) {
                // use text if not empty
                $content .= $banner->getText();
            } else {
                $toolbox = Runtime::getContainer()->get('toolbox');
                $html = $toolbox->html;
                $net = $toolbox->net;
                $img = '<img src="'.$net->image($banner->getImage()).'" alt="'.$html->encode($banner->getTitle()).'" />';
                if (Toolbox::isEmpty($banner->getUrl())) {
                    // if we do not have a url try our luck with the image...
                    $content .= $img;
                } else {
                    $class = '';
                    if ($banner->isNewWin()) {
                        $class = ' class="new-win" ';
                    }

                    $content .= '<a href="'.$net->trackLink('banner', $banner->getId()).'"'.$class.'>'.$img.'</a>';
                }
            }

            if ($this->isTrackDisplay()) {
                $bannerService->updateBannerDisplayCount($banner->getId());
            }
            if (!Toolbox::isEmpty($this->getFormat()) && !empty($content)) {
                $content = sprintf($this->getFormat(), $content);
            }
            $bannerContentList[] = $content;
        }

        // always set
        $this->set('bannerContentList', $bannerContentList);

        if (!Toolbox::isEmpty($this->getTemplate())) {
            // leave formatting to template rather than just concatenating
            return parent::render($request, $engine);
        }

        return implode('', $bannerContentList);
    }

}
