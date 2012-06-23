<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
  $layout_name = $request->query->get("layout_name");
  $view_name = $request->query->get("view_name");

  $source = null;
  if (null != $layout_name) {
      $source = $layout_name;
  } else if (null != $view_name) {
      $source = 'views/'.$view_name.'.php';
  }

  // resolve
  $sourceFile = $resourceResolver->findResource($source, $view::TEMPLATE);

  if (null != $sourceFile && is_file($sourceFile)) {
      ?><h2>Source for <?php echo $source ?></h2><pre id="source"><?php
      echo $html->encode(file_get_contents($sourceFile));
      ?></pre><?php
      return;
  }
?>
<h2>Source File not found</h2>
