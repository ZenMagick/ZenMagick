<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * Pending things widget.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.dashbord.widgets
 */
class ZMPendingDashboardWidget extends ZMDashboardWidget {

    /**
     * Create new user.
     *
     */
    public function __construct() {
        parent::__construct(_zm('Pending'));
    }


    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
        // TODO: convert into ajax pull
        $contents = '';
        $gvApprovalQueue = $this->container->get('couponService')->getCouponsForFlag('N');
        if (0 < count($gvApprovalQueue)) {
            $a = '<a href="'.$request->getToolbox()->admin2->url('zc_admin', 'zpid=gv_queue').'">'._zm('approval').'</a>';
            $contents .= sprintf(_zm('There are %s gift cards waiting for %s.'), count($gvApprovalQueue), $a);
        }

        $result = ZMRuntime::getDatabase()->querySingle("SELECT count(*) AS count FROM " . TABLE_REVIEWS . " WHERE status='0'");
        if (0 < $result['count']) {
            $contents .= ' <a href="'.$request->url('zc_admin', 'zpid=reviews&status=1').'">'.sprintf(_zm('There are %s reviews pending approval.'), $result['count']).'</a>';
        }

        if (0 == strlen($contents)) {
            $contents = _zm('No pending tasks found.');
        }

        if (!empty($contents)) {
            $this->setStatus(self::STATUS_NOTICE);
        }

        $contents = '<p id="pending">'.$contents.'</p>';
        return $contents;
    }

}
