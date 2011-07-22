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

use zenmagick\base\Runtime;

/**
 * Plugin for Ultimate SEO 2.x support.
 *
 * @package org.zenmagick.plugins.useo2
 * @author mano
 */
class ZMUseo2Plugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Ultimate SEO2', 'Ultimate SEO 2.x for ZenMagick', '${plugin.version}');
        $this->setContext('storefront');
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
        // this will remove all '%SEO%' configuration settings, so do this first,
        // before creating SEO plugin settings in parent::install() ...
        $patch = new ZMUseo2SupportPatch();
        if (null != $patch && $patch->isOpen()) {
            $status = $patch->patch(true);
            ZMMessages::instance()->addAll($patch->getMessages());
        }

        parent::install();
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=true) {
        parent::remove($keepSettings);

        $patch = new ZMUseo2SupportPatch();
        if (!$patch->isOpen() && $patch->canUndo()) {
            $status = $patch->undo();
            ZMMessages::instance()->addAll($patch->getMessages());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        Runtime::getSettings()->add('zenmagick.http.request.urlRewriter', 'ZMUseo2UrlRewriter');
    }

    /**
     * {@inheritDoc}
     */
    public function getMessages() {
        $messages = parent::getMessages();
        $patch = new ZMUseo2SupportPatch();
        if (null !== $patch && $patch->isOpen() && !$patch->isReady()) {
            $messages[] = new ZMMessage($patch->getPreconditionsMessage(), ZMMessages::T_WARN);
        }
        return $messages;
    }

}

  define('TABLE_SEO_CACHE', DB_PREFIX . 'seo_cache');
?><?php
// Function to reset SEO URLs database cache entries
// Ultimate SEO URLs v2.1
function zen_reset_cache_data_seo_urls($action) {
	switch ($action){
		case 'reset':
			$GLOBALS['db']->Execute("DELETE FROM " . TABLE_SEO_CACHE . " WHERE cache_name LIKE '%seo_urls%'");
			$GLOBALS['db']->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='false' WHERE configuration_key='SEO_URLS_CACHE_RESET'");
			break;
		default:
			break;
	}
	# The return value is used to set the value upon viewing
	# It's NOT returining a false to indicate failure!!
	return 'false';
}
?><?php function reset_seo_cache() { ZMRuntime::getDatabase()->update("DELETE FROM ".TABLE_SEO_CACHE." WHERE cache_name LIKE '%seo_urls%'"); } ?>
