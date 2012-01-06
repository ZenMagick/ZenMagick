<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace zenmagick\apps\admin\dashboard\widgets;

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;
use zenmagick\apps\admin\dashboard\DashboardWidget;


/**
 * Basic stats dashboard widget.
 *
 * @author Johnny Robeson
 */
class BasicStatsDashboardWidget extends DashboardWidget {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct(_zm('Basic Stats'));
    }


    /**
     * Get data.
     */
    protected function getData($request) {
        $data = array();
        $database = \ZMRuntime::getDatabase();

        // counter
        $result = $database->querySingle("SELECT startdate, counter FROM " . TABLE_COUNTER);
        $counter_startdate = $result['startdate'];
        $counter_startdate_formatted = strftime('%m/%d/%Y', mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
        $data[_zm('Hit Counter Started')] = $counter_startdate_formatted;
        $data[_zm('Hit Counter')] = $result['counter'];

        // customers
        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_CUSTOMERS);
        $data[_zm('Customers')] = $result['count'];

        // products
        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_PRODUCTS . " WHERE products_status = '1'");
        $data[_zm('Products')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_PRODUCTS . " WHERE products_status = '0'");
        $data[_zm('Inactive Products')] = $result['count'];

        // reviews
        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_REVIEWS);
        $data[_zm('Reviews')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_REVIEWS . " WHERE status='0'");
        $data['<a href="'.$request->url('zc_admin', 'zpid=reviews&status=1').'">'._zm('Reviews pending approval').'</a>'] = $result['count'];

        // separator
        $data[] = null;

        // promotions
        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_SPECIALS . " WHERE status= '0'");
        $data[_zm('Specials Expired')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_SPECIALS . " WHERE status= '1'");
        $data[_zm('Specials Active')] = $result['count'];

        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_FEATURED . " WHERE status= '0'");
        $data[_zm('Featured Products Expired')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_FEATURED . " WHERE status= '1'");
        $data[_zm('Featured Products Active')] = $result['count'];

        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_SALEMAKER_SALES . " WHERE sale_status= '0'");
        $data[_zm('Sales Expired')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM " . TABLE_SALEMAKER_SALES . " WHERE sale_status= '1'");
        $data[_zm('Sales Active')] = $result['count'];

        $event = new Event($this, array('data' => $data));
        Runtime::getEventDispatcher()->dispatch('build_basic_stats', $event);

        return $event->get('data');
    }

    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
        $admin2 = $request->getToolbox()->admin2;
        $contents = '<table class="grid" cellspacing="0">';
        $contents .= '<tr><th>'._zm('Type').'</th><th>'._zm('Stat').'</th></tr>';
        $language = $request->getSelectedLanguage();
        foreach ($this->getData($request) as $k => $v) {
            if (null === $v) {
                $contents .= '<tr class="be"><th colspan="2"></th></tr>';
            } else {
                $contents .= '<tr>';
                $contents .= '<td>'.$k.'</td>';
                $contents .= '<td>'.$v.'</td>';
                $contents .= '</tr>';
            }
        }
        $contents .= '</table>';
        return $contents;
    }

}
