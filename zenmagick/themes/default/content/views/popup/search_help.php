<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * $Id$
 */
?>

<h1><?php zm_l10n("Search Help") ?></h1>

<p>Keywords may be separated by <em>and</em> and/or <em>or</em> statements for greater control of the search results.</p>
<p>For example, <span class="example">Microsoft <em>and</em> mouse</span> would generate a result set that contain both words.
   However, for <span class="example">mouse <em>or</em> keyboard</span>, the result set returned would contain both or either words.</p>

<p>Exact matches can be searched for by enclosing keywords in double-quotes.</p>
<p>For example, <span class="example">"notebook computer"</span> would generate a result set which match the exact string.</p>

<p>Brackets can be used for further control on the result set.</p>
<p>For example, <span class="example">Microsoft <em>and</em> (keyboard <em>or</em> mouse <em>or</em> "visual basic")</span>.</p>

<div id="close"><a href="#" onclick="javascript:window.close()"><?php zm_l10n("Close Window [x]") ?></a></div>
