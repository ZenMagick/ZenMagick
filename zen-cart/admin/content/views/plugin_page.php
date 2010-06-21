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
 *
 * $Id: zmPluginPage.php 2649 2009-11-27 03:37:42Z dermanomann $
 */
?><?php

  $toolbox = $request->getToolbox();
  $fkt = $request->getParameter('fkt');
  $view = $toolbox->admin->getViewForFkt($request, $fkt);

  // add event listener to update title...
  class _PPEventListener {
      private $title_ = '';
      function __construct($title) { $this->title_ = $title; }
      public function onZMFinaliseContents($args) {
          if (!ZMLangUtils::isEmpty($this->title_)) {
              $contents = $args['contents'];
              $args['contents'] = preg_replace('/<\/title>/', ' :: ' . $this->title_ . '</title>', $contents, 1);
              return $args;
          }
          return null;
      }
  }
  ZMEvents::instance()->attach(new _PPEventListener($toolbox->utils->getTitle($fkt, false)));

?>

<?php if (null != $view) {
    echo $view->generate($request);
} else { ?>
    <h2>Invalid Plugin Function: <?php echo $fkt ?></h2>
<?php } ?>
