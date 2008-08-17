<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Banner.
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMBanners extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Banners');
    }


    /**
     * Get a banner for the given (zen-cart) index.
     *
     * <p>The index is based on the zen-cart defines for banner; eg: <code>SHOW_BANNERS_GROUP_SET3</code>.
     * Here the index would be three.</p>
     *
     * @param integer index The zen-cart index.
     * @return mixed A <code>ZMBanner</code> instance or <code>null</code>.
     */
    public function getBannerForIndex($index) {
        $list = $this->getBannersForGroupName(ZMSettings::get('bannerGroup'.$index));
        return 0 < count($list) ? $list[0] : null;
    }


    /**
     * Get all banner according to zen-cart configuration.
     *
     * <p>this will return all banner as configured using the zen-cart define <code>SHOW_BANNERS_GROUP_SET_ALL</code>.
     *
     * @return array A list of <code>ZMBanner</code> instances.
     */
    public function getAllBanners() { return $this->getBannersForGroupName(ZMSettings::get('bannerGroupAll'), true); }


    /**
     * Get one (random) or more banner based on the given banner group(s).
     *
     * <p>If <code>$all</code> is set to <code>true, all matching banners will be returned.</p>
     *
     * @param string identifiers One ore more identifiers, separated by ':'.
     * @param boolean all If set to <code>true</code>, all banners will be returned, ordered in 
     *  the configured sort order; default is <code>false</code> to shuffle results.
     * @return array A list of <code>ZMBanner</code> instances.
     */
    private function getBannersForGroupName($identifiers, $all=false) { 
        if (empty($identifiers)) {
            return array();
        }

        $sql = "SELECT *
                FROM " . TABLE_BANNERS . "
                WHERE status = 1";

        if (ZMRequest::isSecure()) {
            $sql .= " AND banners_on_ssl= :ssl";
        }

        // handle multiple identifiers
        $groupList = array();
        foreach (explode(':', $identifiers) as $group) {
            if (!empty($group)) {
                $groupList[] = $group;
            }
        }
        if (0 < count($groupList) && !empty($groupList[0])) {
            $sql .= " AND banners_group IN (:group)";
        }
        if ($all) {
            $sql .= " ORDER BY banners_sort_order";
        }

        $banner = ZMRuntime::getDatabase()->query($sql, array('ssl' => 1, 'group' => $groupList), TABLE_BANNERS, 'Banner');
        if (!$all) {
            shuffle($banner);
        }

        return $banner;
    }


    /**
     * Get a banner for the given id.
     *
     * @param integer id The banner id.
     * @return mixed A <code>ZMBanner</code> instance or <code>null</code>.
     */
    public function getBannerForId($id) { 
        $sql = "SELECT *
                FROM " . TABLE_BANNERS . "
                WHERE status = 1 AND banners_id = :id";
        if (ZMRequest::isSecure()) {
            $sql .= " AND banners_on_ssl= :ssl";
        }

        $banner = ZMRuntime::getDatabase()->querySingle($sql, array('ssl' => 1, 'id' => $id), TABLE_BANNERS, 'Banner');

        return $banner;
    }

    /**
     * Update banner display count.
     *
     * @param int bannerId The banner id.
     */
    public function updateBannerDisplayCount($bannerId) {
        $sql = "SELECT count(*) AS total
                FROM " . TABLE_BANNERS_HISTORY . "
                WHERE banners_id = :id AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $bannerId), array(TABLE_BANNERS_HISTORY), ZM_DB_MODEL_RAW);

        if (0 < $result['total']) {
            $sql = "UPDATE " . TABLE_BANNERS_HISTORY . "
                    SET banners_shown = banners_shown +1
                    WHERE banners_id = :id AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')";
            ZMRuntime::getDatabase()->update($sql, array('id' => $bannerId), TABLE_BANNERS_HISTORY);
        } else {
            $sql = "INSERT INTO " . TABLE_BANNERS_HISTORY . "
                      (banners_id, banners_shown, banners_history_date)
                    VALUES (:id, 1, now())";
            ZMRuntime::getDatabase()->update($sql, array('id' => $bannerId), TABLE_BANNERS_HISTORY);
        }
    }

    /**
     * Update banner click statistics.
     *
     * @param int bannerId The banner id.
     */
    public function updateBannerClickCount($bannerId) {
        $sql = "UPDATE " . TABLE_BANNERS_HISTORY . "
                SET banners_clicked = banners_clicked + 1
                WHERE banners_id = :id AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')";
        ZMRuntime::getDatabase()->update($sql, array('id' => $bannerId), TABLE_BANNERS_HISTORY);
    }

}

?>
