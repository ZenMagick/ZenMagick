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
namespace ZenMagick\StoreBundle\Services\Templating;

use ZMRuntime;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Database\Connection;

/**
 * Banner.
 *
 * @author DerManoMann
 */
class Banners extends ZMObject {

    /**
     * Get a list of all banner groups.
     *
     * @return array List of banner group ids.
     */
    public function getBannerGroupIds() {
        $sql = "SELECT DISTINCT banners_group FROM %table.banners%";
        $ids = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array(), 'banners') as $result) {
            $ids[] = $result['group'];
        }
        return $ids;
    }

    /**
     * Get one (random) or more banner based on the given banner group(s).
     *
     * <p>If <code>$all</code> is set to <code>true</code>, all matching banners will be returned.</p>
     *
     * @param string group One ore more group names, separated by ':'.
     * @param boolean secure Optional flag to load just banners for secure/unsecure pages; default is <code>null</code> for all.
     * @return array A list of <code>Banner</code> instances.
     */
    public function getBannersForGroupName($group, $secure = false) {
        if (empty($group)) {
            return array();
        }

        $sql = "SELECT *
                FROM %table.banners%
                WHERE status = 1";

        if ($secure) {
            $sql .= " AND banners_on_ssl = 1";
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

        return ZMRuntime::getDatabase()->fetchAll($sql, array('group' => $groupList), 'banners', 'ZenMagick\StoreBundle\Entity\Templating\Banner');
    }

    /**
     * Get a banner for the given id.
     *
     * @param integer id The banner id.
     * @return mixed A <code>Banner</code> instance or <code>null</code>.
     */
    public function getBannerForId($id) {
        $sql = "SELECT *
                FROM %table.banners%
                WHERE status = 1 AND banners_id = :id";
        return ZMRuntime::getDatabase()->querySingle($sql, array('id' => $id), 'banners', 'ZenMagick\StoreBundle\Entity\Templating\Banner');
    }

    /**
     * Update banner display count and expire those that have reached the requested impressions.
     *
     * @param int bannerId The banner id.
     */
    public function updateBannerDisplayCount($bannerId) {
        $sql = "SELECT count(*) AS total
                FROM %table.banners_history%
                WHERE banners_id = :id AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $bannerId), array('banners_history'), Connection::MODEL_RAW);

        if (0 < $result['total']) {
            $sql = "UPDATE %table.banners_history%
                    SET banners_shown = banners_shown +1
                    WHERE banners_id = :id AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')";
        } else {
            $sql = "INSERT INTO %table.banners_history%
                      (banners_id, banners_shown, banners_history_date)
                    VALUES (:id, 1, now())";
        }
        ZMRuntime::getDatabase()->updateObj($sql, array('id' => $bannerId), 'banners_history');

        $this->expireByImpressions($bannerId);
    }

    /**
     * Expire banners that have reached their impressions limit.
     *
     * @param int bannerId The banner id.
     */
    public function expireByImpressions($bannerId) {
        $banner = $this->getBannerForId($bannerId);
        $maxImpressions = $banner->getExpiryImpressions();
        if ($maxImpressions > 0) {
            $sql = "SELECT SUM(banners_shown) AS total_shown
                    FROM %table.banners_history%
                    WHERE banners_id = :id";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $banner->getId()), array('banners_history'), Connection::MODEL_RAW);
            if ($maxImpressions <= $result['total_shown']) {
                $banner->setActive(false);
            }
            ZMRuntime::getDatabase()->updateModel('banners', $banner);
        }
    }

    /**
     * Update banner click statistics.
     *
     * @param int bannerId The banner id.
     */
    public function updateBannerClickCount($bannerId) {
        $sql = "UPDATE %table.banners_history%
                SET banners_clicked = banners_clicked + 1
                WHERE banners_id = :id AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')";
        ZMRuntime::getDatabase()->updateObj($sql, array('id' => $bannerId), 'banners_history');
    }

    /**
     * Run scheduled tasks for this service.
     */
    public function runTasks() {
        $this->scheduleBanners();
    }

    /**
     * Start/stop all banners.
     *
     * Stops all banners scheduled for expiration
     * and starts all banners scheduled to be started.
     */
    public function scheduleBanners() {
        $sql = "SELECT banners_id, date_scheduled, expires_date
                FROM %table.banners%";
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array(), 'banners', 'ZenMagick\StoreBundle\Entity\Templating\Banner') as $banner) {
            $dateScheduled = $banner->getDateScheduled();
            $expiryDate = $banner->getExpiryDate();
            if (null != $dateScheduled && new \DateTime() >= $dateScheduled) {
                $banner->setActive(true);
                ZMRuntime::getDatabase()->updateModel('banners', $banner);
            }
            if (null != $expiryDate && new \DateTime() >= $expiryDate) {
                $banner->setActive(false);
                ZMRuntime::getDatabase()->updateModel('banners', $banner);
            }
        }
    }
}
