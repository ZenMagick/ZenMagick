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

<h1>Catalog</h1>
<?php
  ZMSettings::set('apps.store.catalog.controller', 'CatalogDefaultTabController,QuickEditTabController');
?>

<?php

  $catalogViewId = 'catalog_default_tab';
  $catalogViewId = 'quick_edit_tab';

  foreach (explode(',', ZMSettings::get('apps.store.catalog.controller')) as $controller) {
      if (null != ($controller = ZMBeanUtils::getBean(trim($controller))) && $controller instanceof ZMCatalogContentController) {
          if ($controller->isActive($request)) {
              echo $controller->getName()."<BR>";
              if ($catalogViewId == $controller->getCatalogViewId()) {
                  $view = $controller->process($request);
                  $view->setLayout(null);
                  $view->setVar('currentLanguage', $request->getSelectedLanguage());
                  $view->setTemplate($catalogViewId);
                  echo $view->generate($request);
              }
          }
      }
  }

