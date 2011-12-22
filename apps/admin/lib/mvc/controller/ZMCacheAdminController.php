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
 * Admin controller for cache admin.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.admin.mvc.controller
 * @todo move hash calculation into controller
 */
class ZMCacheAdminController extends ZMController {

    /**
     * Get all configured caches.
     */
    protected function getCaches() {
        $caches = array();
        foreach (Runtime::getContainer()->getParameterBag()->get('zenmagick.cacheIds') as $id) {
            $caches[$id] = Runtime::getContainer()->get($id);
        }
        return $caches;
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        return array('caches' => $this->getCaches());
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if ($request->handleDemo()) {
            return $this->findView('success-demo');
        }

        $this->messageService->warn(_zm('Clear cache is currently disabled'));

        /*
        foreach ($this->getCaches() as $type => $cache) {
            $stats = $caches->getStats();
            foreach ($stats['system']['groups'] as $group => $config) {
                $hash = md5($type.$group.implode($config));
                if ('x' == $request->getParameter('cache_'.$hash)) {
                    $cache = ZMCaches::instance()->getCache($group, $config, $type);
                    $result = $cache->clear();
                    $msg = 'Clear cache \'%s\' ' . ($result ? 'successful' : 'failed');
                    $this->messageService->add(sprintf(_zm($msg), $type.'/'.$group), ($result ? 'success' : 'error'));
                }
            }
        }
         */

        return $this->findView('success');
    }

}
