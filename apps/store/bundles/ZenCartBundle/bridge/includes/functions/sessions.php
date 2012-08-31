<?php
/*
 * ZenMagick - Another PHP framework.
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

function zen_session_start() {
    return \ZenMagick\base\Runtime::getContainer()->get('session')->start();
}

/**
 * @todo shared certificates! support switching the id.
 */
function zen_session_id($sessid = '') {
    return (string)\ZenMagick\base\Runtime::getContainer()->get('session')->getId();
}

function zen_session_name($name = '') {
    \ZenMagick\base\Runtime::getContainer()->get('session')->getName();
}

function zen_session_close() {
    \ZenMagick\base\Runtime::getContainer()->get('session')->save();
}

function zen_session_destroy() {
    \ZenMagick\base\Runtime::getContainer()->get('session')->invalidate();
}

function zen_session_save_path($path = '') {
}

function zen_session_recreate() {
    \ZenMagick\base\Runtime::getContainer()->get('session')->migrate();
}
