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
namespace zenmagick\apps\admin\dashboard\widgets;

use zenmagick\apps\admin\dashboard\DashboardWidget;

/**
 * Recent searches dashboard widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.apps.admin.dashbord.widgets
 */
class RecentSearchesDashboardWidget extends DashboardWidget {

    /**
     * Create new user.
     */
    public function __construct() {
        parent::__construct(_zm('Recent Searches'));
    }


    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
        $contents = '<p>'._zm('No Data').'</p>';
        return $contents;
    }

}
