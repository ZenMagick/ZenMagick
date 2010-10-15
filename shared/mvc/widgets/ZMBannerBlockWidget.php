<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
?>
<?php


/**
 * A banner block widget.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.widgets
 */
class ZMBannerBlockWidget extends ZMWidget {
    private $bannerId;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->bannerId_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the banner id.
     *
     * @param string bannerId The banner id.
     */
    public function setBannerId($bannerId) {
        $this->bannerId_ = $bannerId;
    }

    /**
     * Get the banner id.
     *
     * @return string The banner id.
     */
    public function getBannerId() {
        return $this->bannerId_;
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, $view) {
        if (null == ($banner = ZMBanners::instance()->getBannerForSet($this->bannerId_))) {
            return '';
        }

        // render banner
        $content = '';
        if (!ZMLangUtils::isEmpty($banner->getText())) {
            // use text if not empty
            $html = $banner->getText();
        } else {
            $net = $view->getVar('net');
            $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
            $img = '<img src="'.$net->image($banner->getImage()).'" alt="'.
                      ZMHtmlUtils::encode($banner->getTitle()).'"'.$slash.'>';
            if (ZMLangUtils::isEmpty($banner->getUrl())) {
                // if we do not have a url try our luck with the image...
                $content = $img;
            } else {
                $html = $view->getVar('html');
                $content = '<a href="'.$net->trackLink('banner', $banner->getId()).'"'.
                            $html->hrefTarget($banner->isNewWin()).'>'.$img.'</a>';
            }
        }

        if ($updateStats) {
            ZMBanners::instance()->updateBannerDisplayCount($banner->getId());
        }

        return $content;
    }

}
