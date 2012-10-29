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
 */
namespace ZenMagick\ZenCartBundle\Controller;

/**
 * ZenCart admin controller
 *
 * @author Johnny Robeson
 * @todo <johnny> we could try to untangle GET/POST mess, but is it really worth it?
 */
class AdminController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // from init_general_funcs
        foreach ($request->query->all() as $k => $v) {
            $request->query->set($k, strip_tags($v));
        }
        $request->overrideGlobals();
        $session = $request->getSession();
        $language = $request->getSelectedLanguage();

        $themeService = $this->container->get('themeService');
        $themeService->initThemes();

        if (null == $session->get('securityToken')) {
            $session->set('securityToken', $session->getToken());
        }

        $session->set('language', $language->getDirectory());
        $session->set('languages_id', $language->getId());
        $session->set('languages_code', $language->getCode());

        // strangely whos_online is the only user. @todo test ZM version of whos_online
        $session->set('currency', $this->container->get('settingsService')->get('defaultCurrency'));

        if (null == $session->get('selected_box')) {
            $session->set('selected_box', 'configuration');
        }

        $selectedBox = $request->query->get('selected_box');
        if (null != $selectedBox) {
            $session->set('selected_box', $selectedBox);
        }

        foreach ($session->all() as $k => $v) {
            $_SESSION[$k] = $v;
        }

        $autoLoader = $this->container->get('zenCartAutoLoader');
        $autoLoader->initCommon();

        $autoLoader->setGlobalValue('template_dir', $themeService->getActiveTheme()->getId());
        $autoLoader->setGlobalValue('zc_products', new \products);

        $tpl = compact('autoLoader');
        foreach ($autoLoader->getGlobalValues() as $k => $v) {
            $tpl[$k] = $v;
        }
        $view = $this->findView('zc_admin', $tpl);
        $view->setTemplate('zc_admin.php');
        // no layout for invoice/packaging slip
        if (in_array($request->getRequestId(), $this->container->get('settingsService')->get('apps.store.zencart.skipLayout', array()))) {
            $view->setLayout(null);
        }
        return $view;
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (!$this->validateSecurityToken($request)) {
            $this->messageService->error(_zm('Security token validation failed'));
            $request->redirect($request->server->get('HTTP_REFERER'));
        }
        return $this->processGet($request);
    }

    /**
     * Implementation of ZenCart's init_session securityToken checking code
     */
    public function validateSecurityToken($request) {
        $needsToken = $request->get('action') && 'POST' === $request->getMethod();
        if(!$needsToken) return true;
        return $request->getSession()->getToken() === $request->request->get('securityToken', '');
    }
}
