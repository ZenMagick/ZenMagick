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
namespace ZenMagick\AdminBundle\Http\Tools;

use ZenMagick\Base\Runtime;
use ZenMagick\Http\Toolbox\ToolboxTool;
use ZenMagick\StoreBundle\Controller\CatalogContentController;

/**
 * Admin related functions.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ToolboxAdmin extends ToolboxTool
{
    /**
     * Create a catalog admin page URL.
     *
     * @param CatalogContentController controller A controller: default is <code>null</code> to use the current <em>catalogRequestId</em>.
     * @param string params Optional additional url parameter; default is <code>array</code>.
     * @return string A full URL.
     */
    public function catalog($controller=null, $params=array())
    {
        $request = $this->getRequest();
        $ps = array();
        if (null != ($cPath = $request->query->get('cPath'))) {
            $ps['cPath'] = $cPath;
        }
        if (null != ($productId = $request->query->get('productId'))) {
            $ps['productId'] = $productId;
        }
        if (null != $controller && $controller instanceof CatalogContentController) {
            $ps['catalogRequestId'] = $controller->getCatalogRequestId();
        } elseif (null != ($catalogRequestId = $request->query->get('catalogRequestId'))) {
            $ps['catalogRequestId'] = $catalogRequestId;
        }
        $ps = array_merge($ps, $params);

        return $this->container->get('router')->generate('catalog', $ps);
    }

    /**
     * Create a catalog tab admin page URL.
     *
     * @param CatalogContentController controller A controller: default is <code>null</code> to use the current <em>catalogRequestId</em>.
     * @param string params Optional additional url parameter; default is <code>array()</code>.
     * @return string A full URL.
     */
    public function catalogTab($controller=null, $params=array())
    {
        $request = $this->getRequest();
        $ps = array();
        if (null != ($cPath = $request->query->get('cPath'))) {
            $ps['cPath'] = $cPath;
        }
        if (null != ($productId = $request->query->get('productId'))) {
            $ps['productId'] = $productId;
        }
        if (null != $controller && $controller instanceof CatalogContentController) {
            $catalogRequestId = $controller->getCatalogRequestId();
        } else {
            $catalogRequestId = $request->query->get('catalogRequestId');
        }
        $ps = array_merge($ps, $params);

        return $this->container->get('router')->generate($catalogRequestId, $ps);
    }

    /**
     * Set the page title and create the side nav.
     *
     * <p><strong>NOTE: This method will return the derived page title to be used in a <em>h1</em>.</strong></p>
     *
     * @param string title Optional fixed (sub-)title; default is <code>null</code> for none.
     */
    public function title($title=null)
    {
        // @todo don't use getBreadCrumbsArray directly here
        $menu = $this->container->get('admin.menu.main');
        $crumbs = $menu ? $menu->getBreadcrumbsArray() : array();
        $pref = isset($crumbs[1]['item']) ? $crumbs[1]['item']->getLabel() : null;
        if (null == $title) {
            $title = $pref;
        } elseif (null != $pref) {
            $title = sprintf(_zm("%1s: %2s"), $pref, $title);
        }
        ?><h1><?php echo $title ?></h1><?php
        echo $this->getView()->fetch('AdminBundle::sub-menu.html.php');
        echo '<div id="view-container">';
        $title = sprintf(_zm("%1s :: %2s :: ZenMagick Admin"), Runtime::getSettings()->get('storeName'), $title);
        $this->container->get('templating.helper.slots')->set('title', $title);

        return $title;
    }

}
