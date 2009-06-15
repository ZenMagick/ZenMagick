<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
<?php

    /**
     * If you are changing ZM_ROOT, make sure to update 
     * ..\zen-cart\includes\init_includes\overrides\init_templates.php
     * and
     * ..\zen-cart\admin\includes\init_includes\overrides\init_templates.php
     *
     * The full order of action is:
     * 1) Uninstall all ZenMagick patches (as some use this value to generate code)
     * 2) Rename directory
     * 3) Update ZM_ROOT
     * 4) Update the files mentioned above
     * 5) Re-install plugins
     */
    define('ZM_ROOT', 'zenmagick/');
    define('ZM_DEFAULT_THEME', 'default');


    //** url page name key **//

    define('ZM_PAGE_KEY', 'main_page');


    //** db **//

    define('ZM_DB_PREFIX', DB_PREFIX);
    define('ZM_TABLE_TOKEN', ZM_DB_PREFIX . 'zm_token');
    

    //** date/time formats used internally by all models **//

    define('ZM_DATE_FORMAT', 'yyyy-mm-dd');
    define('ZM_DATETIME_FORMAT', 'yyyy-mm-dd hh:ii:ss');


    //** others **//

    define('PRODUCTS_OPTIONS_TYPE_SELECT', 0);

?>
