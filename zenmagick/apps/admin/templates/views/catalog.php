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

<?php foreach (ZMPlugins::instance()->getPluginsForGroup('catalog_manager') as $plugin) { ?>
  xx<?php echo $plugin ?>
<?php } ?>

<?php

  // peel fkt parameter from url string
  function get_fkt($url) {
      $urlToken = parse_url($url); 
      parse_str(str_replace('&amp;', '&', $urlToken['query']), $query); 
      return $query['fkt'];
  }

  // active fkt
  $selectedFkt = $request->getParameter('fkt', '');
  $defaultUrlParams = '';

  $tabInfo = array();
  foreach (ZMAdminMenu::getItemsForParentId(ZMAdminMenu::MENU_CATALOG_MANAGER_TAB) as $item) {
      $fkt = get_fkt($item->getURL());
      $view = $toolbox->admin->getViewForFkt($request, $fkt, $selectedFkt != $fkt);
      // export default url params as determined here
      $view->setVar('defaultUrlParams', $defaultUrlParams);
      $tabInfo[] = array('item' => $item, 'view' => $view, 'fkt' => $fkt);
  }


?>

      <ul>
        <?php foreach ($tabInfo as $info) { ?>
          <li><a href="#<?php echo $info['fkt'] ?>"><span><?php echo $info['item']->getTitle() ?></span></a></li>
        <?php } ?>
      </ul>

<?php $activeTab = 1; ?>
      <?php foreach ($tabInfo as $index => $info) { 
        if ($info['fkt'] == $selectedFkt) { $activeTab = ($index+1); }
        ?>
        <div id="<?php echo $info['fkt'] ?>" style="position:relative;">
            <?php if (ZMMessages::instance()->hasMessages()) { ?>
                <ul id="messages" style="margin-left:0">
                <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
                    <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
                <?php } ?>
                </ul>
            <?php } ?>
            <?php 
            if (null != $info['view']) {
                echo $info['view']->generate($request);
            } ?>
        </div>
      <?php } ?>

