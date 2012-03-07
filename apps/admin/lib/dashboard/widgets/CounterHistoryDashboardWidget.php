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
namespace zenmagick\apps\store\admin\dashboard\widgets;

use zenmagick\apps\store\admin\dashboard\DashboardWidget;

/**
 * Basic stats dashboard widget.
 *
 * @author Johnny Robeson
 */
class CounterHistoryDashboardWidget extends DashboardWidget {
    private $counterResults;


    /**
     * Create new instance.
     */
    public function __construct() {
        $sql = "SELECT startdate, counter, session_counter FROM " . TABLE_COUNTER_HISTORY . " ORDER BY startdate DESC limit 10";
        $this->counterResults = \ZMRuntime::getDatabase()->fetchAll($sql, array(), 'counter_history');

        parent::__construct(sprintf(_zm('Counter History for last %s recorded days'), count($this->counterResults)));
    }


    /**
     * Get data.
     */
    protected function getData() {
        $data = array();

        foreach ($this->counterResults as $result) {
            $counter_startdate = $result['startdate'];
            $counter_startdate_formatted = strftime('%m/%d/%Y', mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
            $data[] = array($counter_startdate_formatted, $result['session_counter'], $result['counter']);
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
        $admin2 = $request->getToolbox()->admin2;
        $contents = '<table class="grid" cellspacing="0">';
        $contents .= '<tr><th>'._zm('Date').'</th><th>'._zm('Session').'</th><th>'._zm('Total').'</th></tr>';
        $language = $request->getSelectedLanguage();
        foreach ($this->getData() as $v) {
            $contents .= '<tr>';
            $contents .= '<td>'.$v[0].'</a></td>';
            $contents .= '<td>'.$v[1].'</td>';
            $contents .= '<td>'.$v[2].'</td>';
            $contents .= '</tr>';
        }
        $contents .= '</table>';
        return $contents;
    }

}
