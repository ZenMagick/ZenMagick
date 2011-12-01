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
 * Store status widget.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.dashbord.widgets
 */
class ZMStoreStatusDashboardWidget extends ZMDashboardWidget {

    /**
     * Create new user.
     *
     */
    public function __construct() {
        parent::__construct(_zm('Store Status'));
    }


    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
        $contents = _zm('Nothing to report.');
        $messages = array();

        // build status list
        if (!defined('DEFAULT_CURRENCY')) { $messages[] = array(self::STATUS_WARN, _zm('Please set a default currency.')); }
        if (!defined('DEFAULT_LANGUAGE') || DEFAULT_LANGUAGE=='') { $messages[] = array(self::STATUS_NOTICE, _zm('Please set a default language.')); }
        if (DOWN_FOR_MAINTENANCE == 'true') { $messages[] = array(self::STATUS_WARN, _zm('Your site is currently down for Maintenance.')); }

        // figure out the status and generate contents
        $status = self::STATUS_DEFAULT;
        // TODO: allow info messages too
        if (0 < count($messages)) {
            // TODO: improve icons and styling
            $contents = '<ul class="ui-widget">';
            foreach ($messages as $details) {
                if (self::STATUS_WARN == $details[0]) {
                    $status = self::STATUS_WARN;
                    $contents .= '<li class="ui-state-error"><span class="ui-icon ui-icon-alert"></span><span>'.$details[1].'</span></li>';
                } else if (self::STATUS_NOTICE == $details[0]) {
                    if (self::STATUS_DEFAULT == $status || self::STATUS_INFO == $status) {
                        $status = self::STATUS_NOTICE;
                    }
                    $contents .= '<li class="ui-state-highlight"><span class="ui-icon ui-icon-notice"></span><span>'.$details[1].'</span></li>';
                } else if (self::STATUS_INFO == $details[0]) {
                    if (self::STATUS_DEFAULT == $status) {
                        $status = self::STATUS_INFO;
                    }
                    $contents .= '<li><span class="ui-icon ui-icon-info"></span><span>'.$details[1].'</span></li>';
                }
            }
            $contents .= '</ul>';
        }

        $this->setStatus($status);

        $contents = '<p id="store-status">'.$contents.'</p>';
        return $contents;
    }

}
