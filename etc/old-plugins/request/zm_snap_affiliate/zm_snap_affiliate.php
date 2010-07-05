<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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

define('TABLE_REFERRERS', DB_PREFIX . 'referrers');
define('TABLE_COMMISSION', DB_PREFIX . 'commission');


/**
 * Snap affiliate plugin based on http://www.filterswept.com/category/snap-affiliates-for-zen-cart/.
 *
 * @package org.zenmagick.plugins.zm_snap_affiliate
 * @author DerManoMann
 * @version $Id$
 */
class zm_snap_affiliate extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Snap Affiliates', 'Simple affiliate program', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
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
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/snap.sql")), $this->messages_);
        $this->addConfigValue('Affiliate key prefix', 'affiliatePrefix', 'CNWR_', 'Common prefix for all generated affiliate keys',
            'widget@TextFormWidget#id=affiliatePrefix&name=affiliatePrefix&default=CNWR_&size=8&maxlength=6');
        $this->addConfigValue('Default commision', 'defaultCommission', '0.1', 'Default commission for all new affiliates',
            'widget@TextFormWidget#id=defaultCommission&name=defaultCommission&default=0.1&size=6&maxlength=6');
        $this->addConfigValue('Template location', 'usePluginViews', 'true', 'From where should the be loaded?',
            'widget@BooleanFormWidget#id=usePluginViews&name=usePluginViews&default=true&label.true=Plugin&label.false=Theme&style=radio');
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

        ZMEvents::instance()->attach($this);

        // set up view mappings used by the wp controller
        $view = 'SavantView#subdir=snap_affiliate&plugin=zm_snap_affiliate';
        if (ZMLangUtils::asBoolean($this->get('usePluginViews'))) {
            $view = 'PluginView#plugin=zm_snap_affiliate&subdir=views';
        }

        // have separate mapping rather than query parameter
        ZMUrlMapper::instance()->setMappingInfo('affiliate_terms', array('view' => 'affiliate_terms', 'viewDefinition' => $view));

        // signup
        ZMUrlMapper::instance()->setMappingInfo('affiliate_signup', array('view' => 'affiliate_signup', 'viewDefinition' => $view));
        ZMUrlMapper::instance()->setMappingInfo('affiliate_signup', array('viewId' => 'success', 'view' => 'affiliate_signup', 'viewDefinition' => $view));
        ZMUrlMapper::instance()->setMappingInfo('affiliate_signup', array('viewId' => 'main', 'view' => 'affiliate_main', 'viewDefinition' => 'RedirectView'));

        // main page
        ZMUrlMapper::instance()->setMappingInfo('affiliate_main', array('view' => 'affiliate_main', 'viewDefinition' => $view));
        ZMUrlMapper::instance()->setMappingInfo('affiliate_main', array('viewId' => 'signup', 'view' => 'affiliate_signup', 'viewDefinition' => 'RedirectView'));

        // sacs mappings
        ZMZenCartAccountSacsHandler::instance()->setMapping('affiliate_main');
    }

    /**
     * Init done callback.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     *
     * @param array args Optional parameter.
     */
    public function onZMInitDone($args=null) {
        // initial rule
        $rules = array(
            array('RequiredRule', 'url', 'Please enter a URL')
        );
        // validation rules for login
        ZMValidator::instance()->addRules('affiliateSignup', $rules);
    }

}
