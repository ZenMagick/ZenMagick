<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Banner.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services
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
        return Runtime::getContainer()->get('bannerService');
    }


    /**
     * Get a list of all banner groups.
     *
     * @return array List of banner group ids.
     */
    public function getBannerGroupIds() {
        $sql = "SELECT DISTINCT banners_group FROM " . TABLE_BANNERS;
        $ids = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array(), TABLE_BANNERS) as $result) {
            $ids[] = $result['group'];
        }
        return $ids;
    }

    /**
     * Get a <strong>random, single</strong> banner for the given symbolic banner group set (yes!) name.
     *
     * <p>A banner set is either a single banner group or a list of banner groups.</p>
     *
     * <p>Banner sets can be configured by creating a setting with the format: <em>banners.[NAME]</em>,
     * with <em>banners.</em> being a fixed prefix and <em>[NAME]</em> the name of the set.</p>
     * <p>Example: <code>ZMSettings::set('banners.mygroup', 'Wide-Banners');</code></p>
     *
     * @param string name A banner group set name.
     * @return mixed A <code>ZMBanner</code> instance or <code>null</code>.
     * @deprecated
     */
    public function getBannerForSet($name) {
        $list = $this->getBannersForGroupName(ZMSettings::get('banners.'.$name), $this->container->get('request')->isSecure());
        shuffle($list);
        return 0 < count($list) ? $list[0] : null;
    }

    /**
     * Get all banner according to zen-cart configuration.
     *
     * <p>this will return all banner as configured using the zen-cart define <code>SHOW_BANNERS_GROUP_SET_ALL</code>.
     *
     * @return array A list of <code>ZMBanner</code> instances.
     * @deprecated
     */
    public function getAllBanners() {
        return $this->getBannersForGroupName(ZMSettings::get('banners.all', 'bannerGroupAll'));
    }

    /**
     * Get one (random) or more banner based on the given banner group(s).
     *
     * <p>If <code>$all</code> is set to <code>true</code>, all matching banners will be returned.</p>
     *
     * @param string group One ore more group names, separated by ':'.
     * @param boolean secure Optional flag to load just banners for secure/unsecure pages; default is <code>null</code> for all.
     * @return array A list of <code>ZMBanner</code> instances.
     */
    public function getBannersForGroupName($group, $secure=null) {
        if (empty($group)) {
            return array();
        }

        $sql = "SELECT *
                FROM " . TABLE_BANNERS . "
                WHERE status = 1";

        if (null !== $secure) {
            $sql .= " AND banners_on_ssl = :ssl";
        }

        // handle multiple groups
        $groupList = array();
        foreach (explode(':', $group) as $group) {
            if (!empty($group)) {
                $groupList[] = $group;
            }
        }
        if (0 < count($groupList) && !empty($groupList[0])) {
            $sql .= " AND banners_group IN (:group)";
        }
        $sql .= " ORDER BY banners_sort_order";

        return ZMRuntime::getDatabase()->query($sql, array('ssl' => $secure, 'group' => $groupList), TABLE_BANNERS, 'ZMBanner');
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
        return ZMRuntime::getDatabase()->querySingle($sql, array('id' => $id), TABLE_BANNERS, 'ZMBanner');
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
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $bannerId), array(TABLE_BANNERS_HISTORY), ZMDatabase::MODEL_RAW);

        if (0 < $result['total']) {
            $sql = "UPDATE " . TABLE_BANNERS_HISTORY . "
                    SET banners_shown = banners_shown +1
                    WHERE banners_id = :id AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')";
        } else {
            $sql = "INSERT INTO " . TABLE_BANNERS_HISTORY . "
                      (banners_id, banners_shown, banners_history_date)
                    VALUES (:id, 1, now())";
        }
        ZMRuntime::getDatabase()->update($sql, array('id' => $bannerId), TABLE_BANNERS_HISTORY);
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
