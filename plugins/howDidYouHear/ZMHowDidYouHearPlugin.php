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

define('ID_SOURCE_OTHER', 9999);
define('TABLE_SOURCES', DB_PREFIX . 'sources');
define('TABLE_SOURCES_OTHER', DB_PREFIX . 'sources_other');


/**
 * Plugin to add and handle a <em>How did you hear about us</em> drop down in the create account page.
 *
 * @package org.zenmagick.plugins.howDidYouHear
 * @author DerManoMann
 */
class ZMHowDidYouHearPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('How did you hear about us', 'Adds a drop down to the register page asking: "How did you hear about us"', '${plugin.version}');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/install_referrals.sql")), $this->messages_);
        $this->addConfigValue('Display "Other', 'displayOther', 'true',
            'Display "Other - please specify" with text box in referral source in account creation',
            'widget@ZMBooleanFormWidget#name=displayOther&default=true&label=Allow other&style=checkbox');
        $this->addConfigValue('Require Source', 'requireSource', 'true', 'Require the Referral Source in account creation',
            'widget@ZMBooleanFormWidget#name=requireSource&default=true&label=Require Source&style=checkbox');
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/uninstall.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        zenmagick\base\Runtime::getEventDispatcher()->listen($this);

        ZMSettings::append('zenmagick.core.database.sql.customers_info.customFields', 'customers_info_source_id;integer;sourceId');

        // add admin pages
        $menuKey = $this->addMenuGroup(_zm('Referral Sources'));
        $this->addMenuItem2(_zm('Referral Stats'), 'howDidYouHearSourcesStats', $menuKey);
        $this->addMenuItem2(_zm('Referral Admin'), 'howDidYouHearSourcesAdmin', $menuKey);
    }

    /**
     * Init done callback.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     */
    public function onInitDone($event) {
        if ($this->isRequired()) {
            // add validation rules
            $rules = array(
                array("RequiredRule", 'sourceId', 'Please select/provide the source where you first heard about us.'),
                array("SourceOtherRule", 'sourceOther', 'Please provide a description about where you first heard about us.')
            );
            ZMValidator::instance()->addRules('registration', $rules);
        }
    }

    /**
     * Add custom view data.
     */
    public function onViewStart($event) {
        $request = $event->get('request');
        if ('create_account' == $request->getRequestId()) {
            // create sources list
            $howDidYouHearSources = array();
            $source = new ZMObject();
            $source->setId('');
            $source->setName(_zm('Please select a source'));
            $howDidYouHearSources[] = $source;

            $sql = "SELECT sources_id, sources_name
                    FROM " . TABLE_SOURCES . "
                    ORDER BY sources_name";
            foreach (ZMRuntime::getDatabase()->query($sql, array()) as $result) {
                $source = new ZMObject();
                $source->setId($result['sources_id']);
                $source->setName($result['sources_name']);
                $howDidYouHearSources[] = $source;
            }
            if ($this->isDisplayOther()) {
                $source = new ZMObject();
                $source->setId(ID_SOURCE_OTHER);
                $source->setName(_zm('Other - (please specifiy)'));
                $howDidYouHearSources[] = $source;
            }
            // add to view context
            $view = $event->get('view');
            $view->setVar('howDidYouHearSources', $howDidYouHearSources);
        }
    }

    /**
     * Custom create account processing
     */
    public function onCreateAccount($event) {
        $account = $event->get('account');
        if (ID_SOURCE_OTHER == $account->getSourceId()) {
            // need to store sourceOther
            $sql = "INSERT INTO " . TABLE_SOURCES_OTHER . "
                    VALUES (:customers_id, :sources_other_name)";
            ZMRuntime::getDatabase()->update($sql, array('customers_id' => $account->getId(), 'sources_other_name' => $account->getSourceOther()), TABLE_SOURCES_OTHER);
        }
    }

    /**
     * Check if displayOther is set.
     *
     * @return boolean <code>true</code> if displayOther is set.
     */
    public function isDisplayOther() {
        return ZMLangUtils::asBoolean($this->get('displayOther'));
    }

    /**
     * Check if this is required.
     *
     * @return boolean <code>true</code> if an answer is required.
     */
    public function isRequired() {
        return ZMLangUtils::asBoolean($this->get('requireSource'));
    }

}
