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
namespace ZenMagick\AdminBundle\Dashboard\Widgets;

use ZenMagick\AdminBundle\Dashboard\DashboardWidget;

use Symfony\Component\EventDispatcher\GenericEvent;

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
        $net = $this->container->get('netTool');
        // counter
        $result = $database->querySingle("SELECT startdate, counter FROM %table.counter%");
        $counter_startdate = $result['startdate'];
        $counter_startdate_formatted = strftime('%m/%d/%Y', mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
        $data[_zm('Hit Counter Started')] = $counter_startdate_formatted;
        $data[_zm('Hit Counter')] = $result['counter'];

        // customers
        $result = $database->querySingle("SELECT count(*) AS count FROM %table.customers%");
        $data[_zm('Customers')] = $result['count'];

        // products
        $result = $database->querySingle("SELECT count(*) AS count FROM %table.products% WHERE products_status = '1'");
        $data[_zm('Products')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM %table.products% WHERE products_status = '0'");
        $data[_zm('Inactive Products')] = $result['count'];

        // reviews
        $result = $database->querySingle("SELECT count(*) AS count FROM %table.reviews%");
        $data[_zm('Reviews')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM %table.reviews% WHERE status='0'");
        $data['<a href="'.$net->url('reviews', 'status=1').'">'._zm('Reviews pending approval').'</a>'] = $result['count'];

        // separator
        $data[] = null;

        // promotions
        $result = $database->querySingle("SELECT count(*) AS count FROM %table.specials% WHERE status= '0'");
        $data[_zm('Specials Expired')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM %table.specials% WHERE status= '1'");
        $data[_zm('Specials Active')] = $result['count'];

        $result = $database->querySingle("SELECT count(*) AS count FROM %table.featured% WHERE status= '0'");
        $data[_zm('Featured Products Expired')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM %table.featured% WHERE status= '1'");
        $data[_zm('Featured Products Active')] = $result['count'];

        $result = $database->querySingle("SELECT count(*) AS count FROM %table.salemaker_sales% WHERE sale_status= '0'");
        $data[_zm('Sales Expired')] = $result['count'];
        $result = $database->querySingle("SELECT count(*) AS count FROM %table.salemaker_sales% WHERE sale_status= '1'");
        $data[_zm('Sales Active')] = $result['count'];

        $event = new GenericEvent($this, array('data' => $data));
        $this->container->get('event_dispatcher')->dispatch('build_basic_stats', $event);

        return $event->getArgument('data');
    }

    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
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
