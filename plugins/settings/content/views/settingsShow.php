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
 */ $admin2->title() ?>
<?php foreach ($settingDetails as $group => $groupDetails) {
    echo '<h2>',$group,'</h2>';
    foreach ($groupDetails as $sub => $subDetails) {
        echo '<h3>',$sub,'</h3>';
        echo '<table width="88%" class="grid">';
        foreach ($subDetails as $details) {
            echo '<tr>';
            echo '<td width="32%">', $details['desc'], '</td>';
            echo '<td width="28%">', $details['fullkey'], '</td>';
            echo '<td>', $details['value'], '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
