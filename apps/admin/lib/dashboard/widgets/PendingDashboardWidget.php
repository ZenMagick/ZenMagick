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
namespace zenmagick\apps\store\admin\dashboard\widgets;

use zenmagick\apps\store\admin\dashboard\DashboardWidget;

/**
 * Pending things widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PendingDashboardWidget extends DashboardWidget {

    /**
     * Create new user.
     */
    public function __construct() {
        parent::__construct(_zm('Pending'));
    }


    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
        // TODO: convert into ajax pull
        $net = $request->getToolbox()->net;
        $contents = '';
        $gvApprovalQueue = $this->container->get('couponService')->getCouponsForFlag('N');
        if (0 < count($gvApprovalQueue)) {
            $a = '<a href="'.$net->url('gv_queue').'">'._zm('approval').'</a>';
            $contents .= sprintf(_zm('There are %s gift cards waiting for %s.'), count($gvApprovalQueue), $a);
        }

        $result = \ZMRuntime::getDatabase()->querySingle("SELECT count(*) AS count FROM %table.reviews% WHERE status='0'");
        if (0 < $result['count']) {
            $contents .= ' <a href="'.$net->url('reviews', 'status=1').'">'.sprintf(_zm('There are %s reviews pending approval.'), $result['count']).'</a>';
        }

        if (0 == strlen($contents)) {
            $contents = _zm('No pending tasks found.');
        } else {
            $this->setStatus(self::STATUS_NOTICE);
        }

        $contents = '<p id="pending">'.$contents.'</p>';
        return $contents;
    }

}
